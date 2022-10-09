import flask
from flask import session, request
from Frame.python3.Authorization import Auth
from Frame.python3.Tools import RegisterCheck
from Frame.python3.Logger import Logger
from Frame.python3.DatabaseConnector import DatabaseConnector
from Frame.python3.Tools import ChangeInfoCheck
import sys
from Frame.python3.CustomResponsePackage import CustomResponsePackage, IllegalValueError
import Ajax.function_contact as function_contact
import Ajax.function_personalInfo as function_personalInfo
import Ajax.function_loginAndRegister as function_loginAndRegister
    

def resetLogoutSession():
    flask.g.isLogin = False
    session.pop('userID') if "userID" in session else None
    session.pop('isLogin') if "isLogin" in session else None
    session.pop('logTime') if "logTime" in session else None


def Users(app : flask.Flask) -> None:
    @app.route("/Ajax/Users/login", methods=['POST'])
    @CustomResponsePackage
    @Logger
    def login():
        if 'StudentID' not in request.form.keys() or 'Password' not in request.form.keys():
            raise IllegalValueError("Need StudentID and Password.", filename=__file__, line=sys._getframe().f_lineno)
        
        returns = function_loginAndRegister.login(request.form)
        return {"warning":"", "message":returns["message"], "data":returns["data"]}


    @app.route("/Ajax/Users/resetPassword", methods=['POST'])
    @CustomResponsePackage
    @Logger
    @Auth(({'department_id':None,'actor':None},))
    def resetPassword():
        if 'Name' not in request.form.keys() or 'StudentID' not in request.form.keys() \
            or 'School' not in request.form.keys() or 'Hometown' not in request.form.keys():
            raise IllegalValueError("Need StudentID and Password.", filename=__file__, line=sys._getframe().f_lineno)
        
        returns = function_personalInfo.reset_password(request.form)
        return {"warning":"", "message":returns["message"], "data":returns["data"]}


    @app.route("/Ajax/Users/delete_personal_info", methods=['POST'])
    @CustomResponsePackage
    @Logger
    @Auth(({'department_id':None,'actor':None},))
    def delete_personal_info():
        database = DatabaseConnector()
        database.startCursor()
        if 'confirmDelete' not in request.form.keys() or request.form['confirmDelete'] != 'confirm':
            raise IllegalValueError("请填写正确确认文字，如有问题请联系管理员！", filename=__file__, line=sys._getframe().f_lineno)

        returns = function_personalInfo.delete_personal_info()
        resetLogoutSession()
        return {"warning":"", "message":returns["message"], "data":returns["data"]}


    @app.route("/Ajax/Users/register", methods=['POST'])
    @CustomResponsePackage
    @Logger
    def register():
        database = DatabaseConnector()
        database.startCursor()

        infodict = dict(request.form)
        tmp = RegisterCheck(infodict, database)
        checkResult, checkMessage = tmp["data"], tmp["message"]
        if not checkResult:
            raise IllegalValueError(checkMessage, filename=__file__, line=sys._getframe().f_lineno)

        returns = function_loginAndRegister.register(infodict, database)
        return {"warning":"", "message":returns["message"], "data":returns["data"]}


    @app.route("/Ajax/Users/logout", methods=['GET','POST'])
    @CustomResponsePackage
    @Logger
    def logout():
        resetLogoutSession()
        return {"warning":"", "message":"", "data":"finished"}


    @app.route("/Ajax/Users/topbarInfo", methods=['GET'])
    @CustomResponsePackage
    @Logger
    @Auth(({'department_id':None,'actor':None},))
    def topbarInfo():
        return {"warning":"", "message":"",
            "data":{"name":session["name"], "groupAndWork":[]}}
        
        
    @app.route("/Ajax/Users/get_contact", methods=['GET'])
    @CustomResponsePackage
    @Logger
    @Auth(({'department_id':None,'actor':'11'},))
    def get_contact():
        returns = function_contact.get_contact()['data']
        return {"warning":"", "message":"", "data":returns}
        
        
    @app.route("/Ajax/Users/get_personal_info", methods=['GET'])
    @CustomResponsePackage
    @Logger
    @Auth(({'department_id':None,'actor':None},))
    def get_personal_info():
        returns = function_personalInfo.get_personal_info()
        return {"warning":"", "message":returns['message'], "data":returns['data']}
        
        
    @app.route("/Ajax/Users/change_personal_info", methods=['POST'])
    @CustomResponsePackage
    @Logger
    @Auth(({'department_id':None,'actor':None},))
    def change_personal_info():
        database = DatabaseConnector()
        database.startCursor()
        
        data = dict(request.form)
        tmp = ChangeInfoCheck(data, database)
        checkResult, checkMessage = tmp["data"], tmp["message"]

        if not checkResult:
            raise IllegalValueError(checkMessage, filename=__file__, line=sys._getframe().f_lineno)
        
        returns = function_personalInfo.change_personal_info(data, database)
        return {"warning":"", "message":returns['message'], "data":returns['data']}