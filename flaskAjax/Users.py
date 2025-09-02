"""
This file is for user manager (self).
In this file, all URL apis are designed for manage user data self.
Usually, all functions need form user auth.
"""

from typing import Any
import flask
from flask import request
from flaskAjax.AjaxParamsCheck.Users import UsersCheck
from flaskAjax.BaseComponents.Logger import Logger
from flaskAjax.BaseComponents.CustomSession import CustomSession
from flaskAjax.BaseComponents.Authorization import Authorization
from flaskAjax.BaseComponents.CustomResponse import CustomResponse
from flaskAjax.BaseComponents.DatabaseConnector import SessionLocal
from flaskAjax.DatabaseBasicOperations.Users import UsersDatabase


def Users(app: flask.Flask) -> None:  # noqa: C901
    @app.route("/Ajax/Users/login", methods=["POST"])
    def login():
        with (
            CustomResponse() as customResponse,
            Logger(funcName="Users.login()") as logger,
            SessionLocal() as session,
        ):
            # ===============
            # 检查接口调用权限
            Authorization.check(rightsNeeded=tuple(), needLogin=False)
            # ========================
            # 检查接口输入参数并记录日志
            UsersCheck.loginParamsCheck(request)
            logger.funcArgs = request.json
            # =========================================
            # 执行接口流程，并获取用户名信息以完成会话建立
            UsersDatabase.login(request, session)
            results = UsersDatabase.getLoginWorks(session)
            # ========================
            # 准备函数返回值和响应与日志
            returns = {"message": "", "data": results}
            customResponse.setMessageAndData(**returns)
            logger.funcReturns = returns
        return customResponse.getResponse()

    @app.route("/Ajax/Users/get_login_works", methods=["GET"])
    def getLoginWorks():
        with (
            CustomResponse() as customResponse,
            Logger(funcName="Users.getLoginWorks()") as logger,
            SessionLocal() as session,
        ):
            # ===============
            # 检查接口调用权限
            Authorization.check(rightsNeeded=tuple(), needLogin=True)
            # =========================================
            # 执行接口流程，并获取用户名信息以完成会话建立
            results = UsersDatabase.getLoginWorks(session)
            # ========================
            # 准备函数返回值和响应与日志
            returns = {"message": "", "data": results}
            customResponse.setMessageAndData(**returns)
            logger.funcReturns = returns
        return customResponse.getResponse()

    @app.route("/Ajax/Users/login_as_specified_work", methods=["POST"])
    def loginAsSpecifiedWork():
        with (
            CustomResponse() as customResponse,
            Logger(funcName="Users.loginAsSpecifiedWork()") as logger,
            SessionLocal() as session,
        ):
            # ===============
            # 检查接口调用权限
            Authorization.check(rightsNeeded=tuple(), needLogin=True)
            # ========================
            # 检查接口输入参数并记录日志
            UsersCheck.loginAsSpecifiedWorkParamsCheck(request)
            logger.funcArgs = request.json
            # =========================================
            # 执行接口流程，并获取用户名信息以完成会话建立
            results = UsersDatabase.loginAsSpecifiedWork(request, session)
            # ========================
            # 准备函数返回值和响应与日志
            returns: dict[str, Any] = {"message": "", "data": results}
            customResponse.setMessageAndData(**returns)
            logger.funcReturns = returns
        return customResponse.getResponse()

    @app.route("/Ajax/Users/resetPassword", methods=["POST"])
    def resetPassword():
        with (
            CustomResponse() as customResponse,
            Logger(funcName="Users.resetPassword()") as logger,
            SessionLocal() as session,
        ):
            # ===============
            # 检查接口调用权限
            Authorization.check(rightsNeeded=tuple(), needLogin=False)
            # ========================
            # 检查接口输入参数并记录日志
            UsersCheck.resetPasswordParamsCheck(request)
            logger.funcArgs = request.json
            # ===========
            # 执行接口流程
            UsersDatabase.resetPassword(request, session)
            # ========================
            # 准备函数返回值和响应与日志
            returns: dict[str, Any] = {
                "message": "",
                "data": "/authentication/login.html",
            }
            customResponse.setMessageAndData(**returns)
            logger.funcReturns = returns
        return customResponse.getResponse()

    @app.route("/Ajax/Users/delete_personal_info", methods=["POST"])
    def deletePersonalInfo():
        with (
            CustomResponse() as customResponse,
            Logger(funcName="Users.deletePersonalInfo()") as logger,
            SessionLocal() as session,
        ):
            # ===============
            # 检查接口调用权限
            Authorization.check(rightsNeeded=tuple(), needLogin=False)
            # ========================
            # 检查接口输入参数并记录日志
            UsersCheck.deletePersonalInfoParamsCheck(request)
            logger.funcArgs = request.form
            # ===========
            # 执行接口流程
            UsersDatabase.deletePersonalInfo(session)
            flask.g.isLogin = False
            CustomSession.clearSession()
            # ========================
            # 准备函数返回值和响应与日志
            returns: dict[str, Any] = {"message": "", "data": ""}
            customResponse.setMessageAndData(**returns)
            logger.funcReturns = returns
        return customResponse.getResponse()

    @app.route("/Ajax/Users/register", methods=["POST"])
    def register():
        with (
            CustomResponse() as customResponse,
            Logger(funcName="Users.register()") as logger,
            SessionLocal() as session,
        ):
            # ===============
            # 检查接口调用权限
            Authorization.check(rightsNeeded=tuple(), needLogin=False)
            # ========================
            # 检查接口输入参数并记录日志
            assert request.json is not None
            infoDict = dict(request.json)
            UsersCheck.registerParamsCheck(infoDict, session)
            logger.funcArgs = request.json
            # ===========
            # 执行接口流程
            UsersDatabase.register(infoDict, session)
            flask.g.isLogin = False
            CustomSession.clearSession()
            # ========================
            # 准备函数返回值和响应与日志
            returns: dict[str, Any] = {
                "message": "",
                "data": "/authentication/login.html",
            }
            customResponse.setMessageAndData(**returns)
            logger.funcReturns = returns
        return customResponse.getResponse()

    @app.route("/Ajax/Users/logout", methods=["GET", "POST"])
    def logout():
        with (
            CustomResponse() as customResponse,
            Logger(funcName="Users.logout()") as logger,
        ):
            # ===============
            # 检查接口调用权限
            Authorization.check(rightsNeeded=tuple(), needLogin=False)
            # ===========
            # 执行接口流程
            flask.g.isLogin = False
            CustomSession.clearSession()
            # ========================
            # 准备函数返回值和响应与日志
            returns: dict[str, Any] = {"message": "", "data": "finished"}
            customResponse.setMessageAndData(**returns)
            logger.funcReturns = returns
        return customResponse.getResponse()

    @app.route("/Ajax/Users/topbarInfo", methods=["GET"])
    def topbarInfo():
        with (
            CustomResponse() as customResponse,
            Logger(funcName="Users.topbarInfo()") as logger,
        ):
            # ===============
            # 检查接口调用权限
            Authorization.check(rightsNeeded=tuple(), needLogin=True)
            # ===========
            # 执行接口流程
            results = UsersDatabase.topbarInfo()
            # ========================
            # 准备函数返回值和响应与日志
            returns: dict[str, Any] = {"message": "", "data": results}
            customResponse.setMessageAndData(**returns)
            logger.funcReturns = returns
        return customResponse.getResponse()

    @app.route("/Ajax/Users/get_contact", methods=['GET'])
    def getContact():
        with CustomResponse() as customResponse:
            with Logger(funcName="Users.getContact()") as logger:
                # ===============
                # 检查接口调用权限
                Authorization.check(
                    rightsNeeded=({
                        "department_id": None,
                        "actor": "member"
                    }, {
                        "department_id": None,
                        "actor": "manager"
                    }),
                    needLogin=True
                )
                # ===========
                # 执行接口流程
                results = UsersDatabase.getContact()
                # ========================
                # 准备函数返回值和响应与日志
                returns = {"message": "", "data": results}
                customResponse.setMessageAndData(**returns)
                logger.funcReturns = returns
        return customResponse.getResponse()

    @app.route("/Ajax/Users/get_personal_info", methods=["GET"])
    def getPersonalInfo():
        with CustomResponse() as customResponse:
            with Logger(funcName="Users.getPersonalInfo()") as logger:
                # ===============
                # 检查接口调用权限
                Authorization.check(rightsNeeded=tuple(), needLogin=True)
                # ===========
                # 执行接口流程
                results = UsersDatabase.getPersonalInfo()
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
                assert request.json is not None
                infoDict = dict(request.json)
                UsersCheck.changePersonalInfoParamsCheck(infoDict)
                logger.funcArgs = request.json
                # ===========
                # 执行接口流程
                results = UsersDatabase.changePersonalInfo(infoDict)
                # ========================
                # 准备函数返回值和响应与日志
                returns: dict = {"message": results, "data": ""}
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
                results = UsersDatabase.getEmptyTimeInfo()
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
                Authorization.check(
                    rightsNeeded=({
                        "department_id": None,
                        "actor": "manager"
                    }, {
                        "department_id": None,
                        "actor": "member"
                    }),
                    needLogin=True
                )
                # ===========
                # 执行接口流程
                results = UsersDatabase.getWorkBasicInfo()
                # ========================
                # 准备函数返回值和响应与日志
                returns = {"message": "", "data": results}
                customResponse.setMessageAndData(**returns)
                logger.funcReturns = returns
        return customResponse.getResponse()

    # @app.route("/Ajax/Users/get_score_details", methods=['GET'])
    # def getScoreDetails():
    #     with CustomResponse() as customResponse:
    #         with Logger(funcName="Users.getScoreDetails()") as logger:
    #             # ===============
    #             # 检查接口调用权限
    #             Authorization.check(
    #                 rightsNeeded=({
    #                     "department_id": 0,
    #                     "actor": 0
    #                 }, {
    #                     "department_id": 0,
    #                     "actor": 1
    #                 }),
    #                 needLogin=True
    #             )
    #             # ===========
    #             # 执行接口流程
    #             results = UsersDatabase.getScoreDetails()
    #             # ========================
    #             # 准备函数返回值和响应与日志
    #             returns = {"message": "", "data": results}
    #             customResponse.setMessageAndData(**returns)
    #             logger.funcReturns = returns
    #     return customResponse.getResponse()

    # @app.route("/Ajax/Users/get_schedule_recent", methods=['GET'])
    # def getScheduleRecent():
    #     with CustomResponse() as customResponse:
    #         with Logger(funcName="Users.getScheduleRecent()") as logger:
    #             # ===============
    #             # 检查接口调用权限
    #             Authorization.check(
    #                 rightsNeeded=({
    #                     "department_id": 5,
    #                     "actor": 0
    #                 }, {
    #                     "department_id": 6,
    #                     "actor": 0
    #                 }, {
    #                     "department_id": 7,
    #                     "actor": 0
    #                 }, {
    #                     "department_id": 8,
    #                     "actor": 0
    #                 }, {
    #                     "department_id": 9,
    #                     "actor": 0
    #                 }, {
    #                     "department_id": 10,
    #                     "actor": 0
    #                 }, {
    #                     "department_id": 11,
    #                     "actor": 0
    #                 }),
    #                 needLogin=True
    #             )
    #             # ===========
    #             # 执行接口流程
    #             results = UsersDatabase.getScheduleRecent()
    #             # ========================
    #             # 准备函数返回值和响应与日志
    #             returns = {"message": "", "data": results}
    #             customResponse.setMessageAndData(**returns)
    #             logger.funcReturns = returns
    #     return customResponse.getResponse()

    # @app.route("/Ajax/Users/get_selfstudy_check_data", methods=['GET'])
    # def getSelfstudyCheckData():
    #     with CustomResponse() as customResponse:
    #         with Logger(funcName="Users.getSelfstudyCheckData()") as logger:
    #             # ===============
    #             # 检查接口调用权限
    #             Authorization.check(
    #                 rightsNeeded=({
    #                     "department_id": 5,
    #                     "actor": 0
    #                 }, {
    #                     "department_id": 6,
    #                     "actor": 0
    #                 }, {
    #                     "department_id": 7,
    #                     "actor": 0
    #                 }, {
    #                     "department_id": 8,
    #                     "actor": 0
    #                 }, {
    #                     "department_id": 9,
    #                     "actor": 0
    #                 }, {
    #                     "department_id": 10,
    #                     "actor": 0
    #                 }, {
    #                     "department_id": 11,
    #                     "actor": 0
    #                 }),
    #                 needLogin=True
    #             )
    #             # ===========
    #             # 执行接口流程
    #             results = UsersDatabase.getSelfstudyCheckData()
    #             # ========================
    #             # 准备函数返回值和响应与日志
    #             returns = {"message": "", "data": results}
    #             customResponse.setMessageAndData(**returns)
    #             logger.funcReturns = returns
    #     return customResponse.getResponse()

    # @app.route("/Ajax/Users/submit_selfstudy_record", methods=['POST'])
    # def submitSelfstudyRecord():
    #     with CustomResponse() as customResponse:
    #         with Logger(funcName="Users.submitSelfstudyRecord()") as logger:
    #             # ===============
    #             # 检查接口调用权限
    #             Authorization.check(
    #                 rightsNeeded=({
    #                     "department_id": 5,
    #                     "actor": 0
    #                 }, {
    #                     "department_id": 6,
    #                     "actor": 0
    #                 }, {
    #                     "department_id": 7,
    #                     "actor": 0
    #                 }, {
    #                     "department_id": 8,
    #                     "actor": 0
    #                 }, {
    #                     "department_id": 9,
    #                     "actor": 0
    #                 }, {
    #                     "department_id": 10,
    #                     "actor": 0
    #                 }, {
    #                     "department_id": 11,
    #                     "actor": 0
    #                 }),
    #                 needLogin=True
    #             )
    #             # ========================
    #             # 检查接口输入参数并记录日志
    #             infoForm = dict(request.get_json())
    #             UsersCheck.submitSelfstudyRecordParamsCheck(infoForm)
    #             logger.funcArgs = request.get_json()
    #             # ===========
    #             # 执行接口流程
    #             UsersDatabase.submitSelfstudyRecord(infoForm)
    #             # ========================
    #             # 准备函数返回值和响应与日志
    #             returns = {"message": "", "data": ""}
    #             customResponse.setMessageAndData(**returns)
    #             logger.funcReturns = returns
    #     return customResponse.getResponse()

    # @app.route("/Ajax/Users/get_courses_check_data", methods=['GET'])
    # def getCoursesCheckData():
    #     with CustomResponse() as customResponse:
    #         with Logger(funcName="Users.getCoursesCheckData()") as logger:
    #             # ===============
    #             # 检查接口调用权限
    #             Authorization.check(
    #                 rightsNeeded=({
    #                     "department_id": 5,
    #                     "actor": 0
    #                 }, {
    #                     "department_id": 6,
    #                     "actor": 0
    #                 }, {
    #                     "department_id": 7,
    #                     "actor": 0
    #                 }, {
    #                     "department_id": 8,
    #                     "actor": 0
    #                 }, {
    #                     "department_id": 9,
    #                     "actor": 0
    #                 }, {
    #                     "department_id": 10,
    #                     "actor": 0
    #                 }, {
    #                     "department_id": 11,
    #                     "actor": 0
    #                 }),
    #                 needLogin=True
    #             )
    #             # ===========
    #             # 执行接口流程
    #             results = UsersDatabase.getCoursesCheckData()
    #             # ========================
    #             # 准备函数返回值和响应与日志
    #             returns = {"message": "", "data": results}
    #             customResponse.setMessageAndData(**returns)
    #             logger.funcReturns = returns
    #     return customResponse.getResponse()

    # @app.route("/Ajax/Users/submit_courses_record", methods=['POST'])
    # def submitCoursesRecord():
    #     with CustomResponse() as customResponse:
    #         with Logger(funcName="Users.submitCoursesRecord()") as logger:
    #             # ===============
    #             # 检查接口调用权限
    #             Authorization.check(
    #                 rightsNeeded=({
    #                     "department_id": 5,
    #                     "actor": 0
    #                 }, {
    #                     "department_id": 6,
    #                     "actor": 0
    #                 }, {
    #                     "department_id": 7,
    #                     "actor": 0
    #                 }, {
    #                     "department_id": 8,
    #                     "actor": 0
    #                 }, {
    #                     "department_id": 9,
    #                     "actor": 0
    #                 }, {
    #                     "department_id": 10,
    #                     "actor": 0
    #                 }, {
    #                     "department_id": 11,
    #                     "actor": 0
    #                 }),
    #                 needLogin=True
    #             )
    #             # ========================
    #             # 检查接口输入参数并记录日志
    #             infoForm = dict(request.get_json())
    #             UsersCheck.submitCoursesRecordParamsCheck(infoForm)
    #             logger.funcArgs = request.get_json()
    #             # ===========
    #             # 执行接口流程
    #             UsersDatabase.submitCoursesRecord(infoForm)
    #             # ========================
    #             # 准备函数返回值和响应与日志
    #             returns = {"message": "", "data": ""}
    #             customResponse.setMessageAndData(**returns)
    #             logger.funcReturns = returns
    #     return customResponse.getResponse()
