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
from flaskAjax.BaseComponents.DatabaseConnector import (
    SQL_User,
    SQL_UserProfile,
    SQL_CollectedInfo,
    SQL_UserCredential,
    SQL_PaymentInfo,
    SQL_EmptyTime,
    SQL_Group,
    SQL_GroupMember,
    SQL_College,
    SQL_Classroom,
    SQL_StudySchedule,
    SQL_CheckInTask,
    SQL_CheckInData,
    SQL_CourseSchedule,
    SQL_InspectionTask,
    SQL_InspectionData,
    SQL_ContactView,
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
                        "job": work.role.value,
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
                CustomSession.setSession(
                    studentID=infoForm["StudentID"],
                    name=infoForm["StudentName"],
                    logTime=datetime.datetime.now().isoformat(),
                    department_id=infoForm["department_id"],
                    chazao=infoForm["job"],
                    chake=infoForm["job"],
                    datamanager=infoForm["job"],
                    job=infoForm["job"],
                    department_name="预备队员",
                )
            else:
                CustomSession.setSession(
                    studentID=infoForm["StudentID"],
                    name=infoForm["StudentName"],
                    logTime=datetime.datetime.now().isoformat(),
                    department_id=infoForm["department_id"],
                    chazao=results[0].chazao,
                    chake=results[0].chake,
                    datamanager=results[0].datamanager,
                    job=infoForm["job"],
                    department_name=results[0].name,
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
            assert flaskRequest.json is not None
            student_id = flaskRequest.json["StudentID"]
            name = flaskRequest.json["Name"]
            school = flaskRequest.json["School"]
            hometown = flaskRequest.json["Hometown"]
            # ===================
            # 匹配结果不唯一则报错
            results = (
                session.query(SQL_UserProfile).filter_by(student_id=student_id).all()
            )
            if len(results) != 1:
                raise PermissionDenyError(
                    "Information is wrong.",
                    filename=__file__,
                    line=sys._getframe().f_lineno,
                )
            results = session.query(SQL_User).filter_by(student_id=student_id).one()
            if (
                results.name != name
                or results.profile.college.name != school
                or results.profile.hometown != hometown
            ):
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
            user_obj = session.get(
                SQL_UserProfile, CustomSession.getSession().get("userID")
            )
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
                    ethnicity=infoDict.get("ethnicity"),
                    dormitory_yuan=infoDict.get("dormitory_yuan"),
                    dormitory_dong=infoDict.get("dormitory_dong"),
                    dormitory_hao=infoDict.get("dormitory_hao"),
                    hometown=infoDict.get("hometown"),
                    phone=infoDict.get("phone"),
                    qq=infoDict.get("qq"),
                )
                session.add(profile)

                # === 插入 PaymentInfo 表信息 ===
                payment = SQL_PaymentInfo(
                    student_id=infoDict["studentID"],
                    recipient_id=infoDict["studentID"],
                    recipient_name=infoDict["name"],
                    card_number=infoDict["bank"],
                    is_registered_poor=infoDict["subsidyDossier"],
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
                    mon="1000",
                    tue="0200",
                    wed="00300",
                    thu="00010",
                    fri="00002",
                    sat="30000",
                    sun="00000",
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
            "chazao": info["chazao"],
            "chake": info["chake"],
            "datamanager": info["datamanager"],
            "job": info["job"],
            "display_title": info["display_title"],
        }

    @staticmethod
    def getContact(db_session: Session | None = None) -> list[dict] | tuple[dict]:
        # =====================================
        # 如果提供已经建立的数据库连接，则直接使用
        session_context = (
            SessionLocal() if db_session is None else nullcontext(db_session)
        )
        with session_context as session:
            # =============
            # 获取通讯录视图
            results = session.query(SQL_ContactView).all()
            results = [
                {
                    "id": i.id,
                    "department": i.department,
                    "name": i.name,
                    "gender": i.gender,
                    "phone": i.phone,
                    "qq": i.qq,
                    "job": i.job,
                    "display_title": i.display_title,
                    "department_id": i.department_id,
                }
                for i in results
            ]
            return results

    @staticmethod
    def getPersonalInfo(db_session: Session | None = None) -> tuple[dict] | list[dict]:
        # =====================================
        # 如果提供已经建立的数据库连接，则直接使用
        session_context = (
            SessionLocal() if db_session is None else nullcontext(db_session)
        )
        with session_context as session:
            # ============
            # 获取个人信息
            results = (
                session.query(SQL_User)
                .filter_by(student_id=CustomSession.getSession()["userID"])
                .one()
            )
            return [
                {
                    "student_id": results.student_id,
                    "name": results.name,
                    "gender": results.gender,
                    "ethnicity": results.profile.ethnicity,
                    "hometown": results.profile.hometown,
                    "phone": results.profile.phone,
                    "qq": results.profile.qq,
                    "campus": results.profile.campus.name,
                    "school": results.profile.college.name,
                    "dormitory_yuan": results.profile.dormitory_yuan,
                    "dormitory_dong": results.profile.dormitory_dong,
                    "dormitory_hao": results.profile.dormitory_hao,
                    "application_student_id": results.profile.payment_info.recipient_id,
                    "application_name": results.profile.payment_info.recipient_name,
                    "application_bankcard": results.profile.payment_info.card_number,
                    "subsidy_dossier": results.profile.payment_info.is_registered_poor,
                }
            ]

    @staticmethod
    def changePersonalInfo(infoDict: dict, db_session: Session | None = None) -> str:
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
                != 1
            ):
                raise IllegalValueError(
                    "Student ID not exists.",
                    filename=__file__,
                    line=sys._getframe().f_lineno,
                )
            # ============
            # 更新个人信息
            user = (
                session.query(SQL_User)
                .filter(SQL_User.student_id == infoDict["studentID"])
                .one()
            )

            user.name = infoDict["name"]
            user.student_id = infoDict["studentID"]
            user.gender = infoDict["gender"]

            user.profile.ethnicity = infoDict["ethnicity"]
            user.profile.hometown = infoDict["hometown"]
            user.profile.phone = infoDict["phone"]
            user.profile.qq = infoDict["qq"]
            user.profile.campus_id = infoDict["campusID"]
            user.profile.college_id = infoDict["schoolID"]
            user.profile.dormitory_yuan = infoDict["dormitory_yuan"]
            user.profile.dormitory_dong = infoDict["dormitory_dong"]
            user.profile.dormitory_hao = infoDict["dormitory_hao"]

            user.profile.payment_info.recipient_id = infoDict["application_student_id"]
            user.profile.payment_info.recipient_name = infoDict["application_name"]
            user.profile.payment_info.card_number = infoDict["application_bankcard"]
            user.profile.payment_info.is_registered_poor = infoDict["subsidyDossier"]

            user.profile.payment_info.recipient_id = infoDict["application_student_id"]
            user.profile.payment_info.recipient_name = infoDict["application_name"]
            user.profile.payment_info.card_number = infoDict["application_bankcard"]
            user.profile.payment_info.is_registered_poor = infoDict["subsidyDossier"]

            if "password" in infoDict.keys():
                user.profile.credential.password_hash = hashlib.sha512(
                    infoDict["password"].encode()
                ).digest()

            session.commit()

        return "刷新页面以更新数据，如仍有数据未更新，请退出重新登录。如有问题请联系管理员。"

    @staticmethod
    def getEmptyTimeInfo(db_session: Session | None = None) -> dict[str, Any]:
        # =====================================
        # 如果提供已经建立的数据库连接，则直接使用
        session_context = (
            SessionLocal() if db_session is None else nullcontext(db_session)
        )
        with session_context as session:
            # =============
            # 获取空课时间表
            results = (
                session.query(SQL_EmptyTime)
                .filter_by(student_id=CustomSession.getSession()["userID"])
                .all()
            )
            if len(results) != 1:
                raise IllegalValueError(
                    "Empty time table not found for the student.",
                    filename=__file__,
                    line=sys._getframe().f_lineno,
                )
            results = results[0]
            results = {
                "mon": results.mon,
                "tue": results.tue,
                "wed": results.wed,
                "thu": results.thu,
                "fri": results.fri,
                "sat": results.sat,
                "sun": results.sun,
            }
            # ===================
            # 按单双周等需求预处理
            week_name = ("mon", "tue", "wed", "thu", "fri", "sat", "sun")
            time_period = ("1-2", "3-4", "5-6", "7-8", "9-11")
            empty_table = {
                "odd": numpy.zeros(
                    (len(time_period), len(week_name)), dtype="int8"
                ).tolist(),
                "even": numpy.zeros(
                    (len(time_period), len(week_name)), dtype="int8"
                ).tolist(),
            }

            for i, name in enumerate(week_name):
                for j in range(len(results[name])):
                    empty_table["even"][j][i] = (
                        0 if results[name][j] in ["0", "1"] else 1
                    )
                    empty_table["odd"][j][i] = (
                        0 if results[name][j] in ["0", "2"] else 1
                    )

            return empty_table

    @staticmethod
    def getWorkBasicInfo(db_session: Session | None = None) -> tuple[dict] | list[dict]:
        # =====================================
        # 如果提供已经建立的数据库连接，则直接使用
        session_context = (
            SessionLocal() if db_session is None else nullcontext(db_session)
        )
        with session_context as session:
            # ===============
            # 获取岗位基本信息
            results = (
                session.query(SQL_GroupMember)
                .join(SQL_Group, SQL_Group.id == SQL_GroupMember.group_id)
                .filter(
                    SQL_GroupMember.student_id == CustomSession.getSession()["userID"]
                )
                .all()
            )
            results = [
                {
                    "name": i.group.name,
                    "department_id": i.group.id,
                    "job": i.role.value,
                    "wage": i.wage,
                    "remark": i.remark,
                }
                for i in results
            ]
            # ==========================
            # 根据当前登录岗位补充岗位信息
            loginDepartmentID = CustomSession().getSession()["department_id"]
            loginJob = CustomSession().getSession()["job"]
            for work in results:
                if (
                    work["department_id"] == loginDepartmentID
                    and work["job"] == loginJob
                ):
                    work["loginWork"] = True
                else:
                    work["loginWork"] = False
            return results

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

    @staticmethod
    def getRecentSchedule(db_session: Session | None = None) -> dict:
        # =====================================
        # 如果提供已经建立的数据库连接，则直接使用
        session_context = (
            SessionLocal() if db_session is None else nullcontext(db_session)
        )
        with session_context as session:
            # =================================
            # 获取当日日期后5天日期，和前10日日期
            currentDate = datetime.date.today()
            post5Date = currentDate + datetime.timedelta(days=5)
            past10Date = currentDate - datetime.timedelta(days=10)
            # ======================================
            # 查询日期范围内，此组员的早自习排班信息
            results = (
                session.query(
                    SQL_StudySchedule.date,
                    SQL_Classroom.building,
                    SQL_Classroom.area,
                    SQL_Classroom.room_number,
                    SQL_CheckInData.status,
                )
                .select_from(SQL_CheckInTask)
                .join(
                    SQL_StudySchedule,
                    SQL_CheckInTask.schedule,
                )
                .join(SQL_Classroom, SQL_StudySchedule.classroom)
                .join(SQL_CheckInData, SQL_CheckInTask.data, isouter=True)
                .filter(
                    SQL_CheckInTask.student_id
                    == CustomSession().getSession()["userID"],
                    SQL_StudySchedule.date.between(past10Date, post5Date),
                )
                .order_by(
                    SQL_StudySchedule.date.desc(),  # 主排序键：日期降序
                    SQL_StudySchedule.classroom_id.asc(),  # 次排序键：教室ID升序
                )
                .all()
            )
            selfstudyHistoryData = [
                {
                    "date": date.isoformat(),
                    "classroom_name": building + area + room_number,
                    "status": status,
                }
                for (date, building, area, room_number, status) in results
            ]
            # ======================================
            # 查询日期范围内，此组员的查课排班信息
            results = (
                session.query(
                    SQL_CourseSchedule.date,
                    SQL_CourseSchedule.time_slot,
                    SQL_Classroom.building,
                    SQL_Classroom.area,
                    SQL_Classroom.room_number,
                    SQL_InspectionData.status,
                )
                .select_from(SQL_InspectionTask)
                .join(SQL_CourseSchedule, SQL_InspectionTask.schedule)
                .join(SQL_Classroom, SQL_CourseSchedule.classroom)
                .join(SQL_InspectionData, SQL_InspectionTask.data, isouter=True)
                .filter(
                    SQL_InspectionTask.student_id
                    == CustomSession().getSession()["userID"],
                    SQL_CourseSchedule.date.between(past10Date, post5Date),
                )
                .order_by(
                    SQL_CourseSchedule.date.desc(),  # 主排序键：日期降序
                    SQL_CourseSchedule.classroom_id.asc(),  # 次排序键：教室ID升序
                )
                .all()
            )
            coursesHistoryData = [
                {
                    "date": date.isoformat(),
                    "time_slot": slot,
                    "classroom_name": building + area + room_number,
                    "status": status,
                }
                for (date, slot, building, area, room_number, status) in results
            ]
            # ========
            # 整合数据
            results = {"selfstudy": selfstudyHistoryData, "courses": coursesHistoryData}
            # ========
            # 返回数据
            return results

    @staticmethod
    def getSelfstudyCheckData(db_session: Session | None = None) -> list[dict]:
        # =====================================
        # 如果提供已经建立的数据库连接，则直接使用
        session_context = (
            SessionLocal() if db_session is None else nullcontext(db_session)
        )
        with session_context as session:
            # ================================
            # 获取当日日期后3日日期，和前5日日期
            past5Date = (datetime.date.today() - datetime.timedelta(days=5)).isoformat()
            post3Date = (datetime.date.today() + datetime.timedelta(days=3)).isoformat()
            # ======================================
            # 查询日期范围内，此组员的早自习排班信息
            results = (
                session.query(
                    SQL_StudySchedule.date,
                    SQL_CheckInTask.id,
                    SQL_Classroom.id,
                    SQL_Classroom.building,
                    SQL_Classroom.area,
                    SQL_Classroom.room_number,
                    SQL_StudySchedule.expected_headcount,
                    SQL_CheckInData.first_count,
                    SQL_CheckInData.second_count,
                    SQL_CheckInData.leave,
                    SQL_CheckInData.late,
                    SQL_CheckInData.early_leave,
                    SQL_CheckInData.absentee,
                    SQL_CheckInData.absent_list,
                    SQL_CheckInData.leave_list,
                    SQL_StudySchedule.remark,
                    SQL_CheckInData.remarks,
                    SQL_CheckInData.status,
                )
                .select_from(SQL_CheckInTask)
                .join(
                    SQL_StudySchedule,
                    SQL_CheckInTask.schedule,
                )
                .join(SQL_Classroom, SQL_StudySchedule.classroom)
                .join(SQL_CheckInData, SQL_CheckInTask.data, isouter=True)
                .filter(
                    SQL_CheckInTask.student_id
                    == CustomSession().getSession()["userID"],
                    SQL_StudySchedule.date.between(past5Date, post3Date),
                )
                .order_by(
                    SQL_StudySchedule.date.desc(),  # 主排序键：日期降序
                    SQL_StudySchedule.classroom_id.asc(),  # 次排序键：教室ID升序
                )
                .all()
            )
            # ========
            # 整合数据
            results = [
                {
                    "date": date.isoformat(),
                    "task_id": task_id,
                    "classroom_id": classroom_id,
                    "classroom_name": building + area + room_number,
                    "expected_headcount": expected_headcount,
                    "first_count": first_count,
                    "second_count": second_count,
                    "leave": leave,
                    "late": late,
                    "early_leave": early_leave,
                    "absentee": absentee,
                    "absent_list": json.loads(absent_list) if absent_list else None,
                    "leave_list": json.loads(leave_list) if leave_list else None,
                    "task_remark": task_remark,
                    "remark": remark,
                    "status": status,
                }
                for (
                    date,
                    task_id,
                    classroom_id,
                    building,
                    area,
                    room_number,
                    expected_headcount,
                    first_count,
                    second_count,
                    leave,
                    late,
                    early_leave,
                    absentee,
                    absent_list,
                    leave_list,
                    task_remark,
                    remark,
                    status,
                ) in results
            ]
            # ========
            # 返回数据
            return results

    @staticmethod
    def submitSelfstudyRecord(
        infoForm: dict, db_session: Session | None = None
    ) -> None:
        # =====================================
        # 如果提供已经建立的数据库连接，则直接使用
        session_context = (
            SessionLocal() if db_session is None else nullcontext(db_session)
        )
        with session_context as session:
            # ==========================================
            # 查找早自习排班编号对应的实际查早组员是否为本人
            student_id = CustomSession().getSession()["userID"]  # 登录用户ID
            task_id = infoForm.get("task_id")
            checkin_task = (
                session.query(SQL_CheckInTask)
                .filter(SQL_CheckInTask.id == task_id)
                .one_or_none()
            )
            if not checkin_task:
                raise IllegalValueError(
                    "无效的早自习任务编号",
                    filename=__file__,
                    line=sys._getframe().f_lineno,
                )
            if checkin_task.student_id != student_id:
                raise PermissionDenyError(
                    "无权提交他人任务的数据",
                    filename=__file__,
                    line=sys._getframe().f_lineno,
                )
            # ============
            # 删除已有数据
            existed_data = (
                session.query(SQL_CheckInData)
                .filter(SQL_CheckInData.task_id == task_id)
                .one_or_none()
            )
            if existed_data:
                session.delete(existed_data)
                session.flush()  # 立即删除避免唯一限制冲突
            # ============
            # 整合已有数据
            absent_list_str = json.dumps(
                infoForm.get("absent_list", []), ensure_ascii=False
            )
            leave_list_str = json.dumps(
                infoForm.get("leave_list", []), ensure_ascii=False
            )
            check_data = SQL_CheckInData(
                task_id=task_id,
                first_count=infoForm.get("first_count", 0),
                second_count=infoForm.get("second_count", 0),
                leave=infoForm.get("leave", 0),
                late=infoForm.get("late", 0),
                absentee=infoForm.get("absentee", 0),
                early_leave=infoForm.get("early_leave", 0),
                absent_list=absent_list_str,
                leave_list=leave_list_str,
                remarks=infoForm.get("remark", ""),
                status="pending",
            )

            # ========
            # 提交数据
            session.add(check_data)
            session.commit()

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
