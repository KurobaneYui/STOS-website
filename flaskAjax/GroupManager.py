"""
This file is for group manager.
In this file, all URL apis are designed for manage group data.
Usually, all functions need group leader auth.
"""

import flask
from flask import request
from flaskAjax.BaseComponents.Logger import Logger
from flaskAjax.BaseComponents.Authorization import Authorization
from flaskAjax.BaseComponents.CustomResponse import CustomResponse
from flaskAjax.AjaxParamsCheck.GroupManager import GroupManagerCheck
from flaskAjax.DatabaseBasicOperations.GroupManager import GroupManagerDatabase


def GroupManager(app: flask.Flask) -> None:
    @app.route("/Ajax/GroupManager/get_all_groups_members", methods=["GET"])
    def getAllGroupsMembers():
        with CustomResponse() as customResponse:
            with Logger(funcName="GroupManager.getAllGroupsMembers()") as logger:
                # ===============
                # 检查接口调用权限
                Authorization.check(
                    rightsNeeded=({"department_id": None, "actor": "manager"},),
                    needLogin=True,
                )
                # =========================================
                # 执行接口流程，并获取用户名信息以完成会话建立
                results = GroupManagerDatabase.getAllGroupsMembers()
                # ========================
                # 准备函数返回值和响应与日志
                returns = {"message": "", "data": results}
                customResponse.setMessageAndData(**returns)
                logger.funcReturns = returns
        return customResponse.getResponse()

    @app.route("/Ajax/GroupManager/search_member", methods=["POST"])
    def searchMember():
        with CustomResponse() as customResponse:
            with Logger(funcName="GroupManager.searchMember()") as logger:
                # ===============
                # 检查接口调用权限
                Authorization.check(
                    rightsNeeded=(
                        {"department_id": None, "actor": "manager"},
                        {"department_id": 1, "actor": "member"},
                    ),
                    needLogin=True,
                )
                # ========================
                # 检查接口输入参数并记录日志
                assert request.json is not None
                infoForm = dict(request.json)
                GroupManagerCheck.searchMemberParamsCheck(infoForm)
                logger.funcArgs = request.json
                # =========================================
                # 执行接口流程，并获取用户名信息以完成会话建立
                results = GroupManagerDatabase.searchMember(infoForm)
                # ========================
                # 准备函数返回值和响应与日志
                returns = {"message": "", "data": results}
                customResponse.setMessageAndData(**returns)
                logger.funcReturns = returns
        return customResponse.getResponse()

    @app.route("/Ajax/GroupManager/add_member", methods=["POST"])
    def addMember():
        with CustomResponse() as customResponse:
            with Logger(funcName="GroupManager.addMember()") as logger:
                # ===============
                # 检查接口调用权限
                assert request.json is not None
                infoForm = dict(request.json)
                Authorization.check(
                    rightsNeeded=(
                        {"department_id": 1, "actor": "member"},
                        {"department_id": 1, "actor": "manager"},
                        {
                            "department_id": infoForm["group_id"],
                            "actor": "manager",
                        },
                    ),
                    needLogin=True,
                )
                # ========================
                # 检查接口输入参数并记录日志
                GroupManagerCheck.addMemberParamsCheck(infoForm)
                logger.funcArgs = request.json
                # =========================================
                # 执行接口流程，并获取用户名信息以完成会话建立
                GroupManagerDatabase.addMember(infoForm)
                # ========================
                # 准备函数返回值和响应与日志
                returns: dict = {"message": "", "data": ""}
                customResponse.setMessageAndData(**returns)
                logger.funcReturns = returns
        return customResponse.getResponse()

    @app.route("/Ajax/GroupManager/remove_member", methods=["POST"])
    def removeMember():
        with CustomResponse() as customResponse:
            with Logger(funcName="GroupManager.removeMember()") as logger:
                # ===============
                # 检查接口调用权限
                assert request.json is not None
                infoForm = dict(request.json)
                Authorization.check(
                    rightsNeeded=(
                        {"department_id": 1, "actor": "member"},
                        {"department_id": 1, "actor": "manager"},
                        {
                            "department_id": infoForm["group_id"],
                            "actor": "manager",
                        },
                    ),
                    needLogin=True,
                )
                # ========================
                # 检查接口输入参数并记录日志
                GroupManagerCheck.removeMemberParamsCheck(infoForm)
                logger.funcArgs = request.json
                # =========================================
                # 执行接口流程，并获取用户名信息以完成会话建立
                GroupManagerDatabase.removeMember(infoForm)
                # ========================
                # 准备函数返回值和响应与日志
                returns: dict = {"message": "", "data": ""}
                customResponse.setMessageAndData(**returns)
                logger.funcReturns = returns
        return customResponse.getResponse()

    @app.route("/Ajax/GroupManager/get_group_selfstudy_check_data", methods=["GET"])
    def getGroupSelfstudyCheckData():
        with CustomResponse() as customResponse:
            with Logger(funcName="Users.getGroupSelfstudyCheckData()") as logger:
                # ===============
                # 检查接口调用权限
                Authorization.check(
                    rightsNeeded=({"department_id": None, "actor": "manager"},),
                    needLogin=True,
                )
                # ===========
                # 执行接口流程
                results = GroupManagerDatabase.getGroupSelfstudyCheckData()
                # ========================
                # 准备函数返回值和响应与日志
                returns = {"message": "", "data": results}
                customResponse.setMessageAndData(**returns)
                logger.funcReturns = returns
        return customResponse.getResponse()

    @app.route("/Ajax/GroupManager/submit_selfstudy_record_recheck", methods=["POST"])
    def submitSelfstudyRecordRecheck():
        with CustomResponse() as customResponse:
            with Logger(funcName="Users.submitSelfstudyRecordRecheck()") as logger:
                # ===============
                # 检查接口调用权限
                Authorization.check(
                    rightsNeeded=({"department_id": None, "actor": "manager"},),
                    needLogin=True,
                )
                # ========================
                # 检查接口输入参数并记录日志
                infoForm = dict(request.get_json())
                GroupManagerCheck.submitSelfstudyRecordRecheckParamsCheck(infoForm)
                logger.funcArgs = request.get_json()
                # ===========
                # 执行接口流程
                GroupManagerDatabase.submitSelfstudyRecordRecheck(infoForm)
                # ========================
                # 准备函数返回值和响应与日志
                returns: dict = {"message": "", "data": ""}
                customResponse.setMessageAndData(**returns)
                logger.funcReturns = returns
        return customResponse.getResponse()

    # @app.route("/Ajax/GroupManager/get_xianchang_group_list", methods=['GET'])
    # def getXianchangGroupList():
    #     with CustomResponse() as customResponse:
    #         with Logger(funcName="Users.getXianchangGroupList()") as logger:
    #             # ===============
    #             # 检查接口调用权限
    #             Authorization.check(rightsNeeded=({"department_id": 5, "actor": 1},
    #                                               {"department_id": 6, "actor": 1},
    #                                               {"department_id": 7, "actor": 1},
    #                                               {"department_id": 8, "actor": 1},
    #                                               {"department_id": 9, "actor": 1},
    #                                               {"department_id": 10, "actor": 1},
    #                                               {"department_id": 11, "actor": 1},
    #                                               {"department_id": 0, "actor": 1}), needLogin=True)
    #             # ===========
    #             # 执行接口流程
    #             results = GroupManagerDatabase.getXianchangGroupList()
    #             # ========================
    #             # 准备函数返回值和响应与日志
    #             returns = {"message": "", "data": results}
    #             customResponse.setMessageAndData(**returns)
    #             logger.funcReturns = returns
    #     return customResponse.getResponse()

    # @app.route("/Ajax/GroupManager/get_group_courses_check_data", methods=['POST'])
    # def getGroupCoursesCheckData():
    #     with CustomResponse() as customResponse:
    #         with Logger(funcName="Users.getGroupCoursesCheckData()") as logger:
    #             # ===============
    #             # 检查接口调用权限
    #             Authorization.check(rightsNeeded=({"department_id": 5, "actor": 1},
    #                                               {"department_id": 6, "actor": 1},
    #                                               {"department_id": 7, "actor": 1},
    #                                               {"department_id": 8, "actor": 1},
    #                                               {"department_id": 9, "actor": 1},
    #                                               {"department_id": 10, "actor": 1},
    #                                               {"department_id": 11, "actor": 1},
    #                                               {"department_id": 0, "actor": 1}), needLogin=True)
    #             # ========================
    #             # 检查接口输入参数并记录日志
    #             infoForm = dict(request.json)
    #             GroupManagerCheck.getGroupCoursesCheckDataParamsCheck(
    #                 infoForm)
    #             logger.funcArgs = request.json
    #             # ===========
    #             # 执行接口流程
    #             results = GroupManagerDatabase.getGroupCoursesCheckData(infoForm)
    #             # ========================
    #             # 准备函数返回值和响应与日志
    #             returns = {"message": "", "data": results}
    #             customResponse.setMessageAndData(**returns)
    #             logger.funcReturns = returns
    #     return customResponse.getResponse()

    # @app.route("/Ajax/GroupManager/submit_courses_record_recheck", methods=['POST'])
    # def submitCoursesRecordRecheck():
    #     with CustomResponse() as customResponse:
    #         with Logger(funcName="Users.submitCoursesRecordRecheck()") as logger:
    #             # ===============
    #             # 检查接口调用权限
    #             Authorization.check(rightsNeeded=({"department_id": 5, "actor": 1},
    #                                               {"department_id": 6, "actor": 1},
    #                                               {"department_id": 7, "actor": 1},
    #                                               {"department_id": 8, "actor": 1},
    #                                               {"department_id": 9, "actor": 1},
    #                                               {"department_id": 10, "actor": 1},
    #                                               {"department_id": 11, "actor": 1},
    #                                               {"department_id": 0, "actor": 1}), needLogin=True)
    #             # ========================
    #             # 检查接口输入参数并记录日志
    #             infoForm = dict(request.form)
    #             GroupManagerCheck.submitCoursesRecordRecheckParamsCheck(
    #                 infoForm)
    #             logger.funcArgs = request.form
    #             # ===========
    #             # 执行接口流程
    #             GroupManagerDatabase.submitCoursesRecordRecheck(
    #                 infoForm)
    #             # ========================
    #             # 准备函数返回值和响应与日志
    #             returns = {"message": "", "data": ""}
    #             customResponse.setMessageAndData(**returns)
    #             logger.funcReturns = returns
    #     return customResponse.getResponse()

    @app.route("/Ajax/GroupManager/get_group_empty_table", methods=["GET"])
    def getGroupEmptyTable():
        with CustomResponse() as customResponse:
            with Logger(funcName="Users.getGroupEmptyTable()") as logger:
                # ===============
                # 检查接口调用权限
                Authorization.check(
                    rightsNeeded=(
                        {"department_id": None, "actor": "manager"},
                        {"department_id": 1, "actor": "member"},
                    ),
                    needLogin=True,
                )
                # ===========
                # 执行接口流程
                results = GroupManagerDatabase.getGroupEmptyTable()
                # ========================
                # 准备函数返回值和响应与日志
                returns = {"message": "", "data": results}
                customResponse.setMessageAndData(**returns)
                logger.funcReturns = returns
        return customResponse.getResponse()

    @app.route("/Ajax/GroupManager/set_member_empty_table", methods=["POST"])
    def setMemberEmptyTable():
        with CustomResponse() as customResponse:
            with Logger(funcName="Users.setMemberEmptyTable()") as logger:
                # ===============
                # 检查接口调用权限
                Authorization.check(
                    rightsNeeded=(
                        {"department_id": None, "actor": "manager"},
                        {"department_id": 1, "actor": "member"},
                    ),
                    needLogin=True,
                )
                # ========================
                # 检查接口输入参数并记录日志
                assert request.json is not None
                infoForm = dict(request.json)
                GroupManagerCheck.setMemberEmptyTableParamsCheck(infoForm)
                logger.funcArgs = request.json
                # ===========
                # 执行接口流程
                results = GroupManagerDatabase.setMemberEmptyTable(infoForm)
                # ========================
                # 准备函数返回值和响应与日志
                returns = {"message": "", "data": results}
                customResponse.setMessageAndData(**returns)
                logger.funcReturns = returns
        return customResponse.getResponse()
