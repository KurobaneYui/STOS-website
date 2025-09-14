import sys
from flask import Request
from flaskAjax.BaseComponents.CustomError import IllegalValueError


class GroupManagerCheck:
    @staticmethod
    def searchMemberParamsCheck(infoForm: dict) -> None:
        if "student_ids" not in infoForm.keys():
            raise IllegalValueError(
                "Not all required data received.",
                filename=__file__,
                line=sys._getframe().f_lineno,
            )

    @staticmethod
    def addMemberParamsCheck(infoForm: dict) -> None:
        if "student_id" not in infoForm.keys() or "group_id" not in infoForm.keys():
            raise IllegalValueError(
                "Not all required data received.",
                filename=__file__,
                line=sys._getframe().f_lineno,
            )

    @staticmethod
    def removeMemberParamsCheck(infoForm: dict) -> None:
        if "student_id" not in infoForm.keys() or "group_id" not in infoForm.keys():
            raise IllegalValueError(
                "Not all required data received.",
                filename=__file__,
                line=sys._getframe().f_lineno,
            )

    @staticmethod
    def submitSelfstudyRecordRecheckParamsCheck(infoForm: dict) -> None:
        # {'absent_list': [{'student_id': '12', 'student_name': '的'},
        #                 {'student_id': '12', 'student_name': '到的'}],
        # 'leave_list': [{'reason': '-', 'student_id': '123', 'student_name': '是'},
        #                 {'reason': '=', 'student_id': '122', 'student_name': '啊'}],
        # 'record': {'absentee': 6,
        #             'early_leave': 4,
        #             'first_count': 1,
        #             'late': 2,
        #             'leave': 5,
        #             'remarks': '啊',
        #             'second_count': 3},
        # 'task_id': 4}
        if (
            "task_id" not in infoForm.keys()
            or "record" not in infoForm.keys()
            or "leave_list" not in infoForm.keys()
            or "absent_list" not in infoForm.keys()
            or "first_count" not in infoForm["record"].keys()
            or "second_count" not in infoForm["record"].keys()
            or "early_leave" not in infoForm["record"].keys()
            or "leave" not in infoForm["record"].keys()
            or "late" not in infoForm["record"].keys()
            or "absentee" not in infoForm["record"].keys()
            or "remarks" not in infoForm["record"].keys()
        ):
            raise IllegalValueError(
                "Not all data are provided.",
                filename=__file__,
                line=sys._getframe().f_lineno,
            )

        for one_student in infoForm["absent_list"]:
            if one_student["student_name"] == "" or one_student["student_id"] == "":
                raise IllegalValueError(
                    "Student name or ID in absent list is empty.",
                    filename=__file__,
                    line=sys._getframe().f_lineno,
                )
            if any(char.isdigit() for char in one_student["student_name"]) or not any(
                char.isalpha() and char.isalnum()
                for char in one_student["student_name"]
            ):
                raise IllegalValueError(
                    "Student name in absent list is wrong.",
                    filename=__file__,
                    line=sys._getframe().f_lineno,
                )
            if not (
                one_student["student_id"].isalnum()
                and one_student["student_id"].isascii()
            ):
                raise IllegalValueError(
                    "Student ID in absent list is wrong.",
                    filename=__file__,
                    line=sys._getframe().f_lineno,
                )

        for one_student in infoForm["leave_list"]:
            if one_student["student_name"] == "" or one_student["student_id"] == "":
                raise IllegalValueError(
                    "Student name or ID in ask for leave list is empty.",
                    filename=__file__,
                    line=sys._getframe().f_lineno,
                )
            if any(char.isdigit() for char in one_student["student_name"]) or not any(
                char.isalpha() and char.isalnum()
                for char in one_student["student_name"]
            ):
                raise IllegalValueError(
                    "Student name in ask for leave list is wrong.",
                    filename=__file__,
                    line=sys._getframe().f_lineno,
                )
            if not (
                one_student["student_id"].isalnum()
                and one_student["student_id"].isascii()
            ):
                raise IllegalValueError(
                    "Student ID in ask for leave list is wrong.",
                    filename=__file__,
                    line=sys._getframe().f_lineno,
                )

        if (
            infoForm["record"]["first_count"] < 0
            or infoForm["record"]["second_count"] < 0
            or infoForm["record"]["late"] < 0
            or infoForm["record"]["leave"] < 0
            or infoForm["record"]["early_leave"] < 0
            or infoForm["record"]["absentee"] < 0
        ):
            raise IllegalValueError(
                "Record is wrong or not nonnegative integer.",
                filename=__file__,
                line=sys._getframe().f_lineno,
            )

    # @staticmethod
    # def getGroupCoursesCheckDataParamsCheck(infoForm: dict) -> None:
    #     if "group_id_list" not in infoForm.keys():
    #         raise IllegalValueError(
    #             "Not all data are provided.",
    #             filename=__file__,
    #             line=sys._getframe().f_lineno,
    #         )

    # @staticmethod
    # def submitCoursesRecordRecheckParamsCheck(infoForm: dict) -> None:
    #     if (
    #         "course_id" not in infoForm.keys()
    #         or "coursecheckdata_id" not in infoForm.keys()
    #         or "rechecked" not in infoForm.keys()
    #         or "recheck_remark" not in infoForm.keys()
    #     ):
    #         raise IllegalValueError(
    #             "Not all data are provided.",
    #             filename=__file__,
    #             line=sys._getframe().f_lineno,
    #         )

    #     try:
    #         infoForm["course_id"] = int(infoForm["course_id"])
    #         infoForm["coursecheckdata_id"] = int(infoForm["coursecheckdata_id"])
    #         infoForm["rechecked"] = infoForm["rechecked"] == "true"
    #         assert infoForm["course_id"] >= 0
    #         assert infoForm["coursecheckdata_id"] >= 0
    #     except:
    #         raise IllegalValueError(
    #             "Provided data is wrong or not nonnegative integer.",
    #             filename=__file__,
    #             line=sys._getframe().f_lineno,
    #         )

    @staticmethod
    def setMemberEmptyTableParamsCheck(infoForm: dict) -> None:
        if (
            "student_id" not in infoForm.keys()
            or "weekName" not in infoForm.keys()
            or "timePeriodOrder" not in infoForm.keys()
            or "evenOrNot" not in infoForm.keys()
            or "emptyOrNot" not in infoForm.keys()
        ):
            raise IllegalValueError(
                "Not all data are provided.",
                filename=__file__,
                line=sys._getframe().f_lineno,
            )

        try:
            assert 0 <= infoForm["timePeriodOrder"] <= 4
            assert infoForm["weekName"] in [
                "mon",
                "tue",
                "wed",
                "thu",
                "fri",
                "sat",
                "sun",
            ]
        except Exception:
            raise IllegalValueError(
                "Provided data is illegal.",
                filename=__file__,
                line=sys._getframe().f_lineno,
            )
