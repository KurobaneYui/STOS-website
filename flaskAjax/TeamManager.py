"""
This file is for team manager.
In this file, all URL apis are designed for manage team data.
Usually, all functions need team leader auth.
"""

import re
import flask
from flask import request
from flaskAjax.BaseComponents.Logger import Logger
from flaskAjax.BaseComponents.Authorization import Authorization
from flaskAjax.BaseComponents.CustomResponse import CustomResponse
from flaskAjax.DatabaseBasicOperations.TeamManager import TeamManagerDatabase
from flaskAjax.AjaxParamsCheck.TeamManager import TeamManagerCheck


def TeamManager(app: flask.Flask) -> None:
    @app.route("/Ajax/TeamManager/get_blacklist", methods=["GET"])
    def getBlacklist():
        with CustomResponse() as customResponse:
            with Logger(funcName="TeamManager.getBlacklist()") as logger:
                # ===============
                # 检查接口调用权限
                Authorization.check(
                    rightsNeeded=(
                        {"department_id": None, "actor": "manager"},
                        {"department_id": 1, "actor": "member"},
                    ),
                    needLogin=True,
                )
                # =========================================
                # 执行接口流程，并获取用户名信息以完成会话建立
                returns = TeamManagerDatabase.getBlacklist()
                # ========================
                # 准备函数返回值和响应与日志
                returns = {"message": "", "data": returns}
                customResponse.setMessageAndData(**returns)
                logger.funcReturns = returns
        return customResponse.getResponse()

    @app.route("/Ajax/TeamManager/get_department", methods=["GET"])
    def getDepartment():
        with CustomResponse() as customResponse:
            with Logger(funcName="TeamManager.getDepartment()") as logger:
                # ===============
                # 检查接口调用权限
                Authorization.check(
                    rightsNeeded=({"department_id": 1, "actor": "manager"},),
                    needLogin=True,
                )
                # =========================================
                # 执行接口流程，并获取用户名信息以完成会话建立
                returns = TeamManagerDatabase.getDepartment()
                # ========================
                # 准备函数返回值和响应与日志
                returns = {"message": "", "data": returns}
                customResponse.setMessageAndData(**returns)
                logger.funcReturns = returns
        return customResponse.getResponse()

    @app.route("/Ajax/TeamManager/update_department", methods=["POST"])
    def updateDepartment():
        with CustomResponse() as customResponse:
            with Logger(funcName="TeamManager.updateDepartment()") as logger:
                # ===============
                # 检查接口调用权限
                Authorization.check(
                    rightsNeeded=({"department_id": 1, "actor": "manager"},),
                    needLogin=True,
                )
                # ========================
                # 检查接口输入参数并记录日志
                assert request.json is not None
                infoForm = dict(request.json)
                TeamManagerCheck.updateDepartmentParamsCheck(infoForm)
                logger.funcArgs = request.json
                # =========================================
                # 执行接口流程，并获取用户名信息以完成会话建立
                TeamManagerDatabase.updateDepartment(infoForm)
                # ========================
                # 准备函数返回值和响应与日志
                returns: dict = {"message": "", "data": ""}
                customResponse.setMessageAndData(**returns)
                logger.funcReturns = returns
        return customResponse.getResponse()

    @app.route("/Ajax/TeamManager/download_finance_EXCEL", methods=["POST"])
    def downloadFinanceEXCEL():
        with CustomResponse() as customResponse:
            with Logger(funcName="TeamManager.downloadFinanceEXCEL()") as logger:
                # ===============
                # 检查接口调用权限
                Authorization.check(
                    rightsNeeded=(
                        {"department_id": 1, "actor": "manager"},
                        {"department_id": 1, "actor": "member"},
                    ),
                    needLogin=True,
                )
                # ========================
                # 检查接口输入参数并记录日志
                assert request.json is not None
                infoForm = dict(request.json)
                TeamManagerCheck.downloadFinanceEXCELParamsCheck(infoForm)
                logger.funcArgs = request.json
                # =========================================
                # 执行接口流程，并获取用户名信息以完成会话建立
                results = TeamManagerDatabase.downloadFinanceEXCEL(infoForm)
                # ========================
                # 准备函数返回值和响应与日志
                returns: dict = {"message": "", "data": results}
                customResponse.setMessageAndData(**returns)
                logger.funcReturns = returns
        return customResponse.getResponse()
