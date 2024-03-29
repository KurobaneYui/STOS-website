import re
import sys
from typing import Any
from flask import Request
from Frame.python3.BaseComponents.CustomSession import CustomSession
from Frame.python3.BaseComponents.CustomError import IllegalValueError
from Frame.python3.BaseComponents.DatabaseConnector import DatabaseConnector


# TODO: Not Finished Yet !!! password not check
def RegisterCheck(formDict: dict, database: DatabaseConnector) -> dict[str, Any]:
    """Check input data for person register.

    Args:
        formDict (dict): flask.request.form
        database (DatabaseConnector): Reuse exist database connection.

    Returns:
        dict[str,Any]: 'data':True if there is no illegal values, and 'data':False if there are some.
                        When 'data':False, 'message' will save info about which value is illegal.
    """
    if "name" not in formDict.keys() and \
        "studentID" not in formDict.keys() and \
        "gender" not in formDict.keys() and \
        "ethnicity" not in formDict.keys() and \
        "hometown" not in formDict.keys() and \
        "phone" not in formDict.keys() and \
        "qq" not in formDict.keys() and \
        "campus" not in formDict.keys() and \
        "school" not in formDict.keys() and \
        "dormitory_yuan" not in formDict.keys() and \
        "dormitory_dong" not in formDict.keys() and \
        "dormitory_hao" not in formDict.keys() and \
        "bank" not in formDict.keys() and \
        "subsidyDossier" not in formDict.keys() and \
            "password" not in formDict.keys():
        return {"warning": "", "message": "所有信息均为必填项，请确认", "data": False}

    returnMessage = ""

    if len(formDict["name"]) == 0:
        returnMessage += "姓名未填写。"
    elif len(formDict["name"]) > 20:
        formDict["name"] = formDict["name"][:20]

    pattern = re.compile(r"^[A-Za-z0-9]{10,15}$")
    if pattern.match(formDict["studentID"]) is None:
        returnMessage += "学号应为10~15长度的数字字母组合，请检查。"

    if formDict["gender"] not in ['男', '女']:
        returnMessage += "信息提交出错，请联系管理员修改网站。error: gender。"

    pattern = re.compile(r"^[\u4E00-\u9FA5]{2,8}$")
    if pattern.match(formDict["ethnicity"]) is None:
        returnMessage += "民族信息有误。"

    if not 0 < len(formDict["hometown"]) <= 20:
        returnMessage += "籍贯信息过长或未填写，仅需省市县，请限制在20字符以内。"

    pattern = re.compile(
        r"^1((34[0-8])|(8\d{2})|(([35][0-35-9]|4[579]|66|7[35678]|9[1389])\d{1}))\d{7}$")
    if pattern.match(formDict["phone"]) is None:
        returnMessage += "手机号码有误。"

    pattern = re.compile(r"^[1-9][0-9]{4,}$")
    if pattern.match(formDict["qq"]) is None:
        returnMessage += "QQ号有误。"

    DBAffectRows = database.execute(
        "SELECT school_id from School WHERE name=%(school)s and campus=%(campus)s;",
        formDict)
    if DBAffectRows != 1:
        returnMessage += "学院、校区匹配错误，请联系管理员处理。"
    else:
        formDict["schoolID"] = database.fetchall()[0]["school_id"]

    if formDict["dormitory_yuan"] not in ["学知苑", "硕丰苑", "校内", "校外"]:
        returnMessage += "信息提交出错，请联系管理员修改网站。error: dormitory_yuan。"

    if formDict["dormitory_yuan"] != "校外":
        pattern = re.compile(r"^[0-9]+$")
        if pattern.match(formDict["dormitory_dong"]) is None:
            returnMessage += "宿舍楼栋输入有误。"
        elif not 0 < int(formDict["dormitory_dong"]) < 100:
            returnMessage += "宿舍楼栋输入有误。"

        pattern = re.compile(r"^[0-9]+$")
        if pattern.match(formDict["dormitory_hao"]) is None:
            returnMessage += "宿舍楼栋输入有误。"
        elif not 100 < int(formDict["dormitory_hao"]) < 1000:
            returnMessage += "宿舍楼栋输入有误。"

    else:
        formDict["dormitory_dong"], formDict["dormitory_hao"] = "0", "0"

    pattern = re.compile(r"^([1-9]{1})(\d{15,18})$")
    if pattern.match(formDict["bank"]) is None:
        returnMessage += "银行卡号有误。"

    formDict["subsidyDossier"] = True if formDict["subsidyDossier"] == 'true' else False

    returnBool = False if len(returnMessage) > 0 else True
    return {"result": returnBool, "message": returnMessage}


# TODO: Not Finished Yet !!! password not check and others
def ChangeInfoCheck(formDict: dict, database: DatabaseConnector) -> dict[str, Any]:
    """Check input data for personal info change.

    Args:
        formDict (dict): flask.request.form
        database (DatabaseConnector): Reuse exist database connection.

    Returns:
        dict[str,Any]: 'data':True if there is no illegal values, and 'data':False if there are some.
                        When 'data':False, 'message' will save info about which value is illegal.
    """
    if "name" not in formDict.keys() and \
        "gender" not in formDict.keys() and \
        "ethnicity" not in formDict.keys() and \
        "hometown" not in formDict.keys() and \
        "infoRemark" not in formDict.keys() and \
        "phone" not in formDict.keys() and \
        "qq" not in formDict.keys() and \
        "campus" not in formDict.keys() and \
        "school" not in formDict.keys() and \
        "dormitory_yuan" not in formDict.keys() and \
        "dormitory_dong" not in formDict.keys() and \
        "dormitory_hao" not in formDict.keys() and \
        "application_bankcard" not in formDict.keys() and \
        "application_name" not in formDict.keys() and \
        "application_student_id" not in formDict.keys() and \
        "subsidyDossier" not in formDict.keys() and \
            "wageRemark" not in formDict.keys():
        return {"warning": "", "message": "除备注和密码外，其余信息均为必填项，请确认", "data": False}

    returnMessage = ""
    formDict["studentID"] = CustomSession.getSession()["userID"]

    if len(formDict["name"]) == 0:
        returnMessage += "姓名未填写。"
    elif len(formDict["name"]) > 20:
        formDict["name"] = formDict["name"][:20]

    if len(formDict["application_name"]) == 0:
        returnMessage += "工资领取人姓名未填写。"
    elif len(formDict["application_name"]) > 20:
        formDict["application_name"] = formDict["application_name"][:20]

    pattern = re.compile(r"^[A-Za-z0-9]{10,15}$")
    if pattern.match(formDict["studentID"]) is None:
        returnMessage += "学号应为10~15长度的数字字母组合，请检查。"
    if pattern.match(formDict["application_student_id"]) is None:
        returnMessage += "工资领取人学号应为10~15长度的数字字母组合，请检查。"

    if formDict["gender"] not in ['男', '女']:
        returnMessage += "信息提交出错，请联系管理员修改网站。error: gender。"

    pattern = re.compile(r"^[\u4E00-\u9FA5]{2,8}$")
    if pattern.match(formDict["ethnicity"]) is None:
        returnMessage += "民族信息有误。"

    if not 0 < len(formDict["hometown"]) <= 20:
        returnMessage += "籍贯信息过长或未填写，仅需省市县，请限制在20字符以内。"

    pattern = re.compile(
        r"^1((34[0-8])|(8\d{2})|(([35][0-35-9]|4[579]|66|7[35678]|9[1389])\d{1}))\d{7}$")
    if pattern.match(formDict["phone"]) is None:
        returnMessage += "手机号码有误。"

    pattern = re.compile(r"^[1-9][0-9]{4,}$")
    if pattern.match(formDict["qq"]) is None:
        returnMessage += "QQ号有误。"

    DBAffectRows = database.execute(
        "SELECT school_id from School WHERE name=%(school)s and campus=%(campus)s;",
        formDict)
    if DBAffectRows != 1:
        returnMessage += "学院、校区匹配错误，请联系管理员处理。"
    else:
        formDict["schoolID"] = database.fetchall()[0]["school_id"]

    if formDict["dormitory_yuan"] not in ["学知苑", "硕丰苑", "校内", "校外"]:
        returnMessage += "信息提交出错，请联系管理员修改网站。error: dormitory_yuan。"

    if formDict["dormitory_yuan"] != "校外":
        pattern = re.compile(r"^[0-9]+$")
        if pattern.match(formDict["dormitory_dong"]) is None:
            returnMessage += "宿舍楼栋输入有误。"
        elif not 0 < int(formDict["dormitory_dong"]) < 100:
            returnMessage += "宿舍楼栋输入有误。"

        pattern = re.compile(r"^[0-9]+$")
        if pattern.match(formDict["dormitory_hao"]) is None:
            returnMessage += "宿舍楼栋输入有误。"
        elif not 100 < int(formDict["dormitory_hao"]) < 1000:
            returnMessage += "宿舍楼栋输入有误。"

    else:
        formDict["dormitory_dong"], formDict["dormitory_hao"] = "0", "0"

    pattern = re.compile(r"^([1-9]{1})(\d{15,18})$")
    if pattern.match(formDict["application_bankcard"]) is None:
        returnMessage += "银行卡号有误。"

    formDict["subsidyDossier"] = True if formDict["subsidyDossier"] == 'true' else False

    returnBool = False if len(returnMessage) > 0 else True
    return {"result": returnBool, "message": returnMessage}


class Ajax_Users:
    @staticmethod
    def loginParamsCheck(flaskRequest: Request) -> None:
        if 'StudentID' not in flaskRequest.form.keys() or 'Password' not in flaskRequest.form.keys():
            raise IllegalValueError(
                "Need StudentID and Password.", filename=__file__, line=sys._getframe().f_lineno)

    @staticmethod
    def loginAsSpecifiedWorkParamsCheck(flaskRequest: Request) -> None:
        if 'department_id' not in flaskRequest.form.keys() or 'job' not in flaskRequest.form.keys():
            raise IllegalValueError(
                "Need department_id and job.", filename=__file__, line=sys._getframe().f_lineno)

    @staticmethod
    def resetPasswordParamsCheck(flaskRequest: Request) -> None:
        if 'Name' not in flaskRequest.form.keys() or 'StudentID' not in flaskRequest.form.keys() \
                or 'School' not in flaskRequest.form.keys() or 'Hometown' not in flaskRequest.form.keys():
            raise IllegalValueError(
                "Need full information in form.", filename=__file__, line=sys._getframe().f_lineno)

    @staticmethod
    def deletePersonalInfoParamsCheck(flaskRequest: Request) -> None:
        if 'confirmDelete' not in flaskRequest.form.keys() or flaskRequest.form['confirmDelete'] != 'confirm':
            raise IllegalValueError(
                "请填写正确确认文字，如有问题请联系管理员！", filename=__file__, line=sys._getframe().f_lineno)

    @staticmethod
    def registerParamsCheck(infoDict: dict, databaseConnector: DatabaseConnector | None = None) -> None:
        # =====================================
        # 如果提供已经建立的数据库连接，则直接使用
        if databaseConnector is None:
            database = DatabaseConnector()
            database.startCursor()
        else:
            database = databaseConnector
        # ========
        # 检查参数
        tmp = RegisterCheck(infoDict, database)
        checkResult, checkMessage = tmp["result"], tmp["message"]
        if not checkResult:
            raise IllegalValueError(
                checkMessage, filename=__file__, line=sys._getframe().f_lineno)

    @staticmethod
    def changePersonalInfoParamsCheck(infoDict: dict, databaseConnector: DatabaseConnector | None = None) -> None:
        # =====================================
        # 如果提供已经建立的数据库连接，则直接使用
        if databaseConnector is None:
            database = DatabaseConnector()
            database.startCursor()
        else:
            database = databaseConnector
        # ====================
        tmp = ChangeInfoCheck(infoDict, database)
        checkResult, checkMessage = tmp["result"], tmp["message"]

        if not checkResult:
            raise IllegalValueError(
                checkMessage, filename=__file__, line=sys._getframe().f_lineno)

    @staticmethod
    def submitSelfstudyRecordParamsCheck(infoForm: dict) -> None:
        if "absentList" not in infoForm.keys() or "askForLeaveList" not in infoForm.keys() \
                or "record" not in infoForm.keys() or "selfstudy_id" not in infoForm.keys():
            raise IllegalValueError(
                "Not all data are provided.", filename=__file__, line=sys._getframe().f_lineno)

        for one_student in infoForm["absentList"]:
            if one_student["student_name"] == "" or one_student["student_id"] == "":
                raise IllegalValueError(
                    "Student name or ID in absent list is empty.", filename=__file__, line=sys._getframe().f_lineno)
            if any(char.isdigit() for char in one_student["student_name"]) or not any(char.isalpha() and char.isalnum() for char in one_student["student_name"]):
                raise IllegalValueError(
                    "Student name in absent list is wrong.", filename=__file__, line=sys._getframe().f_lineno)
            if not (one_student["student_id"].isalnum() and one_student["student_id"].isascii()):
                raise IllegalValueError(
                    "Student ID in absent list is wrong.", filename=__file__, line=sys._getframe().f_lineno)
        
        for one_student in infoForm["askForLeaveList"]:
            if one_student["student_name"] == "" or one_student["student_id"] == "":
                raise IllegalValueError(
                    "Student name or ID in ask for leave list is empty.", filename=__file__, line=sys._getframe().f_lineno)
            if any(char.isdigit() for char in one_student["student_name"]) or not any(char.isalpha() and char.isalnum() for char in one_student["student_name"]):
                raise IllegalValueError(
                    "Student name in ask for leave list is wrong.", filename=__file__, line=sys._getframe().f_lineno)
            if not (one_student["student_id"].isalnum() and one_student["student_id"].isascii()):
                raise IllegalValueError(
                    "Student ID in ask for leave list is wrong.", filename=__file__, line=sys._getframe().f_lineno)

        if "absent" not in infoForm["record"].keys() \
                or "firstPresent" not in infoForm["record"].keys() \
                or "leaveEarly" not in infoForm["record"].keys() \
                or "secondPresent" not in infoForm["record"].keys() \
                or "askForLeave" not in infoForm["record"].keys() \
                or "remark" not in infoForm["record"].keys():
            raise IllegalValueError(
                "Not all data are provided.", filename=__file__, line=sys._getframe().f_lineno)

        try:
            infoForm["selfstudy_id"] = int(infoForm["selfstudy_id"])
            assert infoForm["selfstudy_id"] > 0
        except:
            raise IllegalValueError(
                "selfstudy_id is wrong or not positive integer.", filename=__file__, line=sys._getframe().f_lineno)

        try:
            infoForm["record"]["firstPresent"] = int(
                infoForm["record"]["firstPresent"])
            infoForm["record"]["absent"] = int(infoForm["record"]["absent"])
            infoForm["record"]["secondPresent"] = int(
                infoForm["record"]["secondPresent"])
            infoForm["record"]["leaveEarly"] = int(
                infoForm["record"]["leaveEarly"])
            infoForm["record"]["askForLeave"] = int(
                infoForm["record"]["askForLeave"])
            assert infoForm["record"]["firstPresent"] >= 0
            assert infoForm["record"]["absent"] >= 0
            assert infoForm["record"]["secondPresent"] >= 0
            assert infoForm["record"]["leaveEarly"] >= 0
            assert infoForm["record"]["askForLeave"] >= 0
        except:
            raise IllegalValueError(
                "Record is wrong or not nonnegative integer.", filename=__file__, line=sys._getframe().f_lineno)

    @staticmethod
    def submitCoursesRecordParamsCheck(infoForm: dict) -> None:
        if "record" not in infoForm.keys() or "course_id" not in infoForm.keys():
            raise IllegalValueError(
                "Not all data are provided.", filename=__file__, line=sys._getframe().f_lineno)

        if "firstPresent" not in infoForm["record"].keys() \
                or "firstDisciplinary" not in infoForm["record"].keys() \
                or "secondPresent" not in infoForm["record"].keys() \
                or "secondDisciplinary" not in infoForm["record"].keys() \
                or "remark" not in infoForm["record"].keys():
            raise IllegalValueError(
                "Not all data are provided.", filename=__file__, line=sys._getframe().f_lineno)

        try:
            infoForm["course_id"] = int(infoForm["course_id"])
            assert infoForm["course_id"] > 0
        except:
            raise IllegalValueError(
                "course_id is wrong or not positive integer.", filename=__file__, line=sys._getframe().f_lineno)

        try:
            infoForm["record"]["firstPresent"] = int(
                infoForm["record"]["firstPresent"])
            infoForm["record"]["firstDisciplinary"] = int(
                infoForm["record"]["firstDisciplinary"])
            infoForm["record"]["secondPresent"] = int(
                infoForm["record"]["secondPresent"])
            infoForm["record"]["secondDisciplinary"] = int(
                infoForm["record"]["secondDisciplinary"])
            assert infoForm["record"]["firstPresent"] >= 0
            assert infoForm["record"]["firstDisciplinary"] >= 0
            assert infoForm["record"]["secondPresent"] >= 0
            assert infoForm["record"]["secondDisciplinary"] >= 0
        except:
            raise IllegalValueError(
                "Record is wrong or not nonnegative integer.", filename=__file__, line=sys._getframe().f_lineno)
