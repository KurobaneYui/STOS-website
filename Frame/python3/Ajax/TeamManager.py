"""
This file is for team manager.
In this file, all URL apis are designed for manage team data.
Usually, all functions need team leader auth.
"""


import flask
from flask import request
from Frame.python3.BaseComponents.Logger import Logger
from Frame.python3.BaseComponents.Authorization import Authorization
from Frame.python3.BaseComponents.CustomResponse import CustomResponse
from Frame.python3.DatabaseBasicOperations.DatabaseBasicOperations_TeamManager import DatabaseBasicOperations_TeamManager
from Frame.python3.AjaxParamsCheck.Ajax_TeamManager import Ajax_TeamManager


def TeamManager(app: flask.Flask) -> None:
    @app.route("/Ajax/TeamManager/get_blacklist", methods=['GET'])
    def getBlacklist():
        with CustomResponse() as customResponse:
            with Logger(funcName="TeamManager.getBlacklist()") as logger:
                # ===============
                # 检查接口调用权限
                Authorization.check(rightsNeeded=(
                    {"department_id": 0, "actor": 1},), needLogin=True)
                # =========================================
                # 执行接口流程，并获取用户名信息以完成会话建立
                returns = DatabaseBasicOperations_TeamManager.getBlacklist()
                # ========================
                # 准备函数返回值和响应与日志
                returns = {"message": "",
                           "data": returns}
                customResponse.setMessageAndData(**returns)
                logger.funcReturns = returns
        return customResponse.getResponse()

    @app.route("/Ajax/TeamManager/get_department", methods=['GET'])
    def getDepartment():
        with CustomResponse() as customResponse:
            with Logger(funcName="TeamManager.getDepartment()") as logger:
                # ===============
                # 检查接口调用权限
                Authorization.check(rightsNeeded=(
                    {"department_id": 0, "actor": 1},), needLogin=True)
                # =========================================
                # 执行接口流程，并获取用户名信息以完成会话建立
                returns = DatabaseBasicOperations_TeamManager.getDepartment()
                # ========================
                # 准备函数返回值和响应与日志
                returns = {"message": "",
                           "data": returns}
                customResponse.setMessageAndData(**returns)
                logger.funcReturns = returns
        return customResponse.getResponse()

    @app.route("/Ajax/TeamManager/update_department", methods=['POST'])
    def updateDepartment():
        with CustomResponse() as customResponse:
            with Logger(funcName="TeamManager.updateDepartment()") as logger:
                # ===============
                # 检查接口调用权限
                Authorization.check(rightsNeeded=(
                    {"department_id": 0, "actor": 1},), needLogin=True)
                # ========================
                # 检查接口输入参数并记录日志
                infoForm = dict(request.form)
                Ajax_TeamManager.updateDepartmentParamsCheck(infoForm)
                logger.funcArgs = request.form
                # =========================================
                # 执行接口流程，并获取用户名信息以完成会话建立
                DatabaseBasicOperations_TeamManager.updateDepartment(infoForm)
                # ========================
                # 准备函数返回值和响应与日志
                returns = {"message": "",
                           "data": ""}
                customResponse.setMessageAndData(**returns)
                logger.funcReturns = returns
        return customResponse.getResponse()

    @app.route("/Ajax/TeamManager/download_finance_EXCEL", methods=['POST'])
    def downloadFinanceEXCEL():
        with CustomResponse() as customResponse:
            with Logger(funcName="TeamManager.downloadFinanceEXCEL()") as logger:
                # ===============
                # 检查接口调用权限
                Authorization.check(rightsNeeded=(
                    {"department_id": 0, "actor": 1},), needLogin=True)
                # ========================
                # 检查接口输入参数并记录日志
                infoForm = dict(request.form)
                Ajax_TeamManager.downloadFinanceEXCELParamsCheck(infoForm)
                logger.funcArgs = request.form
                # =========================================
                # 执行接口流程，并获取用户名信息以完成会话建立
                results = DatabaseBasicOperations_TeamManager.downloadFinanceEXCEL(
                    infoForm)
                # ========================
                # 准备函数返回值和响应与日志
                returns = {"message": "",
                           "data": results}
                customResponse.setMessageAndData(**returns)
                logger.funcReturns = returns
        return customResponse.getResponse()
