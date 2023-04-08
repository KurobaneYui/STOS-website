"""
This file is for background data manager.
In this file, all URL apis are designed for manage STSA background data.
Usually, all functions need team leader, Data-Management-Group member or leader auth.
"""


import flask
from flask import request
from Frame.python3.BaseComponents.Authorization import Authorization
from Frame.python3.BaseComponents.Logger import Logger
from Frame.python3.BaseComponents.CustomResponse import CustomResponse
from Frame.python3.AjaxParamsCheck.Ajax_DataManager import Ajax_DataManager
from Frame.python3.DatabaseBasicOperations.DatabaseBasicOperations_DataManager import DatabaseBasicOperations_DataManager


def DataManager(app: flask.Flask) -> None:
    @app.route("/Ajax/DataManager/get_campus_for_form", methods=['GET'])
    def getCampusForForm():
        with CustomResponse() as customResponse:
            with Logger(funcName="DataManager.getCampusForForm()") as logger:
                # ===============
                # 检查接口调用权限
                Authorization.check(rightsNeeded=tuple(), needLogin=False)
                # =========================================
                # 执行接口流程，并获取用户名信息以完成会话建立
                results = DatabaseBasicOperations_DataManager.getCampusForForm()
                # ========================
                # 准备函数返回值和响应与日志
                returns = {"message": "",
                           "data": results}
                customResponse.setMessageAndData(**returns)
                logger.funcReturns = returns
        return customResponse.getResponse()

    @app.route("/Ajax/DataManager/get_school_for_form", methods=['POST'])
    def getSchoolForForm():
        with CustomResponse() as customResponse:
            with Logger(funcName="DataManager.getSchoolForForm()") as logger:
                # ===============
                # 检查接口调用权限
                Authorization.check(rightsNeeded=tuple(), needLogin=False)
                # # ========================
                # # 检查接口输入参数并记录日志
                # Ajax_DataManager.getSchoolForFormParamsCheck(request)
                # logger.funcArgs = request.form
                # =========================================
                # 执行接口流程，并获取用户名信息以完成会话建立
                results = DatabaseBasicOperations_DataManager.getSchoolForForm(
                    request)
                # ========================
                # 准备函数返回值和响应与日志
                returns = {"message": "",
                           "data": results}
                customResponse.setMessageAndData(**returns)
                logger.funcReturns = returns
        return customResponse.getResponse()

    @app.route("/Ajax/DataManager/get_school", methods=['GET'])
    def getSchool():
        with CustomResponse() as customResponse:
            with Logger(funcName="DataManager.getSchool()") as logger:
                # ===============
                # 检查接口调用权限
                Authorization.check(rightsNeeded=({"department_id": 0, "actor": 1}, {
                                    "department_id": 3, "actor": 1}), needLogin=True)
                # =========================================
                # 执行接口流程，并获取用户名信息以完成会话建立
                results = DatabaseBasicOperations_DataManager.getSchool()
                # ========================
                # 准备函数返回值和响应与日志
                returns = {"message": "",
                           "data": results}
                customResponse.setMessageAndData(**returns)
                logger.funcReturns = returns
        return customResponse.getResponse()

    @app.route("/Ajax/DataManager/get_classroom", methods=['POST'])
    def getClassroom():
        with CustomResponse() as customResponse:
            with Logger(funcName="DataManager.getClassroom()") as logger:
                # ===============
                # 检查接口调用权限
                Authorization.check(rightsNeeded=({"department_id": 0, "actor": 1}, {
                                    "department_id": 3, "actor": 1}), needLogin=True)
                # # ========================
                # # 检查接口输入参数并记录日志
                # Ajax_DataManager.getClassroomParamsCheck(request)
                # logger.funcArgs = request.form
                # =========================================
                # 执行接口流程，并获取用户名信息以完成会话建立
                results = DatabaseBasicOperations_DataManager.getClassroom(
                    request)
                # ========================
                # 准备函数返回值和响应与日志
                returns = {"message": "",
                           "data": results}
                customResponse.setMessageAndData(**returns)
                logger.funcReturns = returns
        return customResponse.getResponse()

    @app.route("/Ajax/DataManager/update_school", methods=['POST'])
    # @Auth(({"department_id": 3, "actor": "10"}, {"department_id": 1, "actor": None}))
    def updateSchool():
        with CustomResponse() as customResponse:
            with Logger(funcName="DataManager.updateSchool()") as logger:
                # ===============
                # 检查接口调用权限
                Authorization.check(rightsNeeded=({"department_id": 0, "actor": 1}, {
                                    "department_id": 3, "actor": 1}), needLogin=True)
                # ========================
                # 检查接口输入参数并记录日志
                infoForm = dict(request.form)
                Ajax_DataManager.updateSchoolParamsCheck(infoForm)
                logger.funcArgs = request.form
                # =========================================
                # 执行接口流程，并获取用户名信息以完成会话建立
                DatabaseBasicOperations_DataManager.updateSchool(infoForm)
                # ========================
                # 准备函数返回值和响应与日志
                returns = {"message": "",
                           "data": ""}
                customResponse.setMessageAndData(**returns)
                logger.funcReturns = returns
        return customResponse.getResponse()

    @app.route("/Ajax/DataManager/delete_school", methods=['POST'])
    def deleteSchool():
        with CustomResponse() as customResponse:
            with Logger(funcName="DataManager.deleteSchool()") as logger:
                # ===============
                # 检查接口调用权限
                Authorization.check(rightsNeeded=({"department_id": 0, "actor": 1}, {
                                    "department_id": 3, "actor": 1}), needLogin=True)
                # ========================
                # 检查接口输入参数并记录日志
                Ajax_DataManager.deleteSchoolParamsCheck(request)
                logger.funcArgs = request.form
                # =========================================
                # 执行接口流程，并获取用户名信息以完成会话建立
                results = DatabaseBasicOperations_DataManager.deleteSchool(
                    request)
                # ========================
                # 准备函数返回值和响应与日志
                returns = {"message": "",
                           "data": results}
                customResponse.setMessageAndData(**returns)
                logger.funcReturns = returns
        return customResponse.getResponse()

    @app.route("/Ajax/DataManager/add_school", methods=['POST'])
    def addSchool():
        with CustomResponse() as customResponse:
            with Logger(funcName="DataManager.addSchool()") as logger:
                # ===============
                # 检查接口调用权限
                Authorization.check(rightsNeeded=({"department_id": 0, "actor": 1}, {
                                    "department_id": 3, "actor": 1}), needLogin=True)
                # ========================
                # 检查接口输入参数并记录日志
                Ajax_DataManager.addSchoolParamsCheck(request)
                logger.funcArgs = request.form
                # =========================================
                # 执行接口流程，并获取用户名信息以完成会话建立
                results = DatabaseBasicOperations_DataManager.addSchool(
                    request)
                # ========================
                # 准备函数返回值和响应与日志
                returns = {"message": "",
                           "data": results}
                customResponse.setMessageAndData(**returns)
                logger.funcReturns = returns
        return customResponse.getResponse()

    @app.route("/Ajax/DataManager/get_submitted_selfstudy_date", methods=['GET'])
    def getSubmittedSelfstudyDate():
        with CustomResponse() as customResponse:
            with Logger(funcName="DataManager.getSubmittedSelfstudyDate()") as logger:
                # ===============
                # 检查接口调用权限
                Authorization.check(rightsNeeded=(
                    {"department_id": 0, "actor": 0},), needLogin=True)
                # =========================================
                # 执行接口流程，并获取用户名信息以完成会话建立
                results = DatabaseBasicOperations_DataManager.getSubmittedSelfstudyDate()
                # ========================
                # 准备函数返回值和响应与日志
                returns = {"message": "",
                           "data": results}
                customResponse.setMessageAndData(**returns)
                logger.funcReturns = returns
        return customResponse.getResponse()

    @app.route("/Ajax/DataManager/get_selfstudy_classroom_details", methods=['POST'])
    def getSelfstudyClassroomDetails():
        with CustomResponse() as customResponse:
            with Logger(funcName="DataManager.getSelfstudyClassroomDetails()") as logger:
                # ===============
                # 检查接口调用权限
                Authorization.check(rightsNeeded=(
                    {"department_id": 0, "actor": 0},), needLogin=True)
                # =========================================
                # 执行接口流程，并获取用户名信息以完成会话建立
                results = DatabaseBasicOperations_DataManager.getSelfstudyClassroomDetails(
                    request)
                # ========================
                # 准备函数返回值和响应与日志
                returns = {"message": "",
                           "data": results}
                customResponse.setMessageAndData(**returns)
                logger.funcReturns = returns
        return customResponse.getResponse()

    @app.route("/Ajax/DataManager/upload_selfstudy_classroom", methods=['POST'])
    def uploadSelfstudyClassroom():
        with CustomResponse() as customResponse:
            with Logger(funcName="DataManager.uploadSelfstudyClassroom()") as logger:
                # ===============
                # 检查接口调用权限
                Authorization.check(rightsNeeded=({"department_id": 0, "actor": 1},
                                                  {"department_id": 3, "actor": 0},
                                                  {"department_id": 3, "actor": 1}), needLogin=True)
                # ========================
                # 检查接口输入参数并记录日志
                infoForm = request.get_json()
                Ajax_DataManager.uploadSelfstudyClassroomParamsCheck(infoForm)
                logger.funcArgs = request.get_json()
                # =========================================
                # 执行接口流程，并获取用户名信息以完成会话建立
                DatabaseBasicOperations_DataManager.uploadSelfstudyClassroom(
                    infoForm)
                # ========================
                # 准备函数返回值和响应与日志
                returns = {"message": "",
                           "data": ""}
                customResponse.setMessageAndData(**returns)
                logger.funcReturns = returns
        return customResponse.getResponse()

    @app.route("/Ajax/DataManager/submit_selfstudy_schedule", methods=['POST'])
    def submitSelfstudySchedule():
        with CustomResponse() as customResponse:
            with Logger(funcName="DataManager.submitSelfstudySchedule()") as logger:
                # ===============
                # 检查接口调用权限
                Authorization.check(rightsNeeded=({"department_id": 0, "actor": 1},
                                                  {"department_id": 3, "actor": 0},
                                                  {"department_id": 3, "actor": 1}), needLogin=True)
                # ========================
                # 检查接口输入参数并记录日志
                infoForm = request.get_json()
                Ajax_DataManager.submitSelfstudyScheduleParamsCheck(infoForm)
                logger.funcArgs = request.get_json()
                # =========================================
                # 执行接口流程，并获取用户名信息以完成会话建立
                DatabaseBasicOperations_DataManager.submitSelfstudySchedule(
                    infoForm)
                # ========================
                # 准备函数返回值和响应与日志
                returns = {"message": "",
                           "data": ""}
                customResponse.setMessageAndData(**returns)
                logger.funcReturns = returns
        return customResponse.getResponse()

    @app.route("/Ajax/DataManager/remove_selfstudy_schedule", methods=['POST'])
    def removeSelfstudySchedule():
        with CustomResponse() as customResponse:
            with Logger(funcName="DataManager.removeSelfstudySchedule()") as logger:
                # ===============
                # 检查接口调用权限
                Authorization.check(rightsNeeded=({"department_id": 0, "actor": 1},
                                                  {"department_id": 3, "actor": 0},
                                                  {"department_id": 3, "actor": 1}), needLogin=True)
                # ========================
                # 检查接口输入参数并记录日志
                infoForm = dict(request.form)
                Ajax_DataManager.removeSelfstudyScheduleParamsCheck(infoForm)
                logger.funcArgs = request.form
                # =========================================
                # 执行接口流程，并获取用户名信息以完成会话建立
                DatabaseBasicOperations_DataManager.removeSelfstudySchedule(
                    infoForm)
                # ========================
                # 准备函数返回值和响应与日志
                returns = {"message": "",
                           "data": ""}
                customResponse.setMessageAndData(**returns)
                logger.funcReturns = returns
        return customResponse.getResponse()

    @app.route("/Ajax/DataManager/get_schedule_on_date", methods=['POST'])
    def getScheduleOnDate():
        with CustomResponse() as customResponse:
            with Logger(funcName="DataManager.getScheduleOnDate()") as logger:
                # ===============
                # 检查接口调用权限
                Authorization.check(rightsNeeded=({"department_id": 0, "actor": 1},
                                                  {"department_id": 3, "actor": 0},
                                                  {"department_id": 3, "actor": 1}), needLogin=True)
                # ========================
                # 检查接口输入参数并记录日志
                infoForm = dict(request.form)
                Ajax_DataManager.getScheduleOnDateParamsCheck(infoForm)
                logger.funcArgs = request.form
                # =========================================
                # 执行接口流程，并获取用户名信息以完成会话建立
                results = DatabaseBasicOperations_DataManager.getScheduleOnDate(
                    infoForm)
                # ========================
                # 准备函数返回值和响应与日志
                returns = {"message": "",
                           "data": results}
                customResponse.setMessageAndData(**returns)
                logger.funcReturns = returns
        return customResponse.getResponse()

    @app.route("/Ajax/DataManager/reset_schedule_on_date", methods=['POST'])
    def resetScheduleOnDate():
        with CustomResponse() as customResponse:
            with Logger(funcName="DataManager.resetScheduleOnDate()") as logger:
                # ===============
                # 检查接口调用权限
                Authorization.check(rightsNeeded=({"department_id": 0, "actor": 1},
                                                  {"department_id": 3, "actor": 0},
                                                  {"department_id": 3, "actor": 1}), needLogin=True)
                # ========================
                # 检查接口输入参数并记录日志
                infoForm = dict(request.form)
                Ajax_DataManager.resetScheduleOnDateParamsCheck(infoForm)
                logger.funcArgs = request.form
                # =========================================
                # 执行接口流程，并获取用户名信息以完成会话建立
                results = DatabaseBasicOperations_DataManager.resetScheduleOnDate(
                    infoForm)
                # ========================
                # 准备函数返回值和响应与日志
                returns = {"message": "",
                           "data": results}
                customResponse.setMessageAndData(**returns)
                logger.funcReturns = returns
        return customResponse.getResponse()

    @app.route("/Ajax/DataManager/random_schedule_on_date", methods=['POST'])
    def randomScheduleOnDate():
        with CustomResponse() as customResponse:
            with Logger(funcName="DataManager.randomScheduleOnDate()") as logger:
                # ===============
                # 检查接口调用权限
                Authorization.check(rightsNeeded=({"department_id": 0, "actor": 1},
                                                  {"department_id": 3, "actor": 0},
                                                  {"department_id": 3, "actor": 1}), needLogin=True)
                # ========================
                # 检查接口输入参数并记录日志
                infoForm = dict(request.form)
                Ajax_DataManager.randomScheduleOnDateParamsCheck(infoForm)
                logger.funcArgs = request.form
                # =========================================
                # 执行接口流程，并获取用户名信息以完成会话建立
                results = DatabaseBasicOperations_DataManager.randomScheduleOnDate(
                    infoForm)
                # ========================
                # 准备函数返回值和响应与日志
                returns = {"message": "",
                           "data": results}
                customResponse.setMessageAndData(**returns)
                logger.funcReturns = returns
        return customResponse.getResponse()

    @app.route("/Ajax/DataManager/download_selfstudy_all_data", methods=['POST'])
    def downloadSelfstudyAllData():
        with CustomResponse() as customResponse:
            with Logger(funcName="DataManager.downloadSelfstudyAllData()") as logger:
                # ===============
                # 检查接口调用权限
                Authorization.check(rightsNeeded=({"department_id": 0, "actor": 1},
                                                  {"department_id": 3, "actor": 0},
                                                  {"department_id": 3, "actor": 1}), needLogin=True)
                # ========================
                # 检查接口输入参数并记录日志
                infoForm = dict(request.form)
                Ajax_DataManager.downloadSelfstudyAllDataParamsCheck(infoForm)
                logger.funcArgs = request.form
                # =========================================
                # 执行接口流程，并获取用户名信息以完成会话建立
                results = DatabaseBasicOperations_DataManager.downloadSelfstudyAllData(
                    infoForm)
                # ========================
                # 准备函数返回值和响应与日志
                returns = {"message": "",
                           "data": results}
                customResponse.setMessageAndData(**returns)
                logger.funcReturns = returns
        return customResponse.getResponse()

    @app.route("/Ajax/DataManager/download_empty_time_all_data", methods=['GET'])
    def downloadEmptyTimeAllData():
        with CustomResponse() as customResponse:
            with Logger(funcName="DataManager.downloadEmptyTimeAllData()") as logger:
                # ===============
                # 检查接口调用权限
                Authorization.check(rightsNeeded=({"department_id": 0, "actor": 1},
                                                  {"department_id": 3, "actor": 0},
                                                  {"department_id": 3, "actor": 1}), needLogin=True)
                # =========================================
                # 执行接口流程，并获取用户名信息以完成会话建立
                results = DatabaseBasicOperations_DataManager.downloadEmptyTimeAllData()
                # ========================
                # 准备函数返回值和响应与日志
                returns = {"message": "",
                           "data": results}
                customResponse.setMessageAndData(**returns)
                logger.funcReturns = returns
        return customResponse.getResponse()
