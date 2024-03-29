"""
This file is for user manager (self).
In this file, all URL apis are designed for manage user data self.
Usually, all functions need form user auth.
"""


import datetime
import flask
from flask import request
from Frame.python3.AjaxParamsCheck.Ajax_Users import Ajax_Users
from Frame.python3.BaseComponents.Logger import Logger
from Frame.python3.BaseComponents.CustomSession import CustomSession
from Frame.python3.BaseComponents.Authorization import Authorization
from Frame.python3.BaseComponents.CustomResponse import CustomResponse
from Frame.python3.BaseComponents.DatabaseConnector import DatabaseConnector
from Frame.python3.DatabaseBasicOperations.DatabaseBasicOperations_Users import DatabaseBasicOperations_Users


def Users(app: flask.Flask) -> None:
    @app.route("/Ajax/Users/login", methods=['POST'])
    def login():
        with CustomResponse() as customResponse:
            with Logger(funcName="Users.login()") as logger:
                # ===============
                # 检查接口调用权限
                Authorization.check(rightsNeeded=tuple(), needLogin=False)
                # ========================
                # 检查接口输入参数并记录日志
                Ajax_Users.loginParamsCheck(request)
                logger.funcArgs = request.form
                # =========================================
                # 执行接口流程，并获取用户名信息以完成会话建立
                database = DatabaseConnector()
                database.startCursor()
                DatabaseBasicOperations_Users.login(request, database)
                results = DatabaseBasicOperations_Users.getLoginWorks(database)
                # ========================
                # 准备函数返回值和响应与日志
                returns = {"message": "",
                           "data": results}
                customResponse.setMessageAndData(**returns)
                logger.funcReturns = returns
        return customResponse.getResponse()

    @app.route("/Ajax/Users/get_login_works", methods=['GET'])
    def getLoginWorks():
        with CustomResponse() as customResponse:
            with Logger(funcName="Users.getLoginWorks()") as logger:
                # ===============
                # 检查接口调用权限
                Authorization.check(rightsNeeded=tuple(), needLogin=True)
                # =========================================
                # 执行接口流程，并获取用户名信息以完成会话建立
                results = DatabaseBasicOperations_Users.getLoginWorks()
                # ========================
                # 准备函数返回值和响应与日志
                returns = {"message": "",
                           "data": results}
                customResponse.setMessageAndData(**returns)
                logger.funcReturns = returns
        return customResponse.getResponse()

    @app.route("/Ajax/Users/login_as_specified_work", methods=['POST'])
    def loginAsSpecifiedWork():
        with CustomResponse() as customResponse:
            with Logger(funcName="Users.loginAsSpecifiedWork()") as logger:
                # ===============
                # 检查接口调用权限
                Authorization.check(rightsNeeded=tuple(), needLogin=True)
                # ========================
                # 检查接口输入参数并记录日志
                Ajax_Users.loginAsSpecifiedWorkParamsCheck(request)
                logger.funcArgs = request.form
                # =========================================
                # 执行接口流程，并获取用户名信息以完成会话建立
                database = DatabaseConnector()
                database.startCursor()
                results = DatabaseBasicOperations_Users.loginAsSpecifiedWork(
                    request, database)
                # ========================
                # 准备函数返回值和响应与日志
                returns = {"message": "",
                           "data": results}
                customResponse.setMessageAndData(**returns)
                logger.funcReturns = returns
        return customResponse.getResponse()

    @app.route("/Ajax/Users/resetPassword", methods=['POST'])
    def resetPassword():
        with CustomResponse() as customResponse:
            with Logger(funcName="Users.resetPassword()") as logger:
                # ===============
                # 检查接口调用权限
                Authorization.check(rightsNeeded=tuple(), needLogin=False)
                # ========================
                # 检查接口输入参数并记录日志
                Ajax_Users.resetPasswordParamsCheck(request)
                logger.funcArgs = request.form
                # ===========
                # 执行接口流程
                DatabaseBasicOperations_Users.resetPassword(request)
                # ========================
                # 准备函数返回值和响应与日志
                returns = {"message": "",
                           "data": "/Users/Authentication/login.html"}
                customResponse.setMessageAndData(**returns)
                logger.funcReturns = returns
        return customResponse.getResponse()

    @app.route("/Ajax/Users/delete_personal_info", methods=['POST'])
    def deletePersonalInfo():
        with CustomResponse() as customResponse:
            with Logger(funcName="Users.deletePersonalInfo()") as logger:
                # ===============
                # 检查接口调用权限
                Authorization.check(rightsNeeded=tuple(), needLogin=False)
                # ========================
                # 检查接口输入参数并记录日志
                Ajax_Users.deletePersonalInfoParamsCheck(request)
                logger.funcArgs = request.form
                # ===========
                # 执行接口流程
                DatabaseBasicOperations_Users.deletePersonalInfo()
                flask.g.isLogin = False
                CustomSession.clearSession()
                # ========================
                # 准备函数返回值和响应与日志
                returns = {"message": "", "data": ""}
                customResponse.setMessageAndData(**returns)
                logger.funcReturns = returns
        return customResponse.getResponse()

    @app.route("/Ajax/Users/register", methods=['POST'])
    def register():
        with CustomResponse() as customResponse:
            with Logger(funcName="Users.register()") as logger:
                # ===============
                # 检查接口调用权限
                Authorization.check(rightsNeeded=tuple(), needLogin=False)
                # ========================
                # 检查接口输入参数并记录日志
                database = DatabaseConnector()
                database.startCursor()
                infoDict = dict(request.form)
                Ajax_Users.registerParamsCheck(infoDict, database)
                logger.funcArgs = request.form
                # ===========
                # 执行接口流程
                DatabaseBasicOperations_Users.register(infoDict, database)
                flask.g.isLogin = False
                CustomSession.clearSession()
                # ========================
                # 准备函数返回值和响应与日志
                returns = {"message": "",
                           "data": "/Users/Authentication/login.html"}
                customResponse.setMessageAndData(**returns)
                logger.funcReturns = returns
        return customResponse.getResponse()

    @app.route("/Ajax/Users/logout", methods=['GET', 'POST'])
    def logout():
        with CustomResponse() as customResponse:
            with Logger(funcName="Users.logout()") as logger:
                # ===============
                # 检查接口调用权限
                Authorization.check(rightsNeeded=tuple(), needLogin=False)
                # ===========
                # 执行接口流程
                flask.g.isLogin = False
                CustomSession.clearSession()
                # ========================
                # 准备函数返回值和响应与日志
                returns = {"message": "", "data": "finished"}
                customResponse.setMessageAndData(**returns)
                logger.funcReturns = returns
        return customResponse.getResponse()

    @app.route("/Ajax/Users/topbarInfo", methods=['GET'])
    def topbarInfo():
        with CustomResponse() as customResponse:
            with Logger(funcName="Users.topbarInfo()") as logger:
                # ===============
                # 检查接口调用权限
                Authorization.check(rightsNeeded=tuple(), needLogin=True)
                # ===========
                # 执行接口流程
                results = DatabaseBasicOperations_Users.topbarInfo()
                # ========================
                # 准备函数返回值和响应与日志
                returns = {"message": "", "data": results}
                customResponse.setMessageAndData(**returns)
                logger.funcReturns = returns
        return customResponse.getResponse()

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
    def getContact():
        with CustomResponse() as customResponse:
            with Logger(funcName="Users.getContact()") as logger:
                # ===============
                # 检查接口调用权限
                Authorization.check(rightsNeeded=({"department_id": 0, "actor": 0}, {
                                    "department_id": 0, "actor": 1}), needLogin=True)
                # ===========
                # 执行接口流程
                results = DatabaseBasicOperations_Users.getContact()
                # ========================
                # 准备函数返回值和响应与日志
                returns = {"message": "", "data": results}
                customResponse.setMessageAndData(**returns)
                logger.funcReturns = returns
        return customResponse.getResponse()

    @app.route("/Ajax/Users/get_personal_info", methods=['GET'])
    def getPersonalInfo():
        with CustomResponse() as customResponse:
            with Logger(funcName="Users.getPersonalInfo()") as logger:
                # ===============
                # 检查接口调用权限
                Authorization.check(rightsNeeded=tuple(), needLogin=True)
                # ===========
                # 执行接口流程
                results = DatabaseBasicOperations_Users.getPersonalInfo()
                # ========================
                # 准备函数返回值和响应与日志
                returns = {"message": "", "data": results}
                customResponse.setMessageAndData(**returns)
                logger.funcReturns = returns
        return customResponse.getResponse()

    @app.route("/Ajax/Users/change_personal_info", methods=['POST'])
    def changePersonalInfo():
        with CustomResponse() as customResponse:
            with Logger(funcName="Users.changePersonalInfo()") as logger:
                # ===============
                # 检查接口调用权限
                Authorization.check(rightsNeeded=tuple(), needLogin=True)
                # ========================
                # 检查接口输入参数并记录日志
                database = DatabaseConnector()
                database.startCursor()
                infoDict = dict(request.form)
                Ajax_Users.changePersonalInfoParamsCheck(infoDict, database)
                logger.funcArgs = request.form
                # ===========
                # 执行接口流程
                results = DatabaseBasicOperations_Users.changePersonalInfo(
                    infoDict)
                # ========================
                # 准备函数返回值和响应与日志
                returns = {"message": results, "data": ""}
                customResponse.setMessageAndData(**returns)
                logger.funcReturns = returns
        return customResponse.getResponse()

    @app.route("/Ajax/Users/get_empty_time_info", methods=['GET'])
    def getEmptyTimeInfo():
        with CustomResponse() as customResponse:
            with Logger(funcName="Users.getEmptyTimeInfo()") as logger:
                # ===============
                # 检查接口调用权限
                Authorization.check(rightsNeeded=tuple(), needLogin=True)
                # ===========
                # 执行接口流程
                results = DatabaseBasicOperations_Users.getEmptyTimeInfo()
                # ========================
                # 准备函数返回值和响应与日志
                returns = {"message": "", "data": results}
                customResponse.setMessageAndData(**returns)
                logger.funcReturns = returns
        return customResponse.getResponse()

    @app.route("/Ajax/Users/get_work_basic_info", methods=['GET'])
    def getWorkBasicInfo():
        with CustomResponse() as customResponse:
            with Logger(funcName="Users.getWorkBasicInfo()") as logger:
                # ===============
                # 检查接口调用权限
                Authorization.check(rightsNeeded=({"department_id": 0, "actor": 0}, {
                                    "department_id": 0, "actor": 1}), needLogin=True)
                # ===========
                # 执行接口流程
                results = DatabaseBasicOperations_Users.getWorkBasicInfo()
                # ========================
                # 准备函数返回值和响应与日志
                returns = {"message": "", "data": results}
                customResponse.setMessageAndData(**returns)
                logger.funcReturns = returns
        return customResponse.getResponse()

    @app.route("/Ajax/Users/get_score_details", methods=['GET'])
    def getScoreDetails():
        with CustomResponse() as customResponse:
            with Logger(funcName="Users.getScoreDetails()") as logger:
                # ===============
                # 检查接口调用权限
                Authorization.check(rightsNeeded=({"department_id": 0, "actor": 0}, {
                                    "department_id": 0, "actor": 1}), needLogin=True)
                # ===========
                # 执行接口流程
                results = DatabaseBasicOperations_Users.getScoreDetails()
                # ========================
                # 准备函数返回值和响应与日志
                returns = {"message": "", "data": results}
                customResponse.setMessageAndData(**returns)
                logger.funcReturns = returns
        return customResponse.getResponse()

    @app.route("/Ajax/Users/get_schedule_recent", methods=['GET'])
    def getScheduleRecent():
        with CustomResponse() as customResponse:
            with Logger(funcName="Users.getScheduleRecent()") as logger:
                # ===============
                # 检查接口调用权限
                Authorization.check(rightsNeeded=({"department_id": 5, "actor": 0},
                                                  {"department_id": 6, "actor": 0},
                                                  {"department_id": 7, "actor": 0},
                                                  {"department_id": 8, "actor": 0},
                                                  {"department_id": 9, "actor": 0},
                                                  {"department_id": 10, "actor": 0},
                                                  {"department_id": 11, "actor": 0}), needLogin=True)
                # ===========
                # 执行接口流程
                results = DatabaseBasicOperations_Users.getScheduleRecent()
                # ========================
                # 准备函数返回值和响应与日志
                returns = {"message": "", "data": results}
                customResponse.setMessageAndData(**returns)
                logger.funcReturns = returns
        return customResponse.getResponse()

    @app.route("/Ajax/Users/get_selfstudy_check_data", methods=['GET'])
    def getSelfstudyCheckData():
        with CustomResponse() as customResponse:
            with Logger(funcName="Users.getSelfstudyCheckData()") as logger:
                # ===============
                # 检查接口调用权限
                Authorization.check(rightsNeeded=({"department_id": 5, "actor": 0},
                                                  {"department_id": 6, "actor": 0},
                                                  {"department_id": 7, "actor": 0},
                                                  {"department_id": 8, "actor": 0},
                                                  {"department_id": 9, "actor": 0},
                                                  {"department_id": 10, "actor": 0},
                                                  {"department_id": 11, "actor": 0}), needLogin=True)
                # ===========
                # 执行接口流程
                results = DatabaseBasicOperations_Users.getSelfstudyCheckData()
                # ========================
                # 准备函数返回值和响应与日志
                returns = {"message": "", "data": results}
                customResponse.setMessageAndData(**returns)
                logger.funcReturns = returns
        return customResponse.getResponse()

    @app.route("/Ajax/Users/submit_selfstudy_record", methods=['POST'])
    def submitSelfstudyRecord():
        with CustomResponse() as customResponse:
            with Logger(funcName="Users.submitSelfstudyRecord()") as logger:
                # ===============
                # 检查接口调用权限
                Authorization.check(rightsNeeded=({"department_id": 5, "actor": 0},
                                                  {"department_id": 6, "actor": 0},
                                                  {"department_id": 7, "actor": 0},
                                                  {"department_id": 8, "actor": 0},
                                                  {"department_id": 9, "actor": 0},
                                                  {"department_id": 10, "actor": 0},
                                                  {"department_id": 11, "actor": 0}), needLogin=True)
                # ========================
                # 检查接口输入参数并记录日志
                infoForm = dict(request.get_json())
                Ajax_Users.submitSelfstudyRecordParamsCheck(infoForm)
                logger.funcArgs = request.get_json()
                # ===========
                # 执行接口流程
                DatabaseBasicOperations_Users.submitSelfstudyRecord(
                    infoForm)
                # ========================
                # 准备函数返回值和响应与日志
                returns = {"message": "", "data": ""}
                customResponse.setMessageAndData(**returns)
                logger.funcReturns = returns
        return customResponse.getResponse()

    @app.route("/Ajax/Users/get_courses_check_data", methods=['GET'])
    def getCoursesCheckData():
        with CustomResponse() as customResponse:
            with Logger(funcName="Users.getCoursesCheckData()") as logger:
                # ===============
                # 检查接口调用权限
                Authorization.check(rightsNeeded=({"department_id": 5, "actor": 0},
                                                  {"department_id": 6, "actor": 0},
                                                  {"department_id": 7, "actor": 0},
                                                  {"department_id": 8, "actor": 0},
                                                  {"department_id": 9, "actor": 0},
                                                  {"department_id": 10, "actor": 0},
                                                  {"department_id": 11, "actor": 0}), needLogin=True)
                # ===========
                # 执行接口流程
                results = DatabaseBasicOperations_Users.getCoursesCheckData()
                # ========================
                # 准备函数返回值和响应与日志
                returns = {"message": "", "data": results}
                customResponse.setMessageAndData(**returns)
                logger.funcReturns = returns
        return customResponse.getResponse()

    @app.route("/Ajax/Users/submit_courses_record", methods=['POST'])
    def submitCoursesRecord():
        with CustomResponse() as customResponse:
            with Logger(funcName="Users.submitCoursesRecord()") as logger:
                # ===============
                # 检查接口调用权限
                Authorization.check(rightsNeeded=({"department_id": 5, "actor": 0},
                                                  {"department_id": 6, "actor": 0},
                                                  {"department_id": 7, "actor": 0},
                                                  {"department_id": 8, "actor": 0},
                                                  {"department_id": 9, "actor": 0},
                                                  {"department_id": 10, "actor": 0},
                                                  {"department_id": 11, "actor": 0}), needLogin=True)
                # ========================
                # 检查接口输入参数并记录日志
                infoForm = dict(request.get_json())
                Ajax_Users.submitCoursesRecordParamsCheck(infoForm)
                logger.funcArgs = request.get_json()
                # ===========
                # 执行接口流程
                DatabaseBasicOperations_Users.submitCoursesRecord(
                    infoForm)
                # ========================
                # 准备函数返回值和响应与日志
                returns = {"message": "", "data": ""}
                customResponse.setMessageAndData(**returns)
                logger.funcReturns = returns
        return customResponse.getResponse()
