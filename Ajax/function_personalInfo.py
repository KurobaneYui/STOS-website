from Frame.python3.DatabaseConnector import DatabaseConnector
from Frame.python3.Authorization import Auth
from Frame.python3.Logger import Logger
from flask import session
from Frame.python3.CustomResponsePackage import PermissionDenyError, IllegalValueError, DatabaseRuntimeError
import sys
import hashlib


@Logger
@Auth(({'department_id':None,'actor':None},))
def get_personal_info():
    connection = DatabaseConnector()
    connection.startCursor()
    connection.execute(
        sql="SELECT `MemberExtend`.student_id as student_id, `MemberBasic`.name as name, \
                gender, ethnicity, hometown, phone, qq, `MemberExtend`.remark as infoRemark, \
                campus, `School`.`name` as school, dormitory_yuan, dormitory_dong, dormitory_hao, \
                application_student_id, application_name, application_bankcard, subsidy_dossier, \
                `WageInfo`.remark as wageRemark \
            FROM `MemberExtend` \
            LEFT JOIN `MemberBasic` ON `MemberExtend`.`student_id` = `MemberBasic`.`student_id` \
            LEFT JOIN `School` ON `MemberExtend`.`school_id` = `School`.`school_id` \
            LEFT JOIN `WageInfo` ON `MemberExtend`.`student_id` = `WageInfo`.`student_id` \
            WHERE `MemberExtend`.`student_id` = %s;",
        data=(session["userID"],)
        )
    results = connection.fetchall()
    
    return {'warning':'', 'message':'', 'data':results}


@Logger
@Auth(({'department_id':None,'actor':None},))
def change_personal_info(data : dict, database : DatabaseConnector): # TODO: Not Finished Yet !!!
    DBAffectRows = database.execute(
        "SELECT student_id FROM `MemberExtend` WHERE student_id = %(studentID)s;",
        data)
    database.fetchall()
    if DBAffectRows != 1:
        raise IllegalValueError("Student ID not exists.", filename=__file__, line=sys._getframe().f_lineno)

    DBAffectRows = database.execute(
        "UPDATE `MemberBasic` SET name=%(name)s, gender=%(gender)s WHERE student_id=%(studentID)s;",
        data, autoCommit=False)
    if DBAffectRows not in [0,1]:
        database.rollback()
        raise DatabaseRuntimeError("Update member basic info error.", filename=__file__, line=sys._getframe().f_lineno)
    DBAffectRows = database.execute(
        "UPDATE `MemberExtend` SET \
            ethnicity=%(ethnicity)s, hometown=%(hometown)s, phone=%(phone)s, \
            qq=%(qq)s, school_id=%(schoolID)s, dormitory_yuan=%(dormitory_yuan)s, \
            dormitory_dong=%(dormitory_dong)s, dormitory_hao=%(dormitory_hao)s, \
            remark=%(infoRemark)s \
        WHERE student_id=%(studentID)s;",
        data, autoCommit=False)
    if DBAffectRows not in [0,1]:
        database.rollback()
        raise DatabaseRuntimeError("Update member extend info error.", filename=__file__, line=sys._getframe().f_lineno)
    DBAffectRows = database.execute(
        "UPDATE `WageInfo` SET \
            application_student_id=%(application_student_id)s, application_name=%(application_name)s, \
            application_bankcard=%(application_bankcard)s,subsidy_dossier=%(subsidyDossier)s, \
            remark=%(wageRemark)s \
        WHERE student_id=%(studentID)s;",
        data, autoCommit=False)
    if DBAffectRows not in [0,1]:
        database.rollback()
        raise DatabaseRuntimeError("Update wage info error.", filename=__file__, line=sys._getframe().f_lineno)
    if "password" in data.keys():
        DBAffectRows = database.execute(
            "UPDATE `Password` SET passhash=%s \
            WHERE student_id=%s",
            (hashlib.sha512(data["password"].encode()).digest(), data["studentID"]),
            autoCommit=False)
        if DBAffectRows not in [0,1]:
            database.rollback()
            raise DatabaseRuntimeError("Update password info error.", filename=__file__, line=sys._getframe().f_lineno)
    database.commit()
    return {"warning":"", "data":"", "message":"刷新页面以更新数据，如仍有数据未更新，请退出重新登录。如有问题请联系管理员。"}


@Logger
@Auth(({'department_id':None,'actor':None},))
def delete_personal_info():
    database = DatabaseConnector()
    database.startCursor()

    DBAffectRows = database.execute(
        "DELETE FROM `MemberExtend` WHERE student_id=%s;",
        (session["userID"],))

    if DBAffectRows != 1:
        raise PermissionDenyError("Student ID not exists.", filename=__file__, line=sys._getframe().f_lineno)
    
    return {"warning":"", "message":"", "data":""}


@Logger
@Auth(({'department_id':None,'actor':None},))
def reset_password(data : dict):
    database = DatabaseConnector()
    database.startCursor()
    DBAffectRows = database.execute(
        "SELECT `MemberBasic`.student_id FROM `MemberBasic` \
        LEFT JOIN `MemberExtend` ON `MemberExtend`.student_id = `MemberBasic`.student_id \
        LEFT JOIN `School` ON `School`.school_id = `MemberExtend`.school_id \
        WHERE MemberBasic.student_id = %(StudentID)s and MemberBasic.name = %(Name)s \
            and School.name = %(School)s and hometown = %(Hometown)s;",
        data)
    database.fetchall()
    if DBAffectRows == 1:
        DBAffectRows = database.execute(
            "UPDATE `Password` SET passhash = %s WHERE student_id = %s;",
            (hashlib.sha512(data["StudentID"].encode()).digest(), data["StudentID"]))
        return {"warning":"", "message":"", "data":"/Users/Authentication/login.html"}

    raise PermissionDenyError("Information is wrong.", filename=__file__, line=sys._getframe().f_lineno)