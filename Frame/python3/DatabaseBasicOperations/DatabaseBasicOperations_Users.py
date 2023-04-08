import sys
import json
import numpy
import hashlib
import datetime
from typing import Any
import flask
from flask import Request
from Frame.python3.BaseComponents.ClientInfo import ClientInfo
from Frame.python3.BaseComponents.CustomSession import CustomSession
from Frame.python3.BaseComponents.DatabaseConnector import DatabaseConnector
from Frame.python3.BaseComponents.CustomError import PermissionDenyError, IllegalValueError, DatabaseRuntimeError


def LoginDeviceRecorder(infoForm: dict, loginResult: bool, databaseConnector: DatabaseConnector | None = None) -> None:
    """Detect login device, login time, and login successfully or not. And record info in database.

    Args:
        formDict (dict): flask.request.form
        loginResult (bool): True when login successfully, and False when login error like username not exists or wrong password.
        database (DatabaseConnector): Reuse exist database connection.

    Returns:
        dict[str,str]: Result of recode process. Not used currently.
    """
    # =====================================
    # 如果提供已经建立的数据库连接，则直接使用
    if databaseConnector is None:
        database = DatabaseConnector()
        database.startCursor()
    else:
        database = databaseConnector
    # ========================================
    # 获取客户端信息，并补充提供登录记录的额外信息
    clientInfo = ClientInfo.getInfo()
    clientInfo["studentID"] = infoForm["StudentID"]
    clientInfo["time"] = datetime.datetime.now().isoformat()
    clientInfo["login_result"] = loginResult
    clientInfo["address"] = str(clientInfo["address"])
    clientInfo["department_id"] = infoForm["department_id"]
    clientInfo["job"] = infoForm["job"]
    # ===================================
    # 插入数据库，使用IGNORE参数忽略主键重复
    _ = database.execute(
        "INSERT IGNORE INTO LogInfo (student_id,agent,ip,address,language,time, department_id, job, login_result) \
        VALUES (%(studentID)s,%(agent)s,%(IP)s,%(address)s,%(language)s,%(time)s,%(department_id)s,%(job)s,%(login_result)s);",
        clientInfo)


class DatabaseBasicOperations_Users:
    @staticmethod
    def login(flaskRequest: Request, databaseConnector: DatabaseConnector | None = None) -> None:
        # =====================================
        # 如果提供已经建立的数据库连接，则直接使用
        if databaseConnector is None:
            database = DatabaseConnector()
            database.startCursor()
        else:
            database = databaseConnector
        # ==========================
        # 根据提供的密码和学号进行查询
        infoForm = dict(flaskRequest.form)
        infoForm["department_id"] = 0
        infoForm["job"] = 0
        DBAffectRows = database.execute(
            "SELECT student_id FROM `Password` WHERE student_id = %s and passhash = %s;",
            (infoForm["StudentID"], hashlib.sha512(infoForm["Password"].encode()).digest()))
        database.fetchall()
        # =================================
        # 必须为单一记录才判定为用户名密码正确
        # 即使登录失败，也记录用户登录失败信息
        if DBAffectRows != 1:
            LoginDeviceRecorder(infoForm, False, database)
            raise PermissionDenyError(
                "Username or password error.", filename=__file__, line=sys._getframe().f_lineno)
        # ===============
        # 记录用户登录信息
        LoginDeviceRecorder(infoForm, True, database)
        # ====================
        # 获取姓名以设置Session
        studentName = DatabaseBasicOperations_Users.getName(
            infoForm["StudentID"], database)
        flask.g.isLogin = True
        CustomSession.setSession(
            studentID=infoForm["StudentID"], name=studentName, logTime=datetime.datetime.now().isoformat())

    @staticmethod
    def getLoginWorks(databaseConnector: DatabaseConnector | None = None) -> list[dict] | tuple[dict]:
        # =====================================
        # 如果提供已经建立的数据库连接，则直接使用
        if databaseConnector is None:
            database = DatabaseConnector()
            database.startCursor()
        else:
            database = databaseConnector
        # ========================
        # 查询工作表单获取可登录岗位
        _ = database.execute(
            "SELECT Work.department_id as department_id,name,job FROM `Work`,`Department` \
            WHERE Department.department_id = Work.department_id AND \
                student_id = %(userID)s AND \
                Work.department_id != 0 \
            ORDER BY Work.department_id ASC, job DESC;", CustomSession.getSession())
        # =================
        # 整理查询数据并返回
        results = database.fetchall()
        if len(results) == 0:
            results = ({'department_id': 0, "job": 0, "name": "预备队员"},)
        return results

    @staticmethod
    def loginAsSpecifiedWork(flaskRequest: Request, databaseConnector: DatabaseConnector | None = None) -> str:
        # =====================================
        # 如果提供已经建立的数据库连接，则直接使用
        if databaseConnector is None:
            database = DatabaseConnector()
            database.startCursor()
        else:
            database = databaseConnector
        # ===============================================
        # 查询工作表单验证可登录岗位
        # 如果登录部门是0岗位也是0，说明是预备队员，直接放过
        infoForm = dict(flaskRequest.form)
        infoForm["department_id"] = int(infoForm["department_id"])
        infoForm["job"] = int(infoForm["job"])
        infoForm["StudentID"] = CustomSession.getSession()['userID']
        infoForm["StudentName"] = CustomSession.getSession()['userName']
        DBAffectedRows = database.execute(
            "SELECT * FROM `Work` \
            WHERE department_id = %(department_id)s AND job = %(job)s AND student_id = %(StudentID)s;",
            infoForm)
        database.fetchall()
        if (infoForm["department_id"] != 0 or infoForm["job"] != 0) and DBAffectedRows != 1:
            raise IllegalValueError(
                "The work you want to login is not permitted.", filename=__file__, line=sys._getframe().f_lineno)
        # ===================
        # 添加新的LogInfo记录
        LoginDeviceRecorder(infoForm, True, database)
        # ==============================
        # 查询部门名称，并更新Session信息
        DBAffectedRows = database.execute(
            "SELECT name FROM `Department` WHERE department_id = %(department_id)s AND department_id != 0;", infoForm)
        if DBAffectedRows <= 0:
            database.fetchall()
            name = '预备队员'
        else:
            name = database.fetchall()[0]['name']
        CustomSession.setSession(
            studentID=infoForm["StudentID"], name=infoForm["StudentName"], logTime=datetime.datetime.now().isoformat(), department_id=infoForm["department_id"], job=infoForm["job"], department_name=name)
        flask.g.isLogin = True

        return "/Users/UserCenter/index.html"

    @staticmethod
    def getName(studentId: str, databaseConnector: DatabaseConnector | None = None) -> str:
        # =====================================
        # 如果提供已经建立的数据库连接，则直接使用
        if databaseConnector is None:
            database = DatabaseConnector()
            database.startCursor()
        else:
            database = databaseConnector
        # ===================
        # 利用学号查询对应姓名
        DBAffectRows = database.execute(
            "SELECT `name` FROM `MemberBasic` WHERE student_id = %s;",
            (studentId,))
        DBResult = database.fetchall()
        # ===============
        # 结果不唯一则报错
        if DBAffectRows != 1:
            raise IllegalValueError(
                "StudentID not found.", filename=__file__, line=sys._getframe().f_lineno)

        return DBResult[0]["name"]

    @staticmethod
    def resetPassword(flaskRequest: Request, databaseConnector: DatabaseConnector | None = None) -> None:
        # =====================================
        # 如果提供已经建立的数据库连接，则直接使用
        if databaseConnector is None:
            database = DatabaseConnector()
            database.startCursor()
        else:
            database = databaseConnector
        # =====================
        # 利用提供的信息匹配用户
        DBAffectRows = database.execute(
            "SELECT `MemberBasic`.student_id FROM `MemberBasic` \
            LEFT JOIN `MemberExtend` ON `MemberExtend`.student_id = `MemberBasic`.student_id \
            LEFT JOIN `School` ON `School`.school_id = `MemberExtend`.school_id \
            WHERE MemberBasic.student_id = %(StudentID)s and MemberBasic.name = %(Name)s \
                and School.name = %(School)s and hometown = %(Hometown)s;",
            flaskRequest.form)
        database.fetchall()
        # ===================
        # 匹配结果不唯一则报错
        if DBAffectRows != 1:
            raise PermissionDenyError(
                "Information is wrong.", filename=__file__, line=sys._getframe().f_lineno)
        # =====================
        # 匹配到则重置密码为学号
        DBAffectRows = database.execute(
            "UPDATE `Password` SET passhash = %s WHERE student_id = %s;",
            (hashlib.sha512(flaskRequest.form["StudentID"].encode()).digest(), flaskRequest.form["StudentID"]))

    @staticmethod
    def deletePersonalInfo(databaseConnector: DatabaseConnector | None = None) -> None:
        # =====================================
        # 如果提供已经建立的数据库连接，则直接使用
        if databaseConnector is None:
            database = DatabaseConnector()
            database.startCursor()
        else:
            database = databaseConnector
        # ==========================
        # 清除当前登录会话的用户的信息
        DBAffectRows = database.execute(
            "DELETE FROM `MemberExtend` WHERE student_id=%(userID)s;", CustomSession.getSession())

        if DBAffectRows != 1:
            raise IllegalValueError(
                "Student ID not exists.", filename=__file__, line=sys._getframe().f_lineno)

    @staticmethod
    def register(infoDict: dict, databaseConnector: DatabaseConnector | None = None) -> None:
        # =====================================
        # 如果提供已经建立的数据库连接，则直接使用
        if databaseConnector is None:
            database = DatabaseConnector()
            database.startCursor()
        else:
            database = databaseConnector
        # =================
        # 检查学号是否已存在
        DBAffectRows = database.execute(
            "SELECT student_id FROM `MemberExtend` WHERE student_id = %(studentID)s;",
            infoDict)
        database.fetchall()
        if DBAffectRows == 1:
            raise PermissionDenyError(
                "Student ID exists.", filename=__file__, line=sys._getframe().f_lineno)
        # =================
        # 尝试插入或更新信息
        DBAffectRows = database.execute(
            "INSERT INTO `MemberBasic` (student_id,name,gender,reason) \
                VALUES (%(studentID)s,%(name)s,%(gender)s,'') \
            ON DUPLICATE KEY \
                UPDATE name=%(name)s, gender=%(gender)s;",
            infoDict, autoCommit=False)
        if DBAffectRows not in [0, 1, 2]:
            database.rollback()
            raise DatabaseRuntimeError(
                "Insert or Update member basic info error.", filename=__file__, line=sys._getframe().f_lineno)
        DBAffectRows = database.execute(
            "INSERT INTO `MemberExtend` (student_id,ethnicity,hometown,phone,qq,school_id, \
                                        dormitory_yuan,dormitory_dong,dormitory_hao,remark) \
            VALUES (%(studentID)s,%(ethnicity)s,%(hometown)s,%(phone)s,%(qq)s,%(schoolID)s, \
                    %(dormitory_yuan)s,%(dormitory_dong)s,%(dormitory_hao)s,'');",
            infoDict, autoCommit=False)
        if DBAffectRows != 1:
            database.rollback()
            raise DatabaseRuntimeError(
                "Insert member extend info error.", filename=__file__, line=sys._getframe().f_lineno)
        DBAffectRows = database.execute(
            "INSERT INTO `WageInfo` (student_id,application_student_id,application_name, \
                                    application_bankcard,subsidy_dossier,remark) \
            VALUES (%(studentID)s,%(studentID)s,%(name)s,%(bank)s,%(subsidyDossier)s,'');",
            infoDict, autoCommit=False)
        if DBAffectRows != 1:
            database.rollback()
            raise DatabaseRuntimeError(
                "Insert wage info error.", filename=__file__, line=sys._getframe().f_lineno)
        DBAffectRows = database.execute(
            "INSERT INTO `Password` (student_id,passhash) \
            VALUES (%s,%s);",
            (infoDict["studentID"], hashlib.sha512(
                infoDict["password"].encode()).digest()),
            autoCommit=False)
        if DBAffectRows != 1:
            database.rollback()
            raise DatabaseRuntimeError(
                "Insert password info error.", filename=__file__, line=sys._getframe().f_lineno)
        DBAffectRows = database.execute(
            "INSERT INTO `EmptyTime` (student_id,remark) \
            VALUES (%(studentID)s,'');",
            infoDict, autoCommit=False)
        if DBAffectRows != 1:
            database.rollback()
            raise DatabaseRuntimeError(
                "Insert empty time info error.", filename=__file__, line=sys._getframe().f_lineno)
        database.commit()

    @staticmethod
    def topbarInfo() -> str:
        # ============================
        # 查询Session保存的登录岗位信息
        return {"name": CustomSession.getSession()["userName"], "department_id": CustomSession.getSession()["department_id"], "department_name": CustomSession.getSession()["department_name"], "job": CustomSession.getSession()["job"]}

    @staticmethod
    def getContact(databaseConnector: DatabaseConnector | None = None) -> list[dict] | tuple[dict]:
        # =====================================
        # 如果提供已经建立的数据库连接，则直接使用
        if databaseConnector is None:
            database = DatabaseConnector()
            database.startCursor()
        else:
            database = databaseConnector
        # =============
        # 获取通讯录视图
        database.execute(sql="SELECT * FROM `Contact`;")
        return database.fetchall()

    @staticmethod
    def getPersonalInfo(databaseConnector: DatabaseConnector | None = None) -> tuple[dict] | list[dict]:
        # =====================================
        # 如果提供已经建立的数据库连接，则直接使用
        if databaseConnector is None:
            database = DatabaseConnector()
            database.startCursor()
        else:
            database = databaseConnector
        # ============
        # 获取个人信息
        database.execute(
            sql="SELECT `MemberExtend`.student_id as student_id, `MemberBasic`.name as name, \
                    gender, ethnicity, hometown, phone, qq, `MemberExtend`.remark as infoRemark, \
                    campus, `School`.`name` as school, dormitory_yuan, dormitory_dong, dormitory_hao, \
                    application_student_id, application_name, application_bankcard, subsidy_dossier, \
                    `WageInfo`.remark as wageRemark \
                FROM `MemberExtend` \
                LEFT JOIN `MemberBasic` ON `MemberExtend`.`student_id` = `MemberBasic`.`student_id` \
                LEFT JOIN `School` ON `MemberExtend`.`school_id` = `School`.`school_id` \
                LEFT JOIN `WageInfo` ON `MemberExtend`.`student_id` = `WageInfo`.`student_id` \
                WHERE `MemberExtend`.`student_id` = %(userID)s;",
            data=CustomSession.getSession()
        )
        return database.fetchall()

    # TODO: Not Finished Yet !!!
    @staticmethod
    def changePersonalInfo(infoDict: dict, databaseConnector: DatabaseConnector | None = None) -> str:
        # =====================================
        # 如果提供已经建立的数据库连接，则直接使用
        if databaseConnector is None:
            database = DatabaseConnector()
            database.startCursor()
        else:
            database = databaseConnector
        # ======================
        # 检查个人信息数据是否存在
        DBAffectRows = database.execute(
            "SELECT student_id FROM `MemberExtend` WHERE student_id = %(studentID)s;", infoDict)
        database.fetchall()
        if DBAffectRows != 1:
            raise IllegalValueError(
                "Student ID not exists.", filename=__file__, line=sys._getframe().f_lineno)
        # ============
        # 更新个人信息
        DBAffectRows = database.execute(
            "UPDATE `MemberBasic` SET name=%(name)s, gender=%(gender)s WHERE student_id=%(studentID)s;",
            infoDict, autoCommit=False)
        if DBAffectRows not in [0, 1]:
            database.rollback()
            raise DatabaseRuntimeError(
                "Update member basic info error.", filename=__file__, line=sys._getframe().f_lineno)
        DBAffectRows = database.execute(
            "UPDATE `MemberExtend` SET \
                ethnicity=%(ethnicity)s, hometown=%(hometown)s, phone=%(phone)s, \
                qq=%(qq)s, school_id=%(schoolID)s, dormitory_yuan=%(dormitory_yuan)s, \
                dormitory_dong=%(dormitory_dong)s, dormitory_hao=%(dormitory_hao)s, \
                remark=%(infoRemark)s \
            WHERE student_id=%(studentID)s;",
            infoDict, autoCommit=False)
        if DBAffectRows not in [0, 1]:
            database.rollback()
            raise DatabaseRuntimeError(
                "Update member extend info error.", filename=__file__, line=sys._getframe().f_lineno)
        DBAffectRows = database.execute(
            "UPDATE `WageInfo` SET \
                application_student_id=%(application_student_id)s, application_name=%(application_name)s, \
                application_bankcard=%(application_bankcard)s,subsidy_dossier=%(subsidyDossier)s, \
                remark=%(wageRemark)s \
            WHERE student_id=%(studentID)s;",
            infoDict, autoCommit=False)
        if DBAffectRows not in [0, 1]:
            database.rollback()
            raise DatabaseRuntimeError(
                "Update wage info error.", filename=__file__, line=sys._getframe().f_lineno)
        if "password" in infoDict.keys():
            DBAffectRows = database.execute(
                "UPDATE `Password` SET passhash=%s \
                WHERE student_id=%s",
                (hashlib.sha512(infoDict["password"].encode()
                                ).digest(), infoDict["studentID"]),
                autoCommit=False)
            if DBAffectRows not in [0, 1]:
                database.rollback()
                raise DatabaseRuntimeError(
                    "Update password info error.", filename=__file__, line=sys._getframe().f_lineno)
        database.commit()

        return "刷新页面以更新数据，如仍有数据未更新，请退出重新登录。如有问题请联系管理员。"

    @staticmethod
    def getEmptyTimeInfo(databaseConnector: DatabaseConnector | None = None) -> dict[str, Any]:
        # =====================================
        # 如果提供已经建立的数据库连接，则直接使用
        if databaseConnector is None:
            database = DatabaseConnector()
            database.startCursor()
        else:
            database = databaseConnector
        # =============
        # 获取空课时间表
        DBAffectedRows = database.execute(
            sql="SELECT student_id,mon,tue,wed,thu,fri,sat,sun,remark FROM EmptyTime WHERE student_id=%(userID)s;",
            data=CustomSession.getSession()
        )
        if DBAffectedRows != 1:
            raise IllegalValueError(
                "Empty time table not found for the student.", filename=__file__, line=sys._getframe().f_lineno)
        results = database.fetchall()[0]
        # ===================
        # 按单双周等需求预处理
        week_name = ("mon", "tue", "wed", "thu", "fri", "sat", "sun")
        time_period = ("1-2", "3-4", "5-6", "7-8", "9-11")
        empty_table = {"odd": numpy.zeros((len(time_period), len(week_name)), dtype='int8').tolist(),
                       "even": numpy.zeros((len(time_period), len(week_name)), dtype='int8').tolist(),
                       "remark": results["remark"]}
        for i, name in enumerate(week_name):
            for j in range(len(results[name])):
                empty_table["even"][j][i] = 0 if results[name][j] in [
                    '0', '1'] else 1
                empty_table["odd"][j][i] = 0 if results[name][j] in [
                    '0', '2'] else 1

        return empty_table

    @staticmethod
    def getWorkBasicInfo(databaseConnector: DatabaseConnector | None = None) -> tuple[dict] | list[dict]:
        # =====================================
        # 如果提供已经建立的数据库连接，则直接使用
        if databaseConnector is None:
            database = DatabaseConnector()
            database.startCursor()
        else:
            database = databaseConnector
        # ===============
        # 获取岗位基本信息
        database.execute(
            sql="SELECT name,Department.department_id as department_id,job,wage,Work.remark as remark FROM Work \
                LEFT JOIN Department ON Work.department_id=Department.department_id \
                WHERE student_id=%(userID)s;",
            data=CustomSession.getSession())
        # ==========================
        # 根据当前登录岗位补充岗位信息
        works = database.fetchall()
        loginDepartmentID = CustomSession().getSession()['department_id']
        loginJob = CustomSession().getSession()['job']
        for work in works:
            if work['department_id'] == loginDepartmentID and work['job'] == loginJob:
                work["loginWork"] = True
            else:
                work["loginWork"] = False
        return works

    @staticmethod
    def getScoreDetails(databaseConnector: DatabaseConnector | None = None) -> dict:
        # =====================================
        # 如果提供已经建立的数据库连接，则直接使用
        if databaseConnector is None:
            database = DatabaseConnector()
            database.startCursor()
        else:
            database = databaseConnector
        # ===============
        # 获取个人扣分信息
        DBAffectedRows = database.execute(
            sql="SELECT `date`, `Score`.department_id, `name` AS department_name, variant, reason  FROM `Score` \
                LEFT JOIN Department ON `Score`.department_id=Department.department_id \
                WHERE student_id=%(userID)s \
                ORDER BY `Score`.submission_time DESC;",
            data=CustomSession.getSession())

        returns = dict()
        for row in database.fetchall():
            if row["department_id"] not in returns.keys():
                returns[row["department_id"]] = list()
            if len(returns[row["department_id"]]) > 20:
                continue
            row["date"] = str(row["date"])
            returns[row["department_id"]].append(row)

        return returns

    # TODO: 这里只提供了早自习历史任务，未来加入查课历史任务
    @staticmethod
    def getScheduleHistory(databaseConnector: DatabaseConnector | None = None) -> dict:
        # =====================================
        # 如果提供已经建立的数据库连接，则直接使用
        if databaseConnector is None:
            database = DatabaseConnector()
            database.startCursor()
        else:
            database = databaseConnector
        # =========================
        # 获取当日日期，和前20日日期
        currentDate = datetime.datetime.now()
        past20Date = currentDate - datetime.timedelta(days=20)
        currentDate = currentDate.strftime("%Y-%m-%d")
        past20Date = past20Date.strftime("%Y-%m-%d")
        # ==============================
        # 查询日期范围内，此组员的排班信息
        DBAffectedRows = database.execute(
            sql="SELECT selfstudy_id, date, classroom_name \
                FROM SelfstudyCheckActualView \
                WHERE SelfstudyCheckActualView.actual_student_id = %s \
                    AND (SelfstudyCheckActualView.date >= %s AND SelfstudyCheckActualView.date <= %s) \
                ORDER BY SelfstudyCheckActualView.date DESC, SelfstudyCheckActualView.classroom_name ASC;",
            data=(CustomSession.getSession()["userID"], past20Date, currentDate))
        historyData = list(database.fetchall())
        # ===================
        # 查询排班对应的数据表
        for one_history in historyData:
            # ==============================
            # 处理一下日期，转化为可以JSON化的
            one_history["date"] = one_history["date"].strftime("%Y-%m-%d")
            # ======
            # 数据表
            DBAffectedRows = database.execute(
                sql="SELECT groupleader_recheck \
                    FROM SelfstudyCheckData \
                    WHERE selfstudy_id = %s \
                    ORDER BY submission_time DESC \
                    LIMIT 1;",
                data=(one_history['selfstudy_id'],))
            if DBAffectedRows == 0:
                database.fetchall()
                one_history.update({"groupleader_recheck": 0})
                one_history['submitted'] = False
                one_history['recheck'] = False
            else:
                tmpData = database.fetchall()[0]
                one_history.update(tmpData)
                one_history['submitted'] = True
                one_history['recheck'] = int(
                    tmpData["groupleader_recheck"]) == 1
        # ========
        # 整合数据
        results = dict()
        results['selfstudy'] = historyData
        results['courses'] = list()
        # ========
        # 返回数据
        return results

    @staticmethod
    def getSelfstudyCheckData(databaseConnector: DatabaseConnector | None = None) -> dict:
        # =====================================
        # 如果提供已经建立的数据库连接，则直接使用
        if databaseConnector is None:
            database = DatabaseConnector()
            database.startCursor()
        else:
            database = databaseConnector
        # ================================
        # 获取当日日期后3日日期，和前5日日期
        past5Date = (datetime.datetime.now() -
                     datetime.timedelta(days=5)).strftime("%Y-%m-%d")
        post3Date = (datetime.datetime.now() +
                     datetime.timedelta(days=3)).strftime("%Y-%m-%d")
        # ==============================
        # 查询日期范围内，此组员的排班信息
        DBAffectedRows = database.execute(
            sql="SELECT selfstudy_id, date, classroom_name, campus, school_name, student_supposed \
                FROM SelfstudyCheckActualView \
                WHERE SelfstudyCheckActualView.actual_student_id = %s \
                    AND (SelfstudyCheckActualView.date >= %s AND SelfstudyCheckActualView.date <= %s) \
                ORDER BY SelfstudyCheckActualView.date DESC, SelfstudyCheckActualView.classroom_name ASC;",
            data=(CustomSession.getSession()["userID"], past5Date, post3Date))
        historyData = list(database.fetchall())
        # ===================================
        # 查询排班对应的数据表，并按排班编号整理
        results = list()
        for one_history in historyData:
            # ==============================
            # 处理一下日期，转化为可以JSON化的
            one_history["date"] = one_history["date"].strftime("%Y-%m-%d")
            # ======
            # 数据表
            DBAffectedRows = database.execute(
                sql="SELECT check_result \
                    FROM SelfstudyCheckData \
                    WHERE selfstudy_id = %s \
                    ORDER BY submission_time DESC \
                    LIMIT 1;",
                data=(one_history['selfstudy_id'],))
            if DBAffectedRows == 0:
                database.fetchall()
                selfstudyRecord = "{}"
            else:
                selfstudyRecord = database.fetchall()[0]["check_result"]
            # ======
            # 缺勤表
            DBAffectedRows = database.execute(
                sql="SELECT check_result \
                    FROM SelfstudyCheckAbsent \
                    WHERE selfstudy_id = %s \
                    ORDER BY submission_time DESC \
                    LIMIT 1;",
                data=(one_history['selfstudy_id'],))
            if DBAffectedRows == 0:
                selfstudyAbsentList = "[]"
                database.fetchall()
            else:
                selfstudyAbsentList = database.fetchall()[0]["check_result"]
            # ========
            # 整合数据
            results.append({
                'date': one_history['date'],
                'selfstudy_id': one_history['selfstudy_id'],
                'classroom_name': one_history['classroom_name'],
                'school_name': one_history['school_name'],
                'campus': one_history['campus'],
                'student_supposed': one_history['student_supposed'],
                'record': selfstudyRecord,
                'absent': selfstudyAbsentList
            })
        # ========
        # 返回数据
        return results

    @staticmethod
    def submitSelfstudyRecord(infoForm: dict, databaseConnector: DatabaseConnector | None = None) -> None:
        # =====================================
        # 如果提供已经建立的数据库连接，则直接使用
        if databaseConnector is None:
            database = DatabaseConnector()
            database.startCursor()
        else:
            database = databaseConnector
        # ==========================================
        # 查找早自习排班编号对应的实际查早组员是否为本人
        DBAffectedRows = database.execute(
            sql="SELECT selfstudy_id \
                FROM SelfstudyCheckActualView \
                WHERE SelfstudyCheckActualView.actual_student_id = %s \
                    AND SelfstudyCheckActualView.selfstudy_id = %s;",
            data=(CustomSession.getSession()["userID"], infoForm['selfstudy_id']))
        database.fetchall()
        if DBAffectedRows == 0:
            raise PermissionError(
                "此早自习排班的实际检查组员与本人核验不匹配，请联系管理员.", filename=__file__, line=sys._getframe().f_lineno)
        # ============
        # 提交查早数据
        check_result = json.dumps(infoForm['record'], ensure_ascii=False)
        DBAffectedRows = database.execute(
            sql="INSERT INTO SelfstudyCheckData \
                    (selfstudy_id,check_result,submit_student_id,groupleader_recheck,remark) \
                VALUES \
                    (%s,%s,%s,0,'');",
            data=(infoForm['selfstudy_id'], check_result,
                  CustomSession.getSession()["userID"]),
            autoCommit=False)

        check_result = json.dumps(infoForm['absentList'], ensure_ascii=False)
        DBAffectedRows = database.execute(
            sql="INSERT INTO SelfstudyCheckAbsent \
                    (selfstudy_id,check_result,submit_student_id) \
                VALUES \
                    (%s,%s,%s);",
            data=(infoForm['selfstudy_id'], check_result,
                  CustomSession.getSession()["userID"]),
            autoCommit=False)
        # ========
        # 提交数据
        database.commit()
