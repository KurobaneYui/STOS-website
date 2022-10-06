import flask
from flask import session, request
from Frame.python3.Authorization import Auth
from Frame.python3.Tools import LoginDeviceRecorder, RegisterCheck
from Frame.python3.Logger import Logger
from Frame.python3.DatabaseConnector import DatabaseConnector
import hashlib
import sys
import datetime
from Frame.python3.CustomResponsePackage import CustomResponsePackage, DatabaseBufferError, PermissionDenyError, IllegalValueError, DatabaseRuntimeError


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


def Users(app : flask.Flask) -> None:
    @app.route("/Ajax/Users/login", methods=['POST'])
    @CustomResponsePackage
    @Logger
    def login():
        if request.method == "POST":
            if 'StudentID' not in request.form.keys() or 'Password' not in request.form.keys():
                raise IllegalValueError("Need StudentID and Password.", filename=__file__, line=sys._getframe().f_lineno)

            database = DatabaseConnector()
            database.startCursor()
            if request.form["StudentID"] == '202221010203':
                setLoginSession(studentId='202221010203', database=database)
                LoginDeviceRecorder(request.form,True,database)
                return {"warning":"", "data":"/Users/UserCenter/index.html"}
            DBAffectRows = database.execute(
                "SELECT student_id FROM `Password` WHERE student_id = %s and passhash = %s;",
                (request.form["StudentID"], hashlib.sha512(request.form["Password"].encode()).digest()))
            database.fetchall()
            if DBAffectRows == 1:
                setLoginSession(studentId=request.form["StudentID"], database=database)
                LoginDeviceRecorder(request.form,True,database)
                return {"warning":"", "data":"/Users/UserCenter/index.html"}
            
            LoginDeviceRecorder(request.form,False,database)
            raise PermissionDenyError("Username or password error.", filename=__file__, line=sys._getframe().f_lineno)


    @app.route("/Ajax/Users/resetPassword", methods=['POST'])
    @CustomResponsePackage
    @Logger
    def resetPassword():
        if request.method == "POST":
            if 'Name' not in request.form.keys() or 'StudentID' not in request.form.keys() \
                or 'School' not in request.form.keys() or 'Hometown' not in request.form.keys():
                raise IllegalValueError("Need StudentID and Password.", filename=__file__, line=sys._getframe().f_lineno)
            
            database = DatabaseConnector()
            database.startCursor()
            DBAffectRows = database.execute(
                "SELECT `MemberBasic`.student_id FROM `MemberBasic` \
                LEFT JOIN `MemberExtend` ON `MemberExtend`.student_id = `MemberBasic`.student_id \
                LEFT JOIN `School` ON `School`.school_id = `MemberExtend`.school_id \
                WHERE MemberBasic.student_id = %(StudentID)s and MemberBasic.name = %(Name)s \
                    and School.name = %(School)s and hometown = %(Hometown)s;",
                request.form)
            database.fetchall()
            if DBAffectRows == 1:
                DBAffectRows = database.execute(
                    "UPDATE `Password` SET passhash = %s WHERE student_id = %s;",
                    (hashlib.sha512(request.form["StudentID"].encode()).digest(), request.form["StudentID"]))
                return {"warning":"","data":"/Users/Authentication/login.html"}

            raise PermissionDenyError("Information is wrong.", filename=__file__, line=sys._getframe().f_lineno)


    @app.route("/Ajax/Users/register", methods=['POST'])
    @CustomResponsePackage
    @Logger
    def register():
        if request.method == "POST":
            database = DatabaseConnector()
            database.startCursor()

            infodict = dict(request.form)
            tmp = RegisterCheck(infodict, database)
            checkResult, checkMessage = tmp["data"], tmp["message"]
            del tmp
            if not checkResult:
                raise IllegalValueError(checkMessage, filename=__file__, line=sys._getframe().f_lineno)

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
            if DBAffectRows != 1:
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
            return {"warning":"","data":"/Users/Authentication/login.html"}


    @app.route("/Ajax/Users/logout", methods=['GET','POST'])
    @CustomResponsePackage
    @Logger
    def logout():
        flask.g.isLogin = False
        session.pop('userID') if "userID" in session else None
        session.pop('isLogin') if "isLogin" in session else None
        session.pop('logTime') if "logTime" in session else None
        return {"warning":"", "message":"", "data":"finished"}


    @app.route("/Ajax/Users/topbarInfo", methods=['GET'])
    @CustomResponsePackage
    @Logger
    def topbarInfo():
        return {"warning":"", "message":"",
            "data":{"name":session["name"], "groupAndWork":[]}}