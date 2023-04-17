import sys
from flask import Request
from Frame.python3.BaseComponents.CustomError import IllegalValueError


class Ajax_GroupManager:
    @staticmethod
    def searchMemberParamsCheck(flaskRequest: Request) -> None:
        if "student_ids" not in flaskRequest.form.keys():
            raise IllegalValueError(
                "Not all required data received.", filename=__file__, line=sys._getframe().f_lineno)

    @staticmethod
    def addMemberParamsCheck(flaskRequest: Request) -> None:
        if "student_id" not in flaskRequest.form.keys() or "group_id" not in flaskRequest.form.keys():
            raise IllegalValueError(
                "Not all required data received.", filename=__file__, line=sys._getframe().f_lineno)

    @staticmethod
    def removeMemberParamsCheck(flaskRequest: Request) -> None:
        if "student_id" not in flaskRequest.form.keys() or "group_id" not in flaskRequest.form.keys():
            raise IllegalValueError(
                "Not all required data received.", filename=__file__, line=sys._getframe().f_lineno)

    @staticmethod
    def submitSelfstudyRecordRecheckParamsCheck(infoForm: dict) -> None:
        if "selfstudy_id" not in infoForm.keys() or "selfstudycheckdata_id" not in infoForm.keys() or "selfstudycheckabsent_id" not in infoForm.keys() \
                or "rechecked" not in infoForm.keys() or "recheck_remark" not in infoForm.keys():
            raise IllegalValueError(
                "Not all data are provided.", filename=__file__, line=sys._getframe().f_lineno)

        try:
            infoForm["selfstudy_id"] = int(infoForm["selfstudy_id"])
            infoForm["selfstudycheckdata_id"] = int(
                infoForm["selfstudycheckdata_id"])
            infoForm["selfstudycheckabsent_id"] = int(
                infoForm["selfstudycheckabsent_id"])
            infoForm["rechecked"] = infoForm["rechecked"] == 'true'
            assert infoForm["selfstudy_id"] >= 0
            assert infoForm["selfstudycheckdata_id"] >= 0
            assert infoForm["selfstudycheckabsent_id"] >= 0
        except:
            raise IllegalValueError(
                "Provided data is wrong or not nonnegative integer.", filename=__file__, line=sys._getframe().f_lineno)

    @staticmethod
    def getGroupCoursesCheckDataParamsCheck(infoForm: dict) -> None:
        if "group_id_list" not in infoForm.keys():
            raise IllegalValueError(
                "Not all data are provided.", filename=__file__, line=sys._getframe().f_lineno)

    @staticmethod
    def submitCoursesRecordRecheckParamsCheck(infoForm: dict) -> None:
        if "course_id" not in infoForm.keys() or "coursecheckdata_id" not in infoForm.keys() \
                or "rechecked" not in infoForm.keys() or "recheck_remark" not in infoForm.keys():
            raise IllegalValueError(
                "Not all data are provided.", filename=__file__, line=sys._getframe().f_lineno)

        try:
            infoForm["course_id"] = int(infoForm["course_id"])
            infoForm["coursecheckdata_id"] = int(
                infoForm["coursecheckdata_id"])
            infoForm["rechecked"] = infoForm["rechecked"] == 'true'
            assert infoForm["course_id"] >= 0
            assert infoForm["coursecheckdata_id"] >= 0
        except:
            raise IllegalValueError(
                "Provided data is wrong or not nonnegative integer.", filename=__file__, line=sys._getframe().f_lineno)

    @staticmethod
    def setMemberEmptyTableParamsCheck(infoForm: dict) -> None:
        if "student_id" not in infoForm.keys() or "weekName" not in infoForm.keys() or "timePeriodOrder" not in infoForm.keys() \
                or "evenOrNot" not in infoForm.keys() or "emptyOrNot" not in infoForm.keys():
            raise IllegalValueError(
                "Not all data are provided.", filename=__file__, line=sys._getframe().f_lineno)

        try:
            infoForm["timePeriodOrder"] = int(infoForm["timePeriodOrder"])
            infoForm["evenOrNot"] = infoForm["evenOrNot"] == 'true'
            infoForm["emptyOrNot"] = infoForm["emptyOrNot"] == 'true'
            assert 0 <= infoForm["timePeriodOrder"] <= 4
            assert infoForm["weekName"] in [
                "mon", "tue", "wed", "thu", "fri", "sat", "sun"]
        except:
            raise IllegalValueError(
                "Provided data is illegal.", filename=__file__, line=sys._getframe().f_lineno)
