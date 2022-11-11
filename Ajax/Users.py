"""
This file is for user manager (self).
In this file, all URL apis are designed for manage user data self.
Usually, all functions need form user auth.
"""


import flask
from flask import session, request
from Frame.python3.Authorization import Auth
from Frame.python3.Tools import RegisterCheck
from Frame.python3.Logger import Logger
from Frame.python3.DatabaseConnector import DatabaseConnector
from Frame.python3.Tools import ChangeInfoCheck
from Frame.python3.CustomResponsePackage import CustomResponsePackage, IllegalValueError
import sys
import Ajax.function_contact as function_contact
import Ajax.function_personalInfo as function_personalInfo
import Ajax.function_loginAndRegister as function_loginAndRegister
import Ajax.function_emptyTimeTable as function_emptyTimeTable
import Ajax.function_workBasicInfo as function_workBasicInfo
    

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
        database = DatabaseConnector()
        database.startCursor()
        
        DBAffectRows = database.execute(
            sql="SELECT job FROM `Work` WHERE student_id=%s;",
            data=(session['userID'],)
        )
        database.fetchall()  
        results = "正式队员" if DBAffectRows > 0 else "预备队员"
        
        return {"warning":"", "message":"", "data":{"name":session["name"], "memberType":results}}


    # @app.route("/Ajax/Users/indexInfo", methods=['GET'])
    # @CustomResponsePackage
    # @Logger
    # @Auth(({'department_id':None,'actor':None},))
    # def indexInfo():
    #     database = DatabaseConnector()
    #     database.startCursor()
        
    #     DBAffectRows = database.execute(
    #         sql="SELECT Department.department_id as department_id,name, group_rank, score FROM `Work` \
    #             LEFT JOIN `Department` ON `Work`.department_id=Department.department_id \
    #             LEFT JOIN RankAndScoreInGroup ON \
    #                 `Work`.department_id=RankAndScoreInGroup.department_id AND `Work`.student_id=RankAndScoreInGroup.student_id \
    #             WHERE `Work`.student_id=%s;",
    #         data=(session['userID'],)
    #     )
    #     results = database.fetchall()  
    #     for i in results:
    #         i['score'] = float(i['score'])
        
    #     return {"warning":"", "message":"", "data":{"name":session["name"], "GroupScoreRank":results}}
        
        
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
    
    
    @app.route("/Ajax/Users/get_empty_time_info", methods=['GET'])
    @CustomResponsePackage
    @Logger
    @Auth(({'department_id':None,'actor':None},))
    def get_empty_time_info():
        results = function_emptyTimeTable.get_empty_time_info()
        results['warning'] = ""
        return results
    
    
    @app.route("/Ajax/Users/get_work_basic_info", methods=['GET'])
    @CustomResponsePackage
    @Logger
    @Auth(({'department_id':None,'actor':None},))
    def get_work_basic_info():
        results = function_workBasicInfo.get_work_basic_info()
        results['warning'] = ""
        return results
    
    
    @app.route("/Ajax/Users/get_score_details", methods=['GET'])
    @CustomResponsePackage
    @Logger
    @Auth(({'department_id':None,'actor':"11"},))
    def get_score_details():
        database = DatabaseConnector()
        database.startCursor()
        
        DBAffectedRows = database.execute(
            sql="SELECT `date`, `Score`.department_id, `name` AS department_name, variant, reason  FROM `Score` \
                LEFT JOIN Department ON `Score`.department_id=Department.department_id \
                WHERE student_id=%s \
                ORDER BY `Score`.submission_time DESC;",
            data=(session["userID"],))
        results = database.fetchall()
        
        returns = dict()
        for row in results:
            if row["department_id"] not in returns.keys():
                returns[row["department_id"]] = list()
            if len(returns[row["department_id"]]) > 20:
                continue
            row["date"] = str(row["date"])
            returns[row["department_id"]].append(row)

        return {"warning":"", "message":"", "data":returns}