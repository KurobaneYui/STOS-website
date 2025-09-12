"""
This file is for background data manager.
In this file, all URL apis are designed for manage STSA background data.
Usually, all functions need team leader, Data-Management-Group member or leader auth.
"""

import flask
from flask import request
from flaskAjax.BaseComponents.Authorization import Authorization
from flaskAjax.BaseComponents.Logger import Logger
from flaskAjax.BaseComponents.CustomResponse import CustomResponse
from flaskAjax.AjaxParamsCheck.DataManager import DataManagerCheck
from flaskAjax.DatabaseBasicOperations.DataManager import DataManagerDatabase


def DataManager(app: flask.Flask) -> None:
    @app.route("/Ajax/DataManager/get_campus", methods=["GET"])
    def getCampus():
        with CustomResponse() as customResponse:
            with Logger(funcName="DataManager.getCampus()") as logger:
                # ===============
                # 检查接口调用权限
                Authorization.check(rightsNeeded=tuple(), needLogin=False)
                # =========================================
                # 执行接口流程，并获取用户名信息以完成会话建立
                results = DataManagerDatabase.getCampus()
                # ========================
                # 准备函数返回值和响应与日志
                returns = {"message": "", "data": results}
                customResponse.setMessageAndData(**returns)
                logger.funcReturns = returns
        return customResponse.getResponse()

    @app.route("/Ajax/DataManager/get_classroom", methods=["GET"])
    def getClassroom():
        with CustomResponse() as customResponse:
            with Logger(funcName="DataManager.getClassroom()") as logger:
                # ===============
                # 检查接口调用权限
                Authorization.check(
                    rightsNeeded=(
                        {"department_id": 1, "actor": "manager"},
                        {"department_id": 1, "actor": "member"},
                        {"department_id": 9, "actor": "manager"},
                        {"department_id": 9, "actor": "member"},
                    ),
                    needLogin=True,
                )
                # =========================================
                # 执行接口流程，并获取用户名信息以完成会话建立
                results = DataManagerDatabase.getClassroom()
                # ========================
                # 准备函数返回值和响应与日志
                returns = {"message": "", "data": results}
                customResponse.setMessageAndData(**returns)
                logger.funcReturns = returns
        return customResponse.getResponse()

    @app.route("/Ajax/DataManager/delete_classroom", methods=["POST"])
    def deleteClassroom():
        with CustomResponse() as customResponse:
            with Logger(funcName="DataManager.deleteClassroom()") as logger:
                # ===============
                # 检查接口调用权限
                Authorization.check(
                    rightsNeeded=(
                        {"department_id": 1, "actor": "manager"},
                        {"department_id": 1, "actor": "member"},
                        {"department_id": 9, "actor": "manager"},
                        {"department_id": 9, "actor": "member"},
                    ),
                    needLogin=True,
                )
                # ========================
                # 检查接口输入参数并记录日志
                assert request.json is not None
                infoForm = dict(request.json)
                DataManagerCheck.deleteClassroomParamsCheck(infoForm)
                logger.funcArgs = request.json
                # =========================================
                # 执行接口流程，并获取用户名信息以完成会话建立
                DataManagerDatabase.deleteClassroom(infoForm)
                # ========================
                # 准备函数返回值和响应与日志
                returns: dict = {"message": "", "data": ""}
                customResponse.setMessageAndData(**returns)
                logger.funcReturns = returns
        return customResponse.getResponse()

    @app.route("/Ajax/DataManager/add_classroom", methods=["POST"])
    def addClassroom():
        with CustomResponse() as customResponse:
            with Logger(funcName="DataManager.addClassroom()") as logger:
                # ===============
                # 检查接口调用权限
                Authorization.check(
                    rightsNeeded=(
                        {"department_id": 1, "actor": "manager"},
                        {"department_id": 1, "actor": "member"},
                        {"department_id": 9, "actor": "manager"},
                        {"department_id": 9, "actor": "member"},
                    ),
                    needLogin=True,
                )
                # ========================
                # 检查接口输入参数并记录日志
                assert request.json is not None
                infoForm = dict(request.json)
                DataManagerCheck.addClassroomParamsCheck(infoForm)
                logger.funcArgs = request.json
                # =========================================
                # 执行接口流程，并获取用户名信息以完成会话建立
                DataManagerDatabase.addClassroom(infoForm)
                # ========================
                # 准备函数返回值和响应与日志
                returns: dict = {"message": "", "data": ""}
                customResponse.setMessageAndData(**returns)
                logger.funcReturns = returns
        return customResponse.getResponse()

    @app.route("/Ajax/DataManager/update_classroom", methods=["POST"])
    def updateClassroom():
        with CustomResponse() as customResponse:
            with Logger(funcName="DataManager.updateClassroom()") as logger:
                # ===============
                # 检查接口调用权限
                Authorization.check(
                    rightsNeeded=(
                        {"department_id": 1, "actor": "manager"},
                        {"department_id": 1, "actor": "member"},
                        {"department_id": 9, "actor": "manager"},
                        {"department_id": 9, "actor": "member"},
                    ),
                    needLogin=True,
                )
                # ========================
                # 检查接口输入参数并记录日志
                assert request.json is not None
                infoForm = dict(request.json)
                DataManagerCheck.updateClassroomParamsCheck(infoForm)
                logger.funcArgs = request.json
                # =========================================
                # 执行接口流程，并获取用户名信息以完成会话建立
                DataManagerDatabase.updateClassroom(infoForm)
                # ========================
                # 准备函数返回值和响应与日志
                returns: dict = {"message": "", "data": ""}
                customResponse.setMessageAndData(**returns)
                logger.funcReturns = returns
        return customResponse.getResponse()

    @app.route("/Ajax/DataManager/get_school", methods=["GET"])
    def getSchool():
        with CustomResponse() as customResponse:
            with Logger(funcName="DataManager.getSchool()") as logger:
                # ===============
                # 检查接口调用权限
                Authorization.check(rightsNeeded=tuple(), needLogin=False)
                # =========================================
                # 执行接口流程，并获取用户名信息以完成会话建立
                results = DataManagerDatabase.getSchool()
                # ========================
                # 准备函数返回值和响应与日志
                returns = {"message": "", "data": results}
                customResponse.setMessageAndData(**returns)
                logger.funcReturns = returns
        return customResponse.getResponse()

    @app.route("/Ajax/DataManager/delete_school", methods=["POST"])
    def deleteSchool():
        with CustomResponse() as customResponse:
            with Logger(funcName="DataManager.deleteSchool()") as logger:
                # ===============
                # 检查接口调用权限
                Authorization.check(
                    rightsNeeded=(
                        {"department_id": 1, "actor": "manager"},
                        {"department_id": 9, "actor": "manager"},
                    ),
                    needLogin=True,
                )
                # ========================
                # 检查接口输入参数并记录日志
                assert request.json is not None
                infoForm = dict(request.json)
                DataManagerCheck.deleteSchoolParamsCheck(infoForm)
                logger.funcArgs = request.json
                # =========================================
                # 执行接口流程，并获取用户名信息以完成会话建立
                results = DataManagerDatabase.deleteSchool(infoForm)
                # ========================
                # 准备函数返回值和响应与日志
                returns = {"message": "", "data": results}
                customResponse.setMessageAndData(**returns)
                logger.funcReturns = returns
        return customResponse.getResponse()

    @app.route("/Ajax/DataManager/add_school", methods=["POST"])
    def addSchool():
        with CustomResponse() as customResponse:
            with Logger(funcName="DataManager.addSchool()") as logger:
                # ===============
                # 检查接口调用权限
                Authorization.check(
                    rightsNeeded=(
                        {"department_id": 1, "actor": "manager"},
                        {"department_id": 9, "actor": "manager"},
                    ),
                    needLogin=True,
                )
                # ========================
                # 检查接口输入参数并记录日志
                assert request.json is not None
                infoForm = dict(request.json)
                DataManagerCheck.addSchoolParamsCheck(infoForm)
                logger.funcArgs = request.json
                # =========================================
                # 执行接口流程，并获取用户名信息以完成会话建立
                results = DataManagerDatabase.addSchool(infoForm)
                # ========================
                # 准备函数返回值和响应与日志
                returns = {"message": "", "data": results}
                customResponse.setMessageAndData(**returns)
                logger.funcReturns = returns
        return customResponse.getResponse()

    @app.route("/Ajax/DataManager/update_school", methods=["POST"])
    def updateSchool():
        with CustomResponse() as customResponse:
            with Logger(funcName="DataManager.updateSchool()") as logger:
                # ===============
                # 检查接口调用权限
                Authorization.check(
                    rightsNeeded=(
                        {"department_id": 1, "actor": "manager"},
                        {"department_id": 9, "actor": "manager"},
                    ),
                    needLogin=True,
                )
                # ========================
                # 检查接口输入参数并记录日志
                assert request.json is not None
                infoForm = dict(request.json)
                DataManagerCheck.updateSchoolParamsCheck(infoForm)
                logger.funcArgs = request.json
                # =========================================
                # 执行接口流程，并获取用户名信息以完成会话建立
                DataManagerDatabase.updateSchool(infoForm)
                # ========================
                # 准备函数返回值和响应与日志
                returns: dict = {"message": "", "data": ""}
                customResponse.setMessageAndData(**returns)
                logger.funcReturns = returns
        return customResponse.getResponse()

    @app.route("/Ajax/DataManager/get_submitted_selfstudy_date", methods=["GET"])
    def getSubmittedSelfstudyDate():
        with CustomResponse() as customResponse:
            with Logger(funcName="DataManager.getSubmittedSelfstudyDate()") as logger:
                # ===============
                # 检查接口调用权限
                Authorization.check(
                    rightsNeeded=(
                        {"department_id": 9, "actor": "manager"},
                        {"department_id": 9, "actor": "member"},
                    ),
                    needLogin=True,
                )
                # =========================================
                # 执行接口流程，并获取用户名信息以完成会话建立
                results = DataManagerDatabase.getSubmittedSelfstudyDate()
                # ========================
                # 准备函数返回值和响应与日志
                returns = {"message": "", "data": results}
                customResponse.setMessageAndData(**returns)
                logger.funcReturns = returns
        return customResponse.getResponse()

    @app.route("/Ajax/DataManager/get_selfstudy_classroom_details", methods=["POST"])
    def getSelfstudyClassroomDetails():
        with CustomResponse() as customResponse:
            with Logger(
                funcName="DataManager.getSelfstudyClassroomDetails()"
            ) as logger:
                # ===============
                # 检查接口调用权限
                Authorization.check(
                    rightsNeeded=(
                        {"department_id": 9, "actor": "manager"},
                        {"department_id": 9, "actor": "member"},
                    ),
                    needLogin=True,
                )
                # ========================
                # 检查接口输入参数并记录日志
                assert request.json is not None
                infoForm = dict(request.json)
                DataManagerCheck.getSelfstudyClassroomDetailsParamsCheck(infoForm)
                logger.funcArgs = request.json
                # =========================================
                # 执行接口流程，并获取用户名信息以完成会话建立
                results = DataManagerDatabase.getSelfstudyClassroomDetails(infoForm)
                # ========================
                # 准备函数返回值和响应与日志
                returns = {"message": "", "data": results}
                customResponse.setMessageAndData(**returns)
                logger.funcReturns = returns
        return customResponse.getResponse()

    @app.route("/Ajax/DataManager/upload_selfstudy_classroom", methods=["POST"])
    def uploadSelfstudyClassroom():
        with CustomResponse() as customResponse:
            with Logger(funcName="DataManager.uploadSelfstudyClassroom()") as logger:
                # ===============
                # 检查接口调用权限
                Authorization.check(
                    rightsNeeded=(
                        {"department_id": 9, "actor": "manager"},
                        {"department_id": 9, "actor": "member"},
                    ),
                    needLogin=True,
                )
                # ========================
                # 检查接口输入参数并记录日志
                assert request.json is not None
                infoForm = request.json
                DataManagerCheck.uploadSelfstudyClassroomParamsCheck(infoForm)
                logger.funcArgs = request.json
                # =========================================
                # 执行接口流程，并获取用户名信息以完成会话建立
                DataManagerDatabase.uploadSelfstudyClassroom(infoForm)
                # ========================
                # 准备函数返回值和响应与日志
                returns: dict = {"message": "", "data": ""}
                customResponse.setMessageAndData(**returns)
                logger.funcReturns = returns
        return customResponse.getResponse()

    @app.route(
        "/Ajax/DataManager/get_submitted_selfstudy_schedule_date", methods=["GET"]
    )
    def getSubmittedSelfstudyScheduleDate():
        with CustomResponse() as customResponse:
            with Logger(
                funcName="DataManager.getSubmittedSelfstudyScheduleDate()"
            ) as logger:
                # ===============
                # 检查接口调用权限
                Authorization.check(
                    rightsNeeded=(
                        {"department_id": 9, "actor": "manager"},
                        {"department_id": 9, "actor": "member"},
                    ),
                    needLogin=True,
                )
                # =========================================
                # 执行接口流程，并获取用户名信息以完成会话建立
                results = DataManagerDatabase.getSubmittedSelfstudyScheduleDate()
                # ========================
                # 准备函数返回值和响应与日志
                returns = {"message": "", "data": results}
                customResponse.setMessageAndData(**returns)
                logger.funcReturns = returns
        return customResponse.getResponse()

    @app.route("/Ajax/DataManager/remove_schedule_on_date", methods=["POST"])
    def removeScheduleOnDate():
        with CustomResponse() as customResponse:
            with Logger(funcName="DataManager.removeScheduleOnDate()") as logger:
                # ===============
                # 检查接口调用权限
                Authorization.check(
                    rightsNeeded=(
                        {"department_id": 9, "actor": "manager"},
                        {"department_id": 9, "actor": "member"},
                    ),
                    needLogin=True,
                )
                # ========================
                # 检查接口输入参数并记录日志
                assert request.json is not None
                infoForm = dict(request.json)
                DataManagerCheck.removeScheduleOnDateParamsCheck(infoForm)
                logger.funcArgs = request.json
                # =========================================
                # 执行接口流程，并获取用户名信息以完成会话建立
                DataManagerDatabase.removeScheduleOnDate(infoForm)
                # ========================
                # 准备函数返回值和响应与日志
                returns: dict = {"message": "", "data": ""}
                customResponse.setMessageAndData(**returns)
                logger.funcReturns = returns
        return customResponse.getResponse()

    @app.route("/Ajax/DataManager/get_schedule_on_date", methods=["POST"])
    def getScheduleOnDate():
        with CustomResponse() as customResponse:
            with Logger(funcName="DataManager.getScheduleOnDate()") as logger:
                # ===============
                # 检查接口调用权限
                Authorization.check(
                    rightsNeeded=(
                        {"department_id": 9, "actor": "manager"},
                        {"department_id": 9, "actor": "member"},
                    ),
                    needLogin=True,
                )
                # ========================
                # 检查接口输入参数并记录日志
                assert request.json is not None
                infoForm = dict(request.json)
                DataManagerCheck.getScheduleOnDateParamsCheck(infoForm)
                logger.funcArgs = request.json
                # =========================================
                # 执行接口流程，并获取用户名信息以完成会话建立
                results = DataManagerDatabase.getScheduleOnDate(infoForm)
                # ========================
                # 准备函数返回值和响应与日志
                returns = {"message": "", "data": results}
                customResponse.setMessageAndData(**returns)
                logger.funcReturns = returns
        return customResponse.getResponse()

    @app.route("/Ajax/DataManager/submit_selfstudy_schedule", methods=["POST"])
    def submitSelfstudySchedule():
        with CustomResponse() as customResponse:
            with Logger(funcName="DataManager.submitSelfstudySchedule()") as logger:
                # ===============
                # 检查接口调用权限
                Authorization.check(
                    rightsNeeded=(
                        {"department_id": 9, "actor": "manager"},
                        {"department_id": 9, "actor": "member"},
                    ),
                    needLogin=True,
                )
                # ========================
                # 检查接口输入参数并记录日志
                infoForm = request.get_json()
                DataManagerCheck.submitSelfstudyScheduleParamsCheck(infoForm)
                logger.funcArgs = request.get_json()
                # =========================================
                # 执行接口流程，并获取用户名信息以完成会话建立
                DataManagerDatabase.submitSelfstudySchedule(infoForm)
                # ========================
                # 准备函数返回值和响应与日志
                returns: dict = {"message": "", "data": ""}
                customResponse.setMessageAndData(**returns)
                logger.funcReturns = returns
        return customResponse.getResponse()

    @app.route("/Ajax/DataManager/last_schedule_on_date", methods=["POST"])
    def lastScheduleOnDate():
        with CustomResponse() as customResponse:
            with Logger(funcName="DataManager.lastScheduleOnDate()") as logger:
                # ===============
                # 检查接口调用权限
                Authorization.check(
                    rightsNeeded=(
                        {"department_id": 9, "actor": "manager"},
                        {"department_id": 9, "actor": "member"},
                    ),
                    needLogin=True,
                )
                # ========================
                # 检查接口输入参数并记录日志
                infoForm = request.get_json()
                DataManagerCheck.lastScheduleOnDateCheck(infoForm)
                logger.funcArgs = request.get_json()
                # =========================================
                # 执行接口流程，并获取用户名信息以完成会话建立
                returns = DataManagerDatabase.lastScheduleOnDate(infoForm)
                # ========================
                # 准备函数返回值和响应与日志
                returns = {"message": "", "data": returns}
                customResponse.setMessageAndData(**returns)
                logger.funcReturns = returns
        return customResponse.getResponse()

    # @app.route("/Ajax/DataManager/download_selfstudy_all_data", methods=["POST"])
    # def downloadSelfstudyAllData():
    #     with CustomResponse() as customResponse:
    #         with Logger(funcName="DataManager.downloadSelfstudyAllData()") as logger:
    #             # ===============
    #             # 检查接口调用权限
    #             Authorization.check(
    #                 rightsNeeded=(
    #                     {"department_id": 0, "actor": 1},
    #                     {"department_id": 3, "actor": 0},
    #                     {"department_id": 3, "actor": 1},
    #                 ),
    #                 needLogin=True,
    #             )
    #             # ========================
    #             # 检查接口输入参数并记录日志
    #             infoForm = dict(request.form)
    #             DataManagerCheck.downloadSelfstudyAllDataParamsCheck(infoForm)
    #             logger.funcArgs = request.form
    #             # =========================================
    #             # 执行接口流程，并获取用户名信息以完成会话建立
    #             results = DataManagerDatabase.downloadSelfstudyAllData(infoForm)
    #             # ========================
    #             # 准备函数返回值和响应与日志
    #             returns = {"message": "", "data": results}
    #             customResponse.setMessageAndData(**returns)
    #             logger.funcReturns = returns
    #     return customResponse.getResponse()

    # @app.route("/Ajax/DataManager/download_courses_all_data", methods=["POST"])
    # def downloadCoursesAllData():
    #     with CustomResponse() as customResponse:
    #         with Logger(funcName="DataManager.downloadCoursesAllData()") as logger:
    #             # ===============
    #             # 检查接口调用权限
    #             Authorization.check(
    #                 rightsNeeded=(
    #                     {"department_id": 0, "actor": 1},
    #                     {"department_id": 3, "actor": 0},
    #                     {"department_id": 3, "actor": 1},
    #                 ),
    #                 needLogin=True,
    #             )
    #             # ========================
    #             # 检查接口输入参数并记录日志
    #             infoForm = dict(request.form)
    #             DataManagerCheck.downloadCoursesAllDataParamsCheck(infoForm)
    #             logger.funcArgs = request.form
    #             # =========================================
    #             # 执行接口流程，并获取用户名信息以完成会话建立
    #             results = DataManagerDatabase.downloadCoursesAllData(infoForm)
    #             # ========================
    #             # 准备函数返回值和响应与日志
    #             returns = {"message": "", "data": results}
    #             customResponse.setMessageAndData(**returns)
    #             logger.funcReturns = returns
    #     return customResponse.getResponse()

    # @app.route("/Ajax/DataManager/download_empty_time_all_data", methods=["GET"])
    # def downloadEmptyTimeAllData():
    #     with CustomResponse() as customResponse:
    #         with Logger(funcName="DataManager.downloadEmptyTimeAllData()") as logger:
    #             # ===============
    #             # 检查接口调用权限
    #             Authorization.check(
    #                 rightsNeeded=(
    #                     {"department_id": 0, "actor": 1},
    #                     {"department_id": 3, "actor": 0},
    #                     {"department_id": 3, "actor": 1},
    #                 ),
    #                 needLogin=True,
    #             )
    #             # =========================================
    #             # 执行接口流程，并获取用户名信息以完成会话建立
    #             results = DataManagerDatabase.downloadEmptyTimeAllData()
    #             # ========================
    #             # 准备函数返回值和响应与日志
    #             returns = {"message": "", "data": results}
    #             customResponse.setMessageAndData(**returns)
    #             logger.funcReturns = returns
    #     return customResponse.getResponse()
