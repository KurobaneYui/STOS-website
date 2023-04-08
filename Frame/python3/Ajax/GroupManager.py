"""
This file is for group manager.
In this file, all URL apis are designed for manage group data.
Usually, all functions need group leader auth.
"""


import flask
from flask import request
from Frame.python3.BaseComponents.Logger import Logger
from Frame.python3.BaseComponents.Authorization import Authorization
from Frame.python3.BaseComponents.CustomResponse import CustomResponse
from Frame.python3.AjaxParamsCheck.Ajax_GroupManager import Ajax_GroupManager
from Frame.python3.DatabaseBasicOperations.DatabaseBasicOperations_GroupManager import DatabaseBasicOperations_GroupManager


def GroupManager(app: flask.Flask) -> None:
    @app.route("/Ajax/GroupManager/get_all_groups_members", methods=['GET'])
    def getAllGroupsMembers():
        with CustomResponse() as customResponse:
            with Logger(funcName="GroupManager.getAllGroupsMembers()") as logger:
                # ===============
                # 检查接口调用权限
                Authorization.check(rightsNeeded=(
                    {"department_id": None, "actor": '1'},), needLogin=True)
                # =========================================
                # 执行接口流程，并获取用户名信息以完成会话建立
                results = DatabaseBasicOperations_GroupManager.getAllGroupsMembers()
                # ========================
                # 准备函数返回值和响应与日志
                returns = {"message": "",
                           "data": results}
                customResponse.setMessageAndData(**returns)
                logger.funcReturns = returns
        return customResponse.getResponse()

    @app.route("/Ajax/GroupManager/search_member", methods=['POST'])
    def searchMember():
        with CustomResponse() as customResponse:
            with Logger(funcName="GroupManager.searchMember()") as logger:
                # ===============
                # 检查接口调用权限
                Authorization.check(rightsNeeded=(
                    {"department_id": None, "actor": '1'},), needLogin=True)
                # ========================
                # 检查接口输入参数并记录日志
                Ajax_GroupManager.searchMemberParamsCheck(request)
                logger.funcArgs = request.form
                # =========================================
                # 执行接口流程，并获取用户名信息以完成会话建立
                results = DatabaseBasicOperations_GroupManager.searchMember(
                    request)
                # ========================
                # 准备函数返回值和响应与日志
                returns = {"message": "",
                           "data": results}
                customResponse.setMessageAndData(**returns)
                logger.funcReturns = returns
        return customResponse.getResponse()

    @app.route("/Ajax/GroupManager/add_member", methods=['POST'])
    def addMember():
        with CustomResponse() as customResponse:
            with Logger(funcName="GroupManager.addMember()") as logger:
                # ===============
                # 检查接口调用权限
                Authorization.check(rightsNeeded=({"department_id": 0, "actor": '1'}, {
                                    "department_id": int(request.form["group_id"]), "actor": '1'}), needLogin=True)
                # ========================
                # 检查接口输入参数并记录日志
                Ajax_GroupManager.addMemberParamsCheck(request)
                logger.funcArgs = request.form
                # =========================================
                # 执行接口流程，并获取用户名信息以完成会话建立
                DatabaseBasicOperations_GroupManager.addMember(request)
                # ========================
                # 准备函数返回值和响应与日志
                returns = {"message": "", "data": ""}
                customResponse.setMessageAndData(**returns)
                logger.funcReturns = returns
        return customResponse.getResponse()

    @app.route("/Ajax/GroupManager/remove_member", methods=['POST'])
    def removeMember():
        with CustomResponse() as customResponse:
            with Logger(funcName="GroupManager.removeMember()") as logger:
                # ===============
                # 检查接口调用权限
                Authorization.check(rightsNeeded=({"department_id": 0, "actor": '1'}, {
                                    "department_id": int(request.form["group_id"]), "actor": '1'}), needLogin=True)
                # ========================
                # 检查接口输入参数并记录日志
                Ajax_GroupManager.removeMemberParamsCheck(request)
                logger.funcArgs = request.form
                # =========================================
                # 执行接口流程，并获取用户名信息以完成会话建立
                DatabaseBasicOperations_GroupManager.removeMember(request)
                # ========================
                # 准备函数返回值和响应与日志
                returns = {"message": "", "data": ""}
                customResponse.setMessageAndData(**returns)
                logger.funcReturns = returns
        return customResponse.getResponse()

    @app.route("/Ajax/GroupManager/get_group_selfstudy_check_data", methods=['GET'])
    def getGroupSelfstudyCheckData():
        with CustomResponse() as customResponse:
            with Logger(funcName="Users.getGroupSelfstudyCheckData()") as logger:
                # ===============
                # 检查接口调用权限
                Authorization.check(rightsNeeded=({"department_id": 5, "actor": 1},
                                                  {"department_id": 6, "actor": 1},
                                                  {"department_id": 7, "actor": 1},
                                                  {"department_id": 8, "actor": 1},
                                                  {"department_id": 9, "actor": 1},
                                                  {"department_id": 10, "actor": 1},
                                                  {"department_id": 11, "actor": 1},
                                                  {"department_id": 0, "actor": 1}), needLogin=True)
                # ===========
                # 执行接口流程
                results = DatabaseBasicOperations_GroupManager.getGroupSelfstudyCheckData()
                # ========================
                # 准备函数返回值和响应与日志
                returns = {"message": "", "data": results}
                customResponse.setMessageAndData(**returns)
                logger.funcReturns = returns
        return customResponse.getResponse()

    @app.route("/Ajax/GroupManager/submit_selfstudy_record_recheck", methods=['POST'])
    def submitSelfstudyRecordRecheck():
        with CustomResponse() as customResponse:
            with Logger(funcName="Users.submitSelfstudyRecordRecheck()") as logger:
                # ===============
                # 检查接口调用权限
                Authorization.check(rightsNeeded=({"department_id": 5, "actor": 1},
                                                  {"department_id": 6, "actor": 1},
                                                  {"department_id": 7, "actor": 1},
                                                  {"department_id": 8, "actor": 1},
                                                  {"department_id": 9, "actor": 1},
                                                  {"department_id": 10, "actor": 1},
                                                  {"department_id": 11, "actor": 1},
                                                  {"department_id": 0, "actor": 1}), needLogin=True)
                # ========================
                # 检查接口输入参数并记录日志
                infoForm = dict(request.form)
                Ajax_GroupManager.submitSelfstudyRecordRecheckParamsCheck(
                    infoForm)
                logger.funcArgs = request.form
                # ===========
                # 执行接口流程
                DatabaseBasicOperations_GroupManager.submitSelfstudyRecordRecheck(
                    infoForm)
                # ========================
                # 准备函数返回值和响应与日志
                returns = {"message": "", "data": ""}
                customResponse.setMessageAndData(**returns)
                logger.funcReturns = returns
        return customResponse.getResponse()

    @app.route("/Ajax/GroupManager/get_group_empty_table", methods=['GET'])
    def getGroupEmptyTable():
        with CustomResponse() as customResponse:
            with Logger(funcName="Users.getGroupEmptyTable()") as logger:
                # ===============
                # 检查接口调用权限
                Authorization.check(rightsNeeded=({"department_id": 5, "actor": 1},
                                                  {"department_id": 6, "actor": 1},
                                                  {"department_id": 7, "actor": 1},
                                                  {"department_id": 8, "actor": 1},
                                                  {"department_id": 9, "actor": 1},
                                                  {"department_id": 10, "actor": 1},
                                                  {"department_id": 11, "actor": 1}), needLogin=True)
                # ===========
                # 执行接口流程
                results = DatabaseBasicOperations_GroupManager.getGroupEmptyTable()
                # ========================
                # 准备函数返回值和响应与日志
                returns = {"message": "", "data": results}
                customResponse.setMessageAndData(**returns)
                logger.funcReturns = returns
        return customResponse.getResponse()

    @app.route("/Ajax/GroupManager/set_member_empty_table", methods=['POST'])
    def setMemberEmptyTable():
        with CustomResponse() as customResponse:
            with Logger(funcName="Users.setMemberEmptyTable()") as logger:
                # ===============
                # 检查接口调用权限
                Authorization.check(rightsNeeded=({"department_id": 5, "actor": 1},
                                                  {"department_id": 6, "actor": 1},
                                                  {"department_id": 7, "actor": 1},
                                                  {"department_id": 8, "actor": 1},
                                                  {"department_id": 9, "actor": 1},
                                                  {"department_id": 10, "actor": 1},
                                                  {"department_id": 11, "actor": 1}), needLogin=True)
                # ========================
                # 检查接口输入参数并记录日志
                infoForm = dict(request.form)
                Ajax_GroupManager.setMemberEmptyTableParamsCheck(
                    infoForm)
                logger.funcArgs = request.form
                # ===========
                # 执行接口流程
                results = DatabaseBasicOperations_GroupManager.setMemberEmptyTable(
                    infoForm)
                # ========================
                # 准备函数返回值和响应与日志
                returns = {"message": "", "data": results}
                customResponse.setMessageAndData(**returns)
                logger.funcReturns = returns
        return customResponse.getResponse()
