import re
import sys
import datetime
from flask import Request
from flaskAjax.BaseComponents.CustomError import IllegalValueError, MaintenanceError


def encode_infoForm(infoForm: dict) -> str:
    # 校区编码
    campus_code = 1 if infoForm["campus"] == "清水河" else 2

    # 楼宇编码
    if infoForm["building"] in ["品学楼", "一教"]:
        building_code = 1
    else:
        building_code = 2

    # 区域编码
    area_map = {"A": 1, "B": 2, "C": 3, "-": 0}
    area_code = area_map.get(infoForm["area"], 0)

    # 房间号编码
    room = infoForm["room_number"]
    room_digits = "".join([c for c in room if c.isdigit()])[:3]
    if len(room_digits) < 3:
        room_digits = room_digits.ljust(3, "0")  # 不足3位补0

    # 检查是否有字母
    room_alpha = [c for c in room if c.isalpha()]
    if room_alpha:
        letter = room_alpha[0].upper()
        letter_num = ord(letter) - ord("A") + 1
    else:
        letter_num = 0

    # 拼接编码字符串
    return f"{campus_code}{building_code}{area_code}{room_digits}{letter_num}"


def is_id_valid(infoForm: dict) -> bool:
    return infoForm["id"] == encode_infoForm(infoForm)


class DataManagerCheck:
    @staticmethod
    def updateSchoolParamsCheck(infoForm: dict) -> None:
        if (
            "name" not in infoForm.keys()
            or "old_school_id" not in infoForm.keys()
            or "school_id" not in infoForm.keys()
        ):
            raise IllegalValueError(
                "Not all required data received.",
                filename=__file__,
                line=sys._getframe().f_lineno,
            )

        if (
            not 1 <= int(infoForm["school_id"]) <= 50
            or not 1 <= int(infoForm["old_school_id"]) <= 50
        ):
            raise IllegalValueError(
                "学院编号目前支持1~50之间",
                filename=__file__,
                line=sys._getframe().f_lineno,
            )

    @staticmethod
    def deleteSchoolParamsCheck(infoForm: dict) -> None:
        if "school_id" not in infoForm.keys():
            raise IllegalValueError(
                "Not all required data received.",
                filename=__file__,
                line=sys._getframe().f_lineno,
            )

    @staticmethod
    def addSchoolParamsCheck(infoForm: dict) -> None:
        if "name" not in infoForm.keys() or "school_id" not in infoForm.keys():
            raise IllegalValueError(
                "Not all required data received.",
                filename=__file__,
                line=sys._getframe().f_lineno,
            )

    @staticmethod
    def deleteClassroomParamsCheck(infoForm: dict) -> None:
        if "id" not in infoForm.keys():
            raise IllegalValueError(
                "Not all required data received.",
                filename=__file__,
                line=sys._getframe().f_lineno,
            )

    @staticmethod
    def addClassroomParamsCheck(
        infoForm: dict,
    ) -> None:
        if (
            "id" not in infoForm.keys()
            or "capacity" not in infoForm.keys()
            or "campus" not in infoForm.keys()
            or "building" not in infoForm.keys()
            or "area" not in infoForm.keys()
            or "room_number" not in infoForm.keys()
        ):
            raise IllegalValueError(
                "Not all required data received.",
                filename=__file__,
                line=sys._getframe().f_lineno,
            )
        if infoForm["capacity"] < 0 or infoForm["capacity"] > 500:
            raise IllegalValueError(
                "教室容量目前支持0~500之间",
                filename=__file__,
                line=sys._getframe().f_lineno,
            )
        if infoForm["campus"] not in ["清水河", "沙河"]:
            raise IllegalValueError(
                "校区非清水河/沙河",
                filename=__file__,
                line=sys._getframe().f_lineno,
            )
        if infoForm["building"] not in ["品学楼", "立人楼", "一教", "二教"]:
            raise IllegalValueError(
                "教学楼不属于品学楼/立人楼/一教/二教",
                filename=__file__,
                line=sys._getframe().f_lineno,
            )
        if infoForm["area"] not in ["A", "B", "C", "-"]:
            raise IllegalValueError(
                "区域暂支持：A/B/C/-",
                filename=__file__,
                line=sys._getframe().f_lineno,
            )
        if not re.match(r"^\d{3}[A-Za-z]?$", infoForm["room_number"]):
            raise IllegalValueError(
                "教室编号暂支持：3位数字+可选1位英文字母",
                filename=__file__,
                line=sys._getframe().f_lineno,
            )
        if is_id_valid(infoForm) is False:
            raise IllegalValueError(
                "教室ID与其他信息不匹配，请检查",
                filename=__file__,
                line=sys._getframe().f_lineno,
            )

    @staticmethod
    def updateClassroomParamsCheck(
        infoForm: dict,
    ) -> None:
        if (
            "id" not in infoForm.keys()
            or "old_id" not in infoForm.keys()
            or "capacity" not in infoForm.keys()
            or "campus" not in infoForm.keys()
            or "building" not in infoForm.keys()
            or "area" not in infoForm.keys()
            or "room_number" not in infoForm.keys()
        ):
            raise IllegalValueError(
                "Not all required data received.",
                filename=__file__,
                line=sys._getframe().f_lineno,
            )
        if infoForm["capacity"] < 0 or infoForm["capacity"] > 500:
            raise IllegalValueError(
                "教室容量目前支持0~500之间",
                filename=__file__,
                line=sys._getframe().f_lineno,
            )
        if infoForm["campus"] not in ["清水河", "沙河"]:
            raise IllegalValueError(
                "校区非清水河/沙河",
                filename=__file__,
                line=sys._getframe().f_lineno,
            )
        if infoForm["building"] not in ["品学楼", "立人楼", "一教", "二教"]:
            raise IllegalValueError(
                "教学楼不属于品学楼/立人楼/一教/二教",
                filename=__file__,
                line=sys._getframe().f_lineno,
            )
        if infoForm["area"] not in ["A", "B", "C", "-"]:
            raise IllegalValueError(
                "区域暂支持：A/B/C/-",
                filename=__file__,
                line=sys._getframe().f_lineno,
            )
        if not re.match(r"^\d{3}[A-Za-z]?$", infoForm["room_number"]):
            raise IllegalValueError(
                "教室编号暂支持：3位数字+可选1位英文字母",
                filename=__file__,
                line=sys._getframe().f_lineno,
            )
        if is_id_valid(infoForm) is False:
            raise IllegalValueError(
                "教室ID与其他信息不匹配，请检查",
                filename=__file__,
                line=sys._getframe().f_lineno,
            )

    # @staticmethod
    # def uploadSelfstudyClassroomParamsCheck(infoForm: dict) -> None:
    #     # "date" and "data" should be infoForm's keys
    #     # infoForm["date"] should be "YYYY-MM-DD"
    #     # infoForm["data"] is an array of dict which has unique campus+classroom_name and student_supposed must not negative
    #     if "date" not in infoForm.keys() or "data" not in infoForm.keys():
    #         raise IllegalValueError(
    #             "Not all required data received.",
    #             filename=__file__,
    #             line=sys._getframe().f_lineno,
    #         )
    #     try:
    #         infoForm["date"] = datetime.datetime.strptime(
    #             infoForm["date"], "%Y-%m-%d"
    #         ).strftime("%Y-%m-%d")
    #     except:
    #         raise IllegalValueError(
    #             "Date is wrong or in wrong format. Should be YYYY-MM-DD.",
    #             filename=__file__,
    #             line=sys._getframe().f_lineno,
    #         )
    #     campus_set = set()
    #     for item in infoForm["data"]:
    #         if (
    #             "campus" not in item.keys()
    #             or "classroom_name" not in item.keys()
    #             or "school_name" not in item.keys()
    #             or "student_supposed" not in item.keys()
    #             or "remark" not in item.keys()
    #         ):
    #             raise IllegalValueError(
    #                 "Not all required data received.",
    #                 filename=__file__,
    #                 line=sys._getframe().f_lineno,
    #             )
    #         if (item["campus"] + item["classroom_name"]) in campus_set:
    #             raise IllegalValueError(
    #                 "Classroom must be unique.",
    #                 filename=__file__,
    #                 line=sys._getframe().f_lineno,
    #             )
    #         else:
    #             campus_set.add(item["campus"] + item["classroom_name"])
    #         if not 0 <= int(item["student_supposed"]) <= 300:
    #             raise IllegalValueError(
    #                 "学生人数目前支持1~300之间",
    #                 filename=__file__,
    #                 line=sys._getframe().f_lineno,
    #             )

    # @staticmethod
    # def submitSelfstudyScheduleParamsCheck(infoForm: dict) -> None:
    #     # "date" and "data" should be infoForm's keys
    #     # infoForm["date"] should be "YYYY-MM-DD"
    #     # infoForm["data"] is an array of dict which has unique campus+classroom_name and student_supposed must not negative
    #     if (
    #         "date" not in infoForm.keys()
    #         or "data" not in infoForm.keys()
    #         or "qingshuihe" not in infoForm["data"].keys()
    #         or "shahe" not in infoForm["data"].keys()
    #     ):
    #         raise IllegalValueError(
    #             "Not all required data received.",
    #             filename=__file__,
    #             line=sys._getframe().f_lineno,
    #         )
    #     try:
    #         infoForm["date"] = datetime.datetime.strptime(
    #             infoForm["date"], "%Y-%m-%d"
    #         ).strftime("%Y-%m-%d")
    #     except:
    #         raise IllegalValueError(
    #             "Date is wrong or in wrong format. Should be YYYY-MM-DD.",
    #             filename=__file__,
    #             line=sys._getframe().f_lineno,
    #         )
    #     # =================
    #     # 检查沙河部分的数据
    #     selfstudy_id_set = set()
    #     for item in infoForm["data"]["shahe"]:
    #         if "selfstudy_id" not in item.keys() or "student_id" not in item.keys():
    #             raise IllegalValueError(
    #                 "Not all required data received.",
    #                 filename=__file__,
    #                 line=sys._getframe().f_lineno,
    #             )
    #         if item["selfstudy_id"] in selfstudy_id_set:
    #             raise IllegalValueError(
    #                 "沙河 selfstudy_id must be unique.",
    #                 filename=__file__,
    #                 line=sys._getframe().f_lineno,
    #             )
    #         else:
    #             selfstudy_id_set.add(item["selfstudy_id"])
    #     # ===================
    #     # 检查清水河部分的数据
    #     for item in infoForm["data"]["qingshuihe"]:
    #         if "selfstudy_id" not in item.keys() or "student_id" not in item.keys():
    #             raise IllegalValueError(
    #                 "Not all required data received.",
    #                 filename=__file__,
    #                 line=sys._getframe().f_lineno,
    #             )
    #         if item["selfstudy_id"] in selfstudy_id_set:
    #             raise IllegalValueError(
    #                 "清水河 selfstudy_id must be unique.",
    #                 filename=__file__,
    #                 line=sys._getframe().f_lineno,
    #             )
    #         else:
    #             selfstudy_id_set.add(item["selfstudy_id"])

    # @staticmethod
    # def removeSelfstudyScheduleParamsCheck(infoForm: dict) -> None:
    #     if "date" not in infoForm.keys():
    #         raise IllegalValueError(
    #             "Date is required.", filename=__file__, line=sys._getframe().f_lineno
    #         )

    #     try:
    #         infoForm["date"] = datetime.datetime.strptime(
    #             infoForm["date"], "%Y-%m-%d"
    #         ).strftime("%Y-%m-%d")
    #     except:
    #         raise IllegalValueError(
    #             "Date is wrong or in wrong format. Should be YYYY-MM-DD.",
    #             filename=__file__,
    #             line=sys._getframe().f_lineno,
    #         )

    # @staticmethod
    # def getScheduleOnDateParamsCheck(infoForm: dict) -> None:
    #     if "date" not in infoForm.keys():
    #         raise IllegalValueError(
    #             "Date is required.", filename=__file__, line=sys._getframe().f_lineno
    #         )

    #     try:
    #         infoForm["date"] = datetime.datetime.strptime(
    #             infoForm["date"], "%Y-%m-%d"
    #         ).strftime("%Y-%m-%d")
    #     except:
    #         raise IllegalValueError(
    #             "Date is wrong or in wrong format. Should be YYYY-MM-DD.",
    #             filename=__file__,
    #             line=sys._getframe().f_lineno,
    #         )

    # @staticmethod
    # def resetScheduleOnDateParamsCheck(infoForm: dict) -> None:
    #     if "date" not in infoForm.keys() or "campus" not in infoForm.keys():
    #         raise IllegalValueError(
    #             "Date and campus is required.",
    #             filename=__file__,
    #             line=sys._getframe().f_lineno,
    #         )

    #     if infoForm["campus"] not in ["清水河", "沙河"]:
    #         raise IllegalValueError(
    #             "Campus is not right, should be one of '清水河' and '沙河'.",
    #             filename=__file__,
    #             line=sys._getframe().f_lineno,
    #         )

    #     try:
    #         infoForm["date"] = datetime.datetime.strptime(
    #             infoForm["date"], "%Y-%m-%d"
    #         ).strftime("%Y-%m-%d")
    #     except:
    #         raise IllegalValueError(
    #             "Date is wrong or in wrong format. Should be YYYY-MM-DD.",
    #             filename=__file__,
    #             line=sys._getframe().f_lineno,
    #         )

    # @staticmethod
    # def randomScheduleOnDateParamsCheck(infoForm: dict) -> None:
    #     if "date" not in infoForm.keys() or "campus" not in infoForm.keys():
    #         raise IllegalValueError(
    #             "Date and campus is required.",
    #             filename=__file__,
    #             line=sys._getframe().f_lineno,
    #         )

    #     if infoForm["campus"] not in ["清水河", "沙河"]:
    #         raise IllegalValueError(
    #             "Campus is not right, should be one of '清水河' and '沙河'.",
    #             filename=__file__,
    #             line=sys._getframe().f_lineno,
    #         )

    #     try:
    #         infoForm["date"] = datetime.datetime.strptime(
    #             infoForm["date"], "%Y-%m-%d"
    #         ).strftime("%Y-%m-%d")
    #     except:
    #         raise IllegalValueError(
    #             "Date is wrong or in wrong format. Should be YYYY-MM-DD.",
    #             filename=__file__,
    #             line=sys._getframe().f_lineno,
    #         )

    # @staticmethod
    # def downloadSelfstudyAllDataParamsCheck(infoForm: dict) -> None:
    #     if "startDate" not in infoForm.keys() or "endDate" not in infoForm.keys():
    #         raise IllegalValueError(
    #             "startDate and endDate is required.",
    #             filename=__file__,
    #             line=sys._getframe().f_lineno,
    #         )

    #     try:
    #         infoForm["startDate"] = datetime.datetime.strptime(
    #             infoForm["startDate"], "%Y-%m-%d"
    #         ).strftime("%Y-%m-%d")
    #         infoForm["endDate"] = datetime.datetime.strptime(
    #             infoForm["endDate"], "%Y-%m-%d"
    #         ).strftime("%Y-%m-%d")
    #     except:
    #         raise IllegalValueError(
    #             "Date is wrong or in wrong format. Should be YYYY-MM-DD.",
    #             filename=__file__,
    #             line=sys._getframe().f_lineno,
    #         )

    # @staticmethod
    # def downloadCoursesAllDataParamsCheck(infoForm: dict) -> None:
    #     if "startDate" not in infoForm.keys() or "endDate" not in infoForm.keys():
    #         raise IllegalValueError(
    #             "startDate and endDate is required.",
    #             filename=__file__,
    #             line=sys._getframe().f_lineno,
    #         )

    #     try:
    #         infoForm["startDate"] = datetime.datetime.strptime(
    #             infoForm["startDate"], "%Y-%m-%d"
    #         ).strftime("%Y-%m-%d")
    #         infoForm["endDate"] = datetime.datetime.strptime(
    #             infoForm["endDate"], "%Y-%m-%d"
    #         ).strftime("%Y-%m-%d")
    #     except:
    #         raise IllegalValueError(
    #             "Date is wrong or in wrong format. Should be YYYY-MM-DD.",
    #             filename=__file__,
    #             line=sys._getframe().f_lineno,
    #         )
