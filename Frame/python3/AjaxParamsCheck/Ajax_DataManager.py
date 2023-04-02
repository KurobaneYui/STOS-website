import sys
import datetime
from flask import Request
from Frame.python3.BaseComponents.CustomError import IllegalValueError, MaintenanceError


class Ajax_DataManager:
    @staticmethod
    def updateSchoolParamsCheck(infoForm: dict) -> None:
        if "name" not in infoForm.keys() or "old_school_id" not in infoForm.keys() or \
                "school_id" not in infoForm.keys() or "campus" not in infoForm.keys():
            raise IllegalValueError(
                "Not all required data received.", filename=__file__, line=sys._getframe().f_lineno)

        if not 1 <= int(infoForm['school_id']) <= 50 or not 1 <= int(infoForm['old_school_id']) <= 50:
            raise IllegalValueError(
                "学院编号目前支持1~50之间", filename=__file__, line=sys._getframe().f_lineno)

    # TODO: 实现此函数
    @staticmethod
    def deleteSchoolParamsCheck(flaskRequest: Request) -> None:
        raise MaintenanceError("Function has not been developed yet.",
                               filename=__file__, line=sys._getframe().f_lineno)

    # TODO: 实现此函数
    @staticmethod
    def addSchoolParamsCheck(flaskRequest: Request) -> None:
        raise MaintenanceError("Function has not been developed yet.",
                               filename=__file__, line=sys._getframe().f_lineno)

    @staticmethod
    def uploadSelfstudyClassroomParamsCheck(infoForm: dict) -> None:
        # "date" and "data" should be infoForm's keys
        # infoForm["date"] should be "YYYY-MM-DD"
        # infoForm["data"] is an array of dict which has unique campus+classroom_name and student_supposed must not negative
        if "date" not in infoForm.keys() or "data" not in infoForm.keys():
            raise IllegalValueError(
                "Not all required data received.", filename=__file__, line=sys._getframe().f_lineno)
        try:
            infoForm["date"] = datetime.datetime.strptime(
                infoForm["date"], "%Y-%m-%d").strftime("%Y-%m-%d")
        except:
            raise IllegalValueError(
                "Date is wrong or in wrong format. Should be YYYY-MM-DD.", filename=__file__, line=sys._getframe().f_lineno)
        campus_set = set()
        for item in infoForm["data"]:
            if "campus" not in item.keys() or "classroom_name" not in item.keys() or "school_name" not in item.keys() or \
                    "student_supposed" not in item.keys() or "remark" not in item.keys():
                raise IllegalValueError(
                    "Not all required data received.", filename=__file__, line=sys._getframe().f_lineno)
            if (item["campus"]+item["classroom_name"]) in campus_set:
                raise IllegalValueError(
                    "Classroom must be unique.", filename=__file__, line=sys._getframe().f_lineno)
            else:
                campus_set.add(item["campus"]+item["classroom_name"])
            if not 0 <= int(item["student_supposed"]) <= 300:
                raise IllegalValueError(
                    "学生人数目前支持1~300之间", filename=__file__, line=sys._getframe().f_lineno)

    @staticmethod
    def submitSelfstudyScheduleParamsCheck(infoForm: dict) -> None:
        # "date" and "data" should be infoForm's keys
        # infoForm["date"] should be "YYYY-MM-DD"
        # infoForm["data"] is an array of dict which has unique campus+classroom_name and student_supposed must not negative
        if "date" not in infoForm.keys() or "data" not in infoForm.keys() or "qingshuihe" not in infoForm['data'].keys() or "shahe" not in infoForm['data'].keys():
            raise IllegalValueError(
                "Not all required data received.", filename=__file__, line=sys._getframe().f_lineno)
        try:
            infoForm["date"] = datetime.datetime.strptime(
                infoForm["date"], "%Y-%m-%d").strftime("%Y-%m-%d")
        except:
            raise IllegalValueError(
                "Date is wrong or in wrong format. Should be YYYY-MM-DD.", filename=__file__, line=sys._getframe().f_lineno)
        # =================
        # 检查沙河部分的数据
        selfstudy_id_set = set()
        for item in infoForm["data"]["shahe"]:
            if "selfstudy_id" not in item.keys() or "student_id" not in item.keys():
                raise IllegalValueError(
                    "Not all required data received.", filename=__file__, line=sys._getframe().f_lineno)
            if item["selfstudy_id"] in selfstudy_id_set:
                raise IllegalValueError(
                    "沙河 selfstudy_id must be unique.", filename=__file__, line=sys._getframe().f_lineno)
            else:
                selfstudy_id_set.add(item["selfstudy_id"])
        # ===================
        # 检查清水河部分的数据
        for item in infoForm["data"]["qingshuihe"]:
            if "selfstudy_id" not in item.keys() or "student_id" not in item.keys():
                raise IllegalValueError(
                    "Not all required data received.", filename=__file__, line=sys._getframe().f_lineno)
            if item["selfstudy_id"] in selfstudy_id_set:
                raise IllegalValueError(
                    "清水河 selfstudy_id must be unique.", filename=__file__, line=sys._getframe().f_lineno)
            else:
                selfstudy_id_set.add(item["selfstudy_id"])

    @staticmethod
    def removeSelfstudyScheduleParamsCheck(infoForm: dict) -> None:
        if "date" not in infoForm.keys():
            raise IllegalValueError(
                "Date is required.", filename=__file__, line=sys._getframe().f_lineno)

        try:
            infoForm["date"] = datetime.datetime.strptime(
                infoForm["date"], "%Y-%m-%d").strftime("%Y-%m-%d")
        except:
            raise IllegalValueError(
                "Date is wrong or in wrong format. Should be YYYY-MM-DD.", filename=__file__, line=sys._getframe().f_lineno)

    @staticmethod
    def getScheduleOnDateParamsCheck(infoForm: dict) -> None:
        if "date" not in infoForm.keys():
            raise IllegalValueError(
                "Date is required.", filename=__file__, line=sys._getframe().f_lineno)

        try:
            infoForm["date"] = datetime.datetime.strptime(
                infoForm["date"], "%Y-%m-%d").strftime("%Y-%m-%d")
        except:
            raise IllegalValueError(
                "Date is wrong or in wrong format. Should be YYYY-MM-DD.", filename=__file__, line=sys._getframe().f_lineno)

    @staticmethod
    def resetScheduleOnDateParamsCheck(infoForm: dict) -> None:
        if "date" not in infoForm.keys()or "campus" not in infoForm.keys():
            raise IllegalValueError(
                "Date and campus is required.", filename=__file__, line=sys._getframe().f_lineno)
            
        if infoForm['campus'] not in ['清水河','沙河']:
            raise IllegalValueError(
                "Campus is not right, should be one of '清水河' and '沙河'.", filename=__file__, line=sys._getframe().f_lineno)

        try:
            infoForm["date"] = datetime.datetime.strptime(
                infoForm["date"], "%Y-%m-%d").strftime("%Y-%m-%d")
        except:
            raise IllegalValueError(
                "Date is wrong or in wrong format. Should be YYYY-MM-DD.", filename=__file__, line=sys._getframe().f_lineno)

    @staticmethod
    def randomScheduleOnDateParamsCheck(infoForm: dict) -> None:
        if "date" not in infoForm.keys()or "campus" not in infoForm.keys():
            raise IllegalValueError(
                "Date and campus is required.", filename=__file__, line=sys._getframe().f_lineno)
            
        if infoForm['campus'] not in ['清水河','沙河']:
            raise IllegalValueError(
                "Campus is not right, should be one of '清水河' and '沙河'.", filename=__file__, line=sys._getframe().f_lineno)

        try:
            infoForm["date"] = datetime.datetime.strptime(
                infoForm["date"], "%Y-%m-%d").strftime("%Y-%m-%d")
        except:
            raise IllegalValueError(
                "Date is wrong or in wrong format. Should be YYYY-MM-DD.", filename=__file__, line=sys._getframe().f_lineno)
