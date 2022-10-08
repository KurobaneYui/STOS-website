from typing import Any
from Frame.python3.DatabaseConnector import DatabaseConnector
from Frame.python3.CustomResponsePackage import IllegalValueError
from Frame.python3.ClientInfo import ClientInfo
from Frame.python3.Logger import Logger
import sys
import re
import datetime
import json
from flask import session


@Logger
def LoginDeviceRecorder(formDict : dict, loginResult : bool, database : DatabaseConnector) -> dict[str,str]:
    clientInfo = ClientInfo()
    clientInfo["studentID"] = formDict["StudentID"]
    clientInfo["time"] = datetime.datetime.now().isoformat()
    clientInfo["login_result"] = loginResult
    clientInfo["address"] = str(clientInfo["address"])
    DBAffectRows = database.execute(
        "INSERT IGNORE INTO LogInfo (student_id,agent,ip,address,language,time,login_result) \
        VALUES (%(studentID)s,%(agent)s,%(IP)s,%(address)s,%(language)s,%(time)s,%(login_result)s);",
        clientInfo)
    return {"warning":"", "message":"", "data":""}


@Logger # TODO: Not Finished Yet !!! password not check and others
def ChangeInfoCheck(formDict : dict, database : DatabaseConnector) -> dict[str,Any]:
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
        return {"warning":"", "message": "除备注和密码外，其余信息均为必填项，请确认", "data": False}

    returnMessage = ""
    formDict["studentID"] = session["userID"]

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

    if formDict["gender"] not in ['男','女']:
        returnMessage += "信息提交出错，请联系管理员修改网站。error: gender。"

    pattern = re.compile(r"^[\u4E00-\u9FA5]{2,8}$")
    if pattern.match(formDict["ethnicity"]) is None:
        returnMessage += "民族信息有误。"

    if not 0 < len(formDict["hometown"]) <= 20:
        returnMessage += "籍贯信息过长或未填写，仅需省市县，请限制在20字符以内。"

    pattern = re.compile(r"^1((34[0-8])|(8\d{2})|(([35][0-35-9]|4[579]|66|7[35678]|9[1389])\d{1}))\d{7}$")
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

    if formDict["dormitory_yuan"] not in ["学知苑","硕丰苑","校内","校外"]:
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

    formDict["subsidyDossier"] = True if formDict["subsidyDossier"]=='true' else False

    returnBool = False if len(returnMessage) > 0 else True
    return {"warning":"", "message": returnMessage, "data": returnBool}


@Logger
def RegisterCheck(formDict : dict, database : DatabaseConnector) -> dict[str,Any]: # TODO: Not Finished Yet !!! password not check
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
        return {"warning":"", "message": "所有信息均为必填项，请确认", "data": False}

    returnMessage = ""

    if len(formDict["name"]) == 0:
        returnMessage += "姓名未填写。"
    elif len(formDict["name"]) > 20:
        formDict["name"] = formDict["name"][:20]

    pattern = re.compile(r"^[A-Za-z0-9]{10,15}$")
    if pattern.match(formDict["studentID"]) is None:
        returnMessage += "学号应为10~15长度的数字字母组合，请检查。"

    if formDict["gender"] not in ['男','女']:
        returnMessage += "信息提交出错，请联系管理员修改网站。error: gender。"

    pattern = re.compile(r"^[\u4E00-\u9FA5]{2,8}$")
    if pattern.match(formDict["ethnicity"]) is None:
        returnMessage += "民族信息有误。"

    if not 0 < len(formDict["hometown"]) <= 20:
        returnMessage += "籍贯信息过长或未填写，仅需省市县，请限制在20字符以内。"

    pattern = re.compile(r"^1((34[0-8])|(8\d{2})|(([35][0-35-9]|4[579]|66|7[35678]|9[1389])\d{1}))\d{7}$")
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

    if formDict["dormitory_yuan"] not in ["学知苑","硕丰苑","校内","校外"]:
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

    formDict["subsidyDossier"] = True if formDict["subsidyDossier"]=='true' else False

    returnBool = False if len(returnMessage) > 0 else True
    return {"warning":"", "message": returnMessage, "data": returnBool}
