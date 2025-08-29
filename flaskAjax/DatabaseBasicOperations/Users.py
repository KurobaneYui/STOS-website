import sys
import json
import numpy
import hashlib
import datetime
from typing import Any
from sqlalchemy import select
from sqlalchemy.orm import Session
from sqlalchemy.dialects.sqlite import insert as aqlalchemy_insert
from contextlib import nullcontext
import flask
from flask import Request
from flaskAjax.BaseComponents.ClientInfo import ClientInfo
from flaskAjax.BaseComponents.CustomSession import CustomSession
from flaskAjax.BaseComponents.DatabaseDefinition import (
    CollectedInfo as SQL_CollectedInfo,
    UserCredential as SQL_UserCredential,
    Group as SQL_Group,
    GroupMember as SQL_GroupMember,
    User as SQL_User,
    PaymentInfo as SQL_PaymentInfo,
    EmptyTime as SQL_EmptyTime,
)
from flaskAjax.BaseComponents.DatabaseConnector import (
    SQL_College,
    SQL_UserProfile,
    SessionLocal,
)
from flaskAjax.BaseComponents.CustomError import (
    PermissionDenyError,
    IllegalValueError,
    DatabaseRuntimeError,
)


def LoginDeviceRecorder(
    infoForm: dict, loginResult: bool, db_session: Session | None = None
) -> None:
    """Detect login device, login time, and login successfully or not.
    And record info in database.

    Args:
        formDict (dict):
            flask.request.form
        loginResult (bool):
            True when login successfully, and False when login error like
            username not exists or wrong password.
        db_session (Session):
            Reuse exist database connection.

    Returns:
        dict[str,str]: Result of recode process. Not used currently.
    """
    # =====================================
    # 如果提供已经建立的数据库连接，则直接使用
    session_context = SessionLocal() if db_session is None else nullcontext(db_session)
    with session_context as session:
        # ========================================
        # 获取客户端信息，并补充提供登录记录的额外信息
        clientInfo = ClientInfo.get_info()
        clientInfo["studentID"] = infoForm["StudentID"]
        clientInfo["time"] = datetime.datetime.now().isoformat()
        clientInfo["login_result"] = loginResult
        clientInfo["address"] = str(clientInfo["address"])
        clientInfo["department_id"] = infoForm["department_id"]
        clientInfo["job"] = infoForm["job"]
        # ===================================
        # 插入数据库，使用IGNORE参数忽略主键重复
        stmt = aqlalchemy_insert(SQL_CollectedInfo).values(
            {
                "student_id": clientInfo["studentID"],
                "user_agent": clientInfo["agent"],
                "ip_address": clientInfo["IP"],
                "address_info": clientInfo["address"],
                "language_info": clientInfo["language"],
                "login_result": clientInfo["login_result"],
            }
        )
        stmt = stmt.on_conflict_do_nothing(index_elements=["id"])
        try:
            session.execute(stmt)
            session.commit()
        except Exception:
            session.rollback()


class UsersDatabase:
    @staticmethod
    def login(flaskRequest: Request, db_session: Session | None = None) -> None:
        # =====================================
        # 如果提供已经建立的数据库连接，则直接使用
        session_context = (
            SessionLocal() if db_session is None else nullcontext(db_session)
        )
        with session_context as session:
            # ==========================
            # 根据提供的密码和学号进行查询
            assert flaskRequest.json is not None
            infoForm: dict[str, Any] = dict(flaskRequest.json)
            infoForm["department_id"] = 0
            infoForm["job"] = 0
            results = (
                session.query(SQL_UserCredential)
                .filter_by(
                    student_id=infoForm["StudentID"],
                    password_hash=hashlib.sha512(
                        infoForm["Password"].encode()
                    ).digest(),
                )
                .all()
            )
            # =================================
            # 必须为单一记录才判定为用户名密码正确
            # 即使登录失败，也记录用户登录失败信息
            if len(results) != 1:
                LoginDeviceRecorder(infoForm, False, session)
                raise PermissionDenyError(
                    "Username or password error.",
                    filename=__file__,
                    line=sys._getframe().f_lineno,
                )
            # ===============
            # 记录用户登录信息
            LoginDeviceRecorder(infoForm, True, session)
            # ====================
            # 获取姓名以设置Session
            studentName = UsersDatabase.getName(infoForm["StudentID"], session)
            flask.g.isLogin = True
            CustomSession.setSession(
                studentID=infoForm["StudentID"],
                name=studentName,
                logTime=datetime.datetime.now().isoformat(),
            )

    @staticmethod
    def getLoginWorks(db_session: Session | None = None) -> list[dict] | tuple[dict]:
        # =====================================
        # 如果提供已经建立的数据库连接，则直接使用
        session_context = (
            SessionLocal() if db_session is None else nullcontext(db_session)
        )
        with session_context as session:
            # ========================
            # 查询工作表单获取可登录岗位
            results = (
                session.query(SQL_GroupMember)
                .join(SQL_Group, SQL_GroupMember.group_id == SQL_Group.id)
                .filter(
                    SQL_GroupMember.student_id == CustomSession.getSession()["userID"],
                    SQL_GroupMember.group_id != 0,
                )
                .order_by(SQL_GroupMember.group_id.asc(), SQL_GroupMember.role.desc())
                .all()
            )
            # =================
            # 整理查询数据并返回
            if len(results) == 0:
                results = (
                    {
                        "department_id": 0,
                        "job": None,
                        "name": "预备队员",
                        "display_title": None,
                    },
                )
            else:
                results = [
                    {
                        "department_id": work.group_id,
                        "job": work.role,
                        "name": work.group.name,
                        "display_title": work.display_title,
                    }
                    for work in results
                ]
        return results

    @staticmethod
    def loginAsSpecifiedWork(
        flaskRequest: Request, db_session: Session | None = None
    ) -> str:
        # =====================================
        # 如果提供已经建立的数据库连接，则直接使用
        session_context = (
            SessionLocal() if db_session is None else nullcontext(db_session)
        )
        with session_context as session:
            # ===============================================
            # 查询工作表单验证可登录岗位
            # 如果登录部门是0岗位也是0，说明是预备队员，直接放过
            assert flaskRequest.json is not None
            infoForm = flaskRequest.json
            infoForm["department_id"] = int(infoForm["department_id"])
            infoForm["StudentID"] = CustomSession.getSession()["userID"]
            infoForm["StudentName"] = CustomSession.getSession()["userName"]
            results = (
                session.query(SQL_GroupMember)
                .filter_by(
                    group_id=infoForm["department_id"],
                    role=infoForm["job"],
                    student_id=infoForm["StudentID"],
                )
                .all()
            )
            if (infoForm["department_id"] != 0 or infoForm["job"] is not None) and len(
                results
            ) != 1:
                raise IllegalValueError(
                    "The work you want to login is not permitted.",
                    filename=__file__,
                    line=sys._getframe().f_lineno,
                )
            # ===================
            # 添加新的LogInfo记录
            LoginDeviceRecorder(infoForm, True, session)
            # ==============================
            # 查询部门名称，并更新Session信息
            results = (
                session.query(SQL_Group)
                .filter(SQL_Group.id == infoForm["department_id"] and SQL_Group.id != 0)
                .all()
            )
            if len(results) <= 0:
                name = "预备队员"
            else:
                name = results[0].name
            CustomSession.setSession(
                studentID=infoForm["StudentID"],
                name=infoForm["StudentName"],
                logTime=datetime.datetime.now().isoformat(),
                department_id=infoForm["department_id"],
                job=infoForm["job"],
                department_name=name,
            )
            flask.g.isLogin = True

        return "/user_center/index.html"

    @staticmethod
    def getName(studentId: str, db_session: Session | None = None) -> str:
        # =====================================
        # 如果提供已经建立的数据库连接，则直接使用
        session_context = (
            SessionLocal() if db_session is None else nullcontext(db_session)
        )
        with session_context as session:
            # ===================
            # 利用学号查询对应姓名
            results = session.query(SQL_User).filter_by(student_id=studentId).all()
            # ===============
            # 结果不唯一则报错
            if len(results) != 1:
                raise IllegalValueError(
                    "StudentID not found.",
                    filename=__file__,
                    line=sys._getframe().f_lineno,
                )

        return results[0].name

    @staticmethod
    def resetPassword(flaskRequest: Request, db_session: Session | None = None) -> None:
        # =====================================
        # 如果提供已经建立的数据库连接，则直接使用
        session_context = (
            SessionLocal() if db_session is None else nullcontext(db_session)
        )
        with session_context as session:
            # =====================
            # 利用提供的信息匹配用户
            # DBAffectRows = database.execute(
            #     "SELECT `MemberBasic`.student_id FROM `MemberBasic` \
            #     LEFT JOIN `MemberExtend` ON `MemberExtend`.student_id = `MemberBasic`.student_id \
            #     LEFT JOIN `School` ON `School`.school_id = `MemberExtend`.school_id \
            #     WHERE MemberBasic.student_id = %(StudentID)s and MemberBasic.name = %(Name)s \
            #         and School.name = %(School)s and hometown = %(Hometown)s;",
            #     flaskRequest.form
            # )
            assert flaskRequest.json is not None
            student_id = flaskRequest.json["StudentID"]
            name = flaskRequest.json["Name"]
            school = flaskRequest.json["School"]
            hometown = flaskRequest.json["Hometown"]
            stmt = (
                select(
                    SQL_User.student_id,
                )
                .join(
                    SQL_User.profile,
                )
                .join(
                    SQL_UserProfile.college,
                )
                .where(
                    SQL_User.student_id == student_id,
                    SQL_User.name == name,
                    SQL_UserProfile.hometown == hometown,
                    SQL_College.name == school,
                )
            )
            results = session.scalars(stmt).all()
            # ===================
            # 匹配结果不唯一则报错
            if len(results) != 1:
                raise PermissionDenyError(
                    "Information is wrong.",
                    filename=__file__,
                    line=sys._getframe().f_lineno,
                )
            # =====================
            # 匹配到则重置密码为学号
            credential = (
                session.query(SQL_UserCredential)
                .filter_by(student_id=flaskRequest.json["StudentID"])
                .one()
            )
            credential.password_hash = hashlib.sha512(student_id.encode()).digest()
            session.commit()

    @staticmethod
    def deletePersonalInfo(db_session: Session | None = None) -> None:
        # =====================================
        # 如果提供已经建立的数据库连接，则直接使用
        session_context = (
            SessionLocal() if db_session is None else nullcontext(db_session)
        )
        with session_context as session:
            # ==========================
            # 清除当前登录会话的用户的信息
            user_obj = session.get(SQL_User, CustomSession.getSession().get("userID"))
            if user_obj is None:
                raise IllegalValueError(
                    "Student ID not exists.",
                    filename=__file__,
                    line=sys._getframe().f_lineno,
                )
            # 删除，此操作会级联删除相关所有外键 ondelete 配置为 CASCADE 的行
            session.delete(user_obj)
            session.commit()

    @staticmethod
    def register(infoDict: dict, db_session: Session | None = None) -> None:
        # =====================================
        # 如果提供已经建立的数据库连接，则直接使用
        session_context = (
            SessionLocal() if db_session is None else nullcontext(db_session)
        )
        with session_context as session:
            # === 检查 StudentID 是否已存在 ===
            if (
                len(
                    session.query(SQL_UserProfile)
                    .filter(SQL_UserProfile.student_id == infoDict["studentID"])
                    .all()
                )
                != 0
            ):
                raise PermissionDenyError(
                    "Student ID exists.",
                    filename=__file__,
                    line=sys._getframe().f_lineno,
                )
            # === 尝试插入 User 表核心信息 ===
            if (
                len(
                    session.query(SQL_User)
                    .filter(SQL_User.student_id == infoDict["studentID"])
                    .all()
                )
                == 0
            ):
                user = SQL_User(
                    student_id=infoDict["studentID"],
                    name=infoDict["name"],
                    gender=infoDict["gender"],
                )
                session.add(user)
            try:
                # === 插入 UserProfile 表扩展信息 ===
                profile = SQL_UserProfile(
                    student_id=infoDict["studentID"],
                    campus_id=infoDict.get("campusID"),
                    college_id=infoDict.get("schoolID"),
                    hometown=infoDict.get("hometown"),
                    phone=infoDict.get("phone"),
                    qq=infoDict.get("qq"),
                )
                session.add(profile)

                # === 插入 PaymentInfo 表信息 ===
                payment = SQL_PaymentInfo(
                    student_id=infoDict["studentID"],
                    recipient_name=infoDict["name"],  # 如无 application_name 则用 name
                    card_number=infoDict["bank"],
                    is_registered_poor=0,  # 该值看你的应用有需要可填
                )
                session.add(payment)

                # === 插入密码表 ===
                password_hash = hashlib.sha512(infoDict["password"].encode()).digest()
                credential = SQL_UserCredential(
                    student_id=infoDict["studentID"], password_hash=password_hash
                )
                session.add(credential)

                # === 插入EmptyTime/空闲信息 ===
                empty_time = SQL_EmptyTime(
                    student_id=infoDict["studentID"],
                    slot_1_2=0,
                    slot_3_4=0,
                    slot_5_6=0,
                    slot_7_8=0,
                    slot_9_11=0,
                )
                session.add(empty_time)

                # === 7. 提交事务 ===
                session.commit()
                # 返回结果、或根据实际需求返回各表对象
            except Exception as e:
                session.rollback()
                raise DatabaseRuntimeError(
                    "Insert or Update member info error: " + str(e),
                    filename=__file__,
                    line=sys._getframe().f_lineno,
                )

    @staticmethod
    def topbarInfo() -> dict:
        # ============================
        # 查询Session保存的登录岗位信息
        info = CustomSession.getSession()
        return {
            "name": info["userName"],
            "department_id": info["department_id"],
            "department_name": info["department_name"],
            "job": info["job"],
        }

    # @staticmethod
    # def getContact(db_session: Session | None = None) -> list[dict] | tuple[dict]:
    #     # =====================================
    #     # 如果提供已经建立的数据库连接，则直接使用
    #     if db_session is None:
    #         database = DatabaseConnector()
    #         database.startCursor()
    #     else:
    #         database = db_session
    #     # =============
    #     # 获取通讯录视图
    #     database.execute(sql="SELECT * FROM `Contact`;")
    #     return database.fetchall()

    # @staticmethod
    # def getPersonalInfo(db_session: Session | None = None) -> tuple[dict] | list[dict]:
    #     # =====================================
    #     # 如果提供已经建立的数据库连接，则直接使用
    #     if db_session is None:
    #         database = DatabaseConnector()
    #         database.startCursor()
    #     else:
    #         database = db_session
    #     # ============
    #     # 获取个人信息
    #     database.execute(
    #         sql=
    #         "SELECT `MemberExtend`.student_id as student_id, `MemberBasic`.name as name, \
    #                 gender, ethnicity, hometown, phone, qq, `MemberExtend`.remark as infoRemark, \
    #                 campus, `School`.`name` as school, dormitory_yuan, dormitory_dong, dormitory_hao, \
    #                 application_student_id, application_name, application_bankcard, subsidy_dossier, \
    #                 `WageInfo`.remark as wageRemark \
    #             FROM `MemberExtend` \
    #             LEFT JOIN `MemberBasic` ON `MemberExtend`.`student_id` = `MemberBasic`.`student_id` \
    #             LEFT JOIN `School` ON `MemberExtend`.`school_id` = `School`.`school_id` \
    #             LEFT JOIN `WageInfo` ON `MemberExtend`.`student_id` = `WageInfo`.`student_id` \
    #             WHERE `MemberExtend`.`student_id` = %(userID)s;",
    #         data=CustomSession.getSession()
    #     )
    #     return database.fetchall()

    # # TODO: Not Finished Yet !!!
    # @staticmethod
    # def changePersonalInfo(infoDict: dict, db_session: Session | None = None) -> str:
    #     # =====================================
    #     # 如果提供已经建立的数据库连接，则直接使用
    #     if db_session is None:
    #         database = DatabaseConnector()
    #         database.startCursor()
    #     else:
    #         database = db_session
    #     # ======================
    #     # 检查个人信息数据是否存在
    #     DBAffectRows = database.execute(
    #         "SELECT student_id FROM `MemberExtend` WHERE student_id = %(studentID)s;",
    #         infoDict
    #     )
    #     database.fetchall()
    #     if DBAffectRows != 1:
    #         raise IllegalValueError(
    #             "Student ID not exists.",
    #             filename=__file__,
    #             line=sys._getframe().f_lineno
    #         )
    #     # ============
    #     # 更新个人信息
    #     DBAffectRows = database.execute(
    #         "UPDATE `MemberBasic` SET name=%(name)s, gender=%(gender)s WHERE student_id=%(studentID)s;",
    #         infoDict,
    #         autoCommit=False
    #     )
    #     if DBAffectRows not in [0, 1]:
    #         database.rollback()
    #         raise DatabaseRuntimeError(
    #             "Update member basic info error.",
    #             filename=__file__,
    #             line=sys._getframe().f_lineno
    #         )
    #     DBAffectRows = database.execute(
    #         "UPDATE `MemberExtend` SET \
    #             ethnicity=%(ethnicity)s, hometown=%(hometown)s, phone=%(phone)s, \
    #             qq=%(qq)s, school_id=%(schoolID)s, dormitory_yuan=%(dormitory_yuan)s, \
    #             dormitory_dong=%(dormitory_dong)s, dormitory_hao=%(dormitory_hao)s, \
    #             remark=%(infoRemark)s \
    #         WHERE student_id=%(studentID)s;",
    #         infoDict,
    #         autoCommit=False
    #     )
    #     if DBAffectRows not in [0, 1]:
    #         database.rollback()
    #         raise DatabaseRuntimeError(
    #             "Update member extend info error.",
    #             filename=__file__,
    #             line=sys._getframe().f_lineno
    #         )
    #     DBAffectRows = database.execute(
    #         "UPDATE `WageInfo` SET \
    #             application_student_id=%(application_student_id)s, application_name=%(application_name)s, \
    #             application_bankcard=%(application_bankcard)s,subsidy_dossier=%(subsidyDossier)s, \
    #             remark=%(wageRemark)s \
    #         WHERE student_id=%(studentID)s;",
    #         infoDict,
    #         autoCommit=False
    #     )
    #     if DBAffectRows not in [0, 1]:
    #         database.rollback()
    #         raise DatabaseRuntimeError(
    #             "Update wage info error.",
    #             filename=__file__,
    #             line=sys._getframe().f_lineno
    #         )
    #     if "password" in infoDict.keys():
    #         DBAffectRows = database.execute(
    #             "UPDATE `Password` SET passhash=%s \
    #             WHERE student_id=%s", (
    #                 hashlib.sha512(infoDict["password"].encode()
    #                               ).digest(), infoDict["studentID"]
    #             ),
    #             autoCommit=False
    #         )
    #         if DBAffectRows not in [0, 1]:
    #             database.rollback()
    #             raise DatabaseRuntimeError(
    #                 "Update password info error.",
    #                 filename=__file__,
    #                 line=sys._getframe().f_lineno
    #             )
    #     database.commit()

    #     return "刷新页面以更新数据，如仍有数据未更新，请退出重新登录。如有问题请联系管理员。"

    # @staticmethod
    # def getEmptyTimeInfo(db_session: Session | None = None) -> dict[str, Any]:
    #     # =====================================
    #     # 如果提供已经建立的数据库连接，则直接使用
    #     if databaseConnector is None:
    #         database = DatabaseConnector()
    #         database.startCursor()
    #     else:
    #         database = databaseConnector
    #     # =============
    #     # 获取空课时间表
    #     DBAffectedRows = database.execute(
    #         sql=
    #         "SELECT student_id,mon,tue,wed,thu,fri,sat,sun,remark FROM EmptyTime WHERE student_id=%(userID)s;",
    #         data=CustomSession.getSession()
    #     )
    #     if DBAffectedRows != 1:
    #         raise IllegalValueError(
    #             "Empty time table not found for the student.",
    #             filename=__file__,
    #             line=sys._getframe().f_lineno
    #         )
    #     results = database.fetchall()[0]
    #     # ===================
    #     # 按单双周等需求预处理
    #     week_name = ("mon", "tue", "wed", "thu", "fri", "sat", "sun")
    #     time_period = ("1-2", "3-4", "5-6", "7-8", "9-11")
    #     empty_table = {
    #         "odd":
    #             numpy.zeros((len(time_period), len(week_name)), dtype='int8').tolist(),
    #         "even":
    #             numpy.zeros((len(time_period), len(week_name)), dtype='int8').tolist(),
    #         "remark":
    #             results["remark"]
    #     }
    #     for i, name in enumerate(week_name):
    #         for j in range(len(results[name])):
    #             empty_table["even"][j][i] = 0 if results[name][j] in ['0', '1'] else 1
    #             empty_table["odd"][j][i] = 0 if results[name][j] in ['0', '2'] else 1

    #     return empty_table

    # @staticmethod
    # def getWorkBasicInfo(db_session: Session | None = None) -> tuple[dict] | list[dict]:
    #     # =====================================
    #     # 如果提供已经建立的数据库连接，则直接使用
    #     if databaseConnector is None:
    #         database = DatabaseConnector()
    #         database.startCursor()
    #     else:
    #         database = databaseConnector
    #     # ===============
    #     # 获取岗位基本信息
    #     database.execute(
    #         sql=
    #         "SELECT name,Department.department_id as department_id,job,wage,Work.remark as remark FROM Work \
    #             LEFT JOIN Department ON Work.department_id=Department.department_id \
    #             WHERE student_id=%(userID)s;",
    #         data=CustomSession.getSession()
    #     )
    #     # ==========================
    #     # 根据当前登录岗位补充岗位信息
    #     works = database.fetchall()
    #     loginDepartmentID = CustomSession().getSession()['department_id']
    #     loginJob = CustomSession().getSession()['job']
    #     for work in works:
    #         if work['department_id'] == loginDepartmentID and work['job'] == loginJob:
    #             work["loginWork"] = True
    #         else:
    #             work["loginWork"] = False
    #     return works

    # @staticmethod
    # def getScoreDetails(db_session: Session | None = None) -> dict:
    #     # =====================================
    #     # 如果提供已经建立的数据库连接，则直接使用
    #     if databaseConnector is None:
    #         database = DatabaseConnector()
    #         database.startCursor()
    #     else:
    #         database = databaseConnector
    #     # ===============
    #     # 获取个人扣分信息
    #     DBAffectedRows = database.execute(
    #         sql=
    #         "SELECT `date`, `Score`.department_id, `name` AS department_name, variant, reason  FROM `Score` \
    #             LEFT JOIN Department ON `Score`.department_id=Department.department_id \
    #             WHERE student_id=%(userID)s \
    #             ORDER BY `Score`.submission_time DESC;",
    #         data=CustomSession.getSession()
    #     )

    #     returns = dict()
    #     for row in database.fetchall():
    #         if row["department_id"] not in returns.keys():
    #             returns[row["department_id"]] = list()
    #         if len(returns[row["department_id"]]) > 20:
    #             continue
    #         row["date"] = str(row["date"])
    #         returns[row["department_id"]].append(row)

    #     return returns

    # @staticmethod
    # def getScheduleRecent(db_session: Session | None = None) -> dict:
    #     # =====================================
    #     # 如果提供已经建立的数据库连接，则直接使用
    #     if databaseConnector is None:
    #         database = DatabaseConnector()
    #         database.startCursor()
    #     else:
    #         database = databaseConnector
    #     # =================================
    #     # 获取当日日期后5天日期，和前20日日期
    #     currentDate = datetime.datetime.now()
    #     post5Date = currentDate + datetime.timedelta(days=5)
    #     past20Date = currentDate - datetime.timedelta(days=20)
    #     currentDate = currentDate.strftime("%Y-%m-%d")
    #     post5Date = post5Date.strftime("%Y-%m-%d")
    #     past20Date = past20Date.strftime("%Y-%m-%d")
    #     # ====================================
    #     # 查询日期范围内，此组员的早自习排班信息
    #     DBAffectedRows = database.execute(
    #         sql="SELECT selfstudy_id, date, classroom_name \
    #             FROM SelfstudyCheckActualView \
    #             WHERE SelfstudyCheckActualView.actual_student_id = %s \
    #                 AND (SelfstudyCheckActualView.date >= %s AND SelfstudyCheckActualView.date <= %s) \
    #             ORDER BY SelfstudyCheckActualView.date DESC, SelfstudyCheckActualView.classroom_name ASC;",
    #         data=(CustomSession.getSession()["userID"], past20Date, post5Date)
    #     )
    #     selfstudyHistoryData = list(database.fetchall())
    #     # =========================
    #     # 查询早自习排班对应的数据表
    #     for one_history in selfstudyHistoryData:
    #         # ==============================
    #         # 处理一下日期，转化为可以JSON化的
    #         one_history["date"] = one_history["date"].strftime("%Y-%m-%d")
    #         # ======
    #         # 数据表
    #         DBAffectedRows = database.execute(
    #             sql="SELECT groupleader_recheck \
    #                 FROM SelfstudyCheckData \
    #                 WHERE selfstudy_id = %s \
    #                 ORDER BY submission_time DESC \
    #                 LIMIT 1;",
    #             data=(one_history['selfstudy_id'],)
    #         )
    #         if DBAffectedRows == 0:
    #             database.fetchall()
    #             one_history.update({"groupleader_recheck": 0})
    #             one_history['submitted'] = False
    #             one_history['recheck'] = False
    #         else:
    #             tmpData = database.fetchall()[0]
    #             one_history.update(tmpData)
    #             one_history['submitted'] = True
    #             one_history['recheck'] = int(tmpData["groupleader_recheck"]) == 1
    #     # ==================================
    #     # 查询日期范围内，此组员的查课排班信息
    #     DBAffectedRows = database.execute(
    #         sql="SELECT course_id, date, period, course_order, classroom_name \
    #             FROM CourseCheckActualView \
    #             WHERE CourseCheckActualView.actual_student_id = %s \
    #                 AND (CourseCheckActualView.date >= %s AND CourseCheckActualView.date <= %s) \
    #             ORDER BY CourseCheckActualView.date DESC, CourseCheckActualView.course_order ASC, CourseCheckActualView.classroom_name ASC;",
    #         data=(CustomSession.getSession()["userID"], past20Date, post5Date)
    #     )
    #     coursesHistoryData = list(database.fetchall())
    #     # =======================
    #     # 查询查课排班对应的数据表
    #     for one_history in coursesHistoryData:
    #         # ==============================
    #         # 处理一下日期，转化为可以JSON化的
    #         one_history["date"] = one_history["date"].strftime("%Y-%m-%d")
    #         # ======
    #         # 数据表
    #         DBAffectedRows = database.execute(
    #             sql="SELECT groupleader_recheck \
    #                 FROM CourseCheckData \
    #                 WHERE course_id = %s \
    #                 ORDER BY submission_time DESC \
    #                 LIMIT 1;",
    #             data=(one_history['course_id'],)
    #         )
    #         if DBAffectedRows == 0:
    #             database.fetchall()
    #             one_history.update({"groupleader_recheck": 0})
    #             one_history['submitted'] = False
    #             one_history['recheck'] = False
    #         else:
    #             tmpData = database.fetchall()[0]
    #             one_history.update(tmpData)
    #             one_history['submitted'] = True
    #             one_history['recheck'] = int(tmpData["groupleader_recheck"]) == 1
    #     # ========
    #     # 整合数据
    #     results = dict()
    #     results['selfstudy'] = selfstudyHistoryData
    #     results['courses'] = coursesHistoryData
    #     # ========
    #     # 返回数据
    #     return results

    # @staticmethod
    # def getSelfstudyCheckData(db_session: Session | None = None) -> dict:
    #     # =====================================
    #     # 如果提供已经建立的数据库连接，则直接使用
    #     if databaseConnector is None:
    #         database = DatabaseConnector()
    #         database.startCursor()
    #     else:
    #         database = databaseConnector
    #     # ================================
    #     # 获取当日日期后3日日期，和前5日日期
    #     past5Date = (datetime.datetime.now() -
    #                  datetime.timedelta(days=5)).strftime("%Y-%m-%d")
    #     post3Date = (datetime.datetime.now() +
    #                  datetime.timedelta(days=3)).strftime("%Y-%m-%d")
    #     # ==============================
    #     # 查询日期范围内，此组员的排班信息
    #     DBAffectedRows = database.execute(
    #         sql=
    #         "SELECT selfstudy_id, date, classroom_name, campus, school_name, student_supposed \
    #             FROM SelfstudyCheckActualView \
    #             WHERE SelfstudyCheckActualView.actual_student_id = %s \
    #                 AND (SelfstudyCheckActualView.date >= %s AND SelfstudyCheckActualView.date <= %s) \
    #             ORDER BY SelfstudyCheckActualView.date DESC, SelfstudyCheckActualView.classroom_name ASC;",
    #         data=(CustomSession.getSession()["userID"], past5Date, post3Date)
    #     )
    #     historyData = list(database.fetchall())
    #     # ===================================
    #     # 查询排班对应的数据表，并按排班编号整理
    #     results = list()
    #     for one_history in historyData:
    #         # ==============================
    #         # 处理一下日期，转化为可以JSON化的
    #         one_history["date"] = one_history["date"].strftime("%Y-%m-%d")
    #         # ======
    #         # 数据表
    #         DBAffectedRows = database.execute(
    #             sql="SELECT check_result \
    #                 FROM SelfstudyCheckData \
    #                 WHERE selfstudy_id = %s \
    #                 ORDER BY submission_time DESC \
    #                 LIMIT 1;",
    #             data=(one_history['selfstudy_id'],)
    #         )
    #         if DBAffectedRows == 0:
    #             database.fetchall()
    #             selfstudyRecord = "{}"
    #         else:
    #             selfstudyRecord = database.fetchall()[0]["check_result"]
    #         # ======
    #         # 缺勤表
    #         DBAffectedRows = database.execute(
    #             sql="SELECT check_result \
    #                 FROM SelfstudyCheckAbsent \
    #                 WHERE selfstudy_id = %s \
    #                 ORDER BY submission_time DESC \
    #                 LIMIT 1;",
    #             data=(one_history['selfstudy_id'],)
    #         )
    #         if DBAffectedRows == 0:
    #             selfstudyAbsentList = "[]"
    #             database.fetchall()
    #         else:
    #             selfstudyAbsentList = database.fetchall()[0]["check_result"]
    #         # ======
    #         # 请假表
    #         DBAffectedRows = database.execute(
    #             sql="SELECT check_result \
    #                 FROM SelfstudyCheckAskForLeave \
    #                 WHERE selfstudy_id = %s \
    #                 ORDER BY submission_time DESC \
    #                 LIMIT 1;",
    #             data=(one_history['selfstudy_id'],)
    #         )
    #         if DBAffectedRows == 0:
    #             selfstudyAskForLeaveList = "[]"
    #             database.fetchall()
    #         else:
    #             selfstudyAskForLeaveList = database.fetchall()[0]["check_result"]
    #         # ========
    #         # 整合数据
    #         results.append({
    #             'date': one_history['date'],
    #             'selfstudy_id': one_history['selfstudy_id'],
    #             'classroom_name': one_history['classroom_name'],
    #             'school_name': one_history['school_name'],
    #             'campus': one_history['campus'],
    #             'student_supposed': one_history['student_supposed'],
    #             'record': selfstudyRecord,
    #             'absent': selfstudyAbsentList,
    #             'askForLeave': selfstudyAskForLeaveList
    #         })
    #     # ========
    #     # 返回数据
    #     return results

    # @staticmethod
    # def submitSelfstudyRecord(infoForm: dict, db_session: Session | None = None) -> None:
    #     # =====================================
    #     # 如果提供已经建立的数据库连接，则直接使用
    #     if databaseConnector is None:
    #         database = DatabaseConnector()
    #         database.startCursor()
    #     else:
    #         database = databaseConnector
    #     # ==========================================
    #     # 查找早自习排班编号对应的实际查早组员是否为本人
    #     DBAffectedRows = database.execute(
    #         sql="SELECT selfstudy_id \
    #             FROM SelfstudyCheckActualView \
    #             WHERE SelfstudyCheckActualView.actual_student_id = %s \
    #                 AND SelfstudyCheckActualView.selfstudy_id = %s;",
    #         data=(CustomSession.getSession()["userID"], infoForm['selfstudy_id'])
    #     )
    #     database.fetchall()
    #     if DBAffectedRows == 0:
    #         raise PermissionError(
    #             "此早自习排班的实际检查组员与本人核验不匹配，请联系管理员.",
    #             filename=__file__,
    #             line=sys._getframe().f_lineno
    #         )
    #     # ============
    #     # 提交查早数据
    #     check_result = json.dumps(infoForm['record'], ensure_ascii=False)
    #     DBAffectedRows = database.execute(
    #         sql="INSERT INTO SelfstudyCheckData \
    #                 (selfstudy_id,check_result,submit_student_id,groupleader_recheck,remark) \
    #             VALUES \
    #                 (%s,%s,%s,0,'');",
    #         data=(
    #             infoForm['selfstudy_id'], check_result,
    #             CustomSession.getSession()["userID"]
    #         ),
    #         autoCommit=False
    #     )

    #     check_result = json.dumps(infoForm['absentList'], ensure_ascii=False)
    #     DBAffectedRows = database.execute(
    #         sql="INSERT INTO SelfstudyCheckAbsent \
    #                 (selfstudy_id,check_result,submit_student_id) \
    #             VALUES \
    #                 (%s,%s,%s);",
    #         data=(
    #             infoForm['selfstudy_id'], check_result,
    #             CustomSession.getSession()["userID"]
    #         ),
    #         autoCommit=False
    #     )

    #     check_result = json.dumps(infoForm['askForLeaveList'], ensure_ascii=False)
    #     DBAffectedRows = database.execute(
    #         sql="INSERT INTO SelfstudyCheckAskForLeave \
    #                 (selfstudy_id,check_result,submit_student_id) \
    #             VALUES \
    #                 (%s,%s,%s);",
    #         data=(
    #             infoForm['selfstudy_id'], check_result,
    #             CustomSession.getSession()["userID"]
    #         ),
    #         autoCommit=False
    #     )
    #     # ========
    #     # 提交数据
    #     database.commit()

    # @staticmethod
    # def getCoursesCheckData(db_session: Session | None = None) -> dict:
    #     # =====================================
    #     # 如果提供已经建立的数据库连接，则直接使用
    #     if databaseConnector is None:
    #         database = DatabaseConnector()
    #         database.startCursor()
    #     else:
    #         database = databaseConnector
    #     # ================================
    #     # 获取当日日期后3日日期，和前5日日期
    #     past5Date = (datetime.datetime.now() -
    #                  datetime.timedelta(days=5)).strftime("%Y-%m-%d")
    #     post3Date = (datetime.datetime.now() +
    #                  datetime.timedelta(days=3)).strftime("%Y-%m-%d")
    #     # ==============================
    #     # 查询日期范围内，此组员的排班信息
    #     DBAffectedRows = database.execute(
    #         sql=
    #         "SELECT course_id, date, period, classroom_name, campus, school_name, student_supposed, course_name, grade, course_order \
    #             FROM CourseCheckActualView \
    #             WHERE CourseCheckActualView.actual_student_id = %s \
    #                 AND (CourseCheckActualView.date >= %s AND CourseCheckActualView.date <= %s) \
    #             ORDER BY CourseCheckActualView.date DESC, CourseCheckActualView.period ASC, CourseCheckActualView.course_order ASC,CourseCheckActualView.classroom_name ASC;",
    #         data=(CustomSession.getSession()["userID"], past5Date, post3Date)
    #     )
    #     historyData = list(database.fetchall())
    #     # ===================================
    #     # 查询排班对应的数据表，并按排班编号整理
    #     results = dict()
    #     for one_history in historyData:
    #         # ==============================
    #         # 处理一下日期，转化为可以JSON化的
    #         one_history["date"] = one_history["date"].strftime("%Y-%m-%d")
    #         # ======
    #         # 数据表
    #         DBAffectedRows = database.execute(
    #             sql="SELECT check_result \
    #                 FROM CourseCheckData \
    #                 WHERE course_id = %s \
    #                 ORDER BY submission_time DESC \
    #                 LIMIT 1;",
    #             data=(one_history['course_id'],)
    #         )
    #         if DBAffectedRows == 0:
    #             database.fetchall()
    #             courseRecord = "{}"
    #         else:
    #             courseRecord = database.fetchall()[0]["check_result"]
    #         # ========
    #         # 整合数据
    #         if (one_history['date'] + '_' + one_history['period']) not in results.keys():
    #             results[one_history['date'] + '_' + one_history['period']] = list()
    #         results[one_history['date'] + '_' + one_history['period']].append({
    #             'date': one_history['date'],
    #             'period': one_history['period'],
    #             'course_id': one_history['course_id'],
    #             'classroom_name': one_history['classroom_name'],
    #             'school_name': one_history['school_name'],
    #             'campus': one_history['campus'],
    #             'student_supposed': one_history['student_supposed'],
    #             'course_name': one_history['course_name'],
    #             'grade': one_history['grade'],
    #             'course_order': one_history['course_order'],
    #             'record': courseRecord
    #         })
    #     # ========
    #     # 返回数据
    #     return results

    # @staticmethod
    # def submitCoursesRecord(infoForm: dict, db_session: Session | None = None) -> None:
    #     # =====================================
    #     # 如果提供已经建立的数据库连接，则直接使用
    #     if databaseConnector is None:
    #         database = DatabaseConnector()
    #         database.startCursor()
    #     else:
    #         database = databaseConnector
    #     # ==========================================
    #     # 查找查课排班编号对应的实际查早组员是否为本人
    #     DBAffectedRows = database.execute(
    #         sql="SELECT course_id \
    #             FROM CourseCheckActualView \
    #             WHERE CourseCheckActualView.actual_student_id = %s \
    #                 AND CourseCheckActualView.course_id = %s;",
    #         data=(CustomSession.getSession()["userID"], infoForm['course_id'])
    #     )
    #     database.fetchall()
    #     if DBAffectedRows == 0:
    #         raise PermissionError(
    #             "此查课排班的实际检查组员与本人核验不匹配，请联系管理员.",
    #             filename=__file__,
    #             line=sys._getframe().f_lineno
    #         )
    #     # ============
    #     # 提交查课数据
    #     check_result = json.dumps(infoForm['record'], ensure_ascii=False)
    #     DBAffectedRows = database.execute(
    #         sql="INSERT INTO CourseCheckData \
    #                 (course_id,check_result,submit_student_id,groupleader_recheck,remark) \
    #             VALUES \
    #                 (%s,%s,%s,0,'');",
    #         data=(
    #             infoForm['course_id'], check_result, CustomSession.getSession()["userID"]
    #         ),
    #         autoCommit=False
    #     )
    #     # ========
    #     # 提交数据
    #     database.commit()
