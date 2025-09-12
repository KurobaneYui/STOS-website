import sys
import datetime
from flask import Request
from flaskAjax.BaseComponents.CustomError import IllegalValueError


class TeamManagerCheck:
    @staticmethod
    def addBlockedParamsCheck(infoForm: dict) -> None:
        if (
            "name" not in infoForm.keys()
            or "student_id" not in infoForm.keys()
            or "reason" not in infoForm.keys()
            or "date" not in infoForm.keys()
            or "gender" not in infoForm.keys()
        ):
            raise IllegalValueError(
                "Not all required data received.",
                filename=__file__,
                line=sys._getframe().f_lineno,
            )
        infoForm["date"] = datetime.date.fromisoformat(infoForm["date"])

    @staticmethod
    def updateDepartmentParamsCheck(infoForm: dict) -> None:
        if (
            "department_id" not in infoForm.keys()
            or "old_department_id" not in infoForm.keys()
            or "department_name" not in infoForm.keys()
            or "group_leader_id" not in infoForm.keys()
            or "chazao" not in infoForm.keys()
            or "chake" not in infoForm.keys()
            or "datamanager" not in infoForm.keys()
            or "remark" not in infoForm.keys()
        ):
            raise IllegalValueError(
                "Not all required data received.",
                filename=__file__,
                line=sys._getframe().f_lineno,
            )
        if infoForm["old_department_id"] == 1 and infoForm["department_id"] != 1:
            raise IllegalValueError(
                "队长组必须配置ID为1",
                filename=__file__,
                line=sys._getframe().f_lineno,
            )
        if (
            not isinstance(infoForm["department_id"], int)
            or infoForm["department_id"] < 1
            or infoForm["department_id"] > 30
        ):
            raise IllegalValueError(
                "Invalid department ID.",
                filename=__file__,
                line=sys._getframe().f_lineno,
            )
        if len(infoForm["remark"]) > 100:
            infoForm["remark"] = infoForm["remark"][:100]

    @staticmethod
    def addDepartmentParamsCheck(infoForm: dict) -> None:
        if (
            "department_id" not in infoForm.keys()
            or "department_name" not in infoForm.keys()
            or "group_leader_id" not in infoForm.keys()
            or "chazao" not in infoForm.keys()
            or "chake" not in infoForm.keys()
            or "datamanager" not in infoForm.keys()
            or "remark" not in infoForm.keys()
        ):
            raise IllegalValueError(
                "Not all required data received.",
                filename=__file__,
                line=sys._getframe().f_lineno,
            )
        if (
            not isinstance(infoForm["department_id"], int)
            or infoForm["department_id"] < 1
            or infoForm["department_id"] > 30
        ):
            raise IllegalValueError(
                "Invalid department ID.",
                filename=__file__,
                line=sys._getframe().f_lineno,
            )
        if len(infoForm["remark"]) > 100:
            infoForm["remark"] = infoForm["remark"][:100]

    @staticmethod
    def deleteDepartmentParamsCheck(infoForm: dict) -> None:
        if "department_id" not in infoForm.keys():
            raise IllegalValueError(
                "Not all required data received.",
                filename=__file__,
                line=sys._getframe().f_lineno,
            )

    @staticmethod
    def downloadFinanceEXCELParamsCheck(infoForm: dict) -> None:
        if (
            "date" not in infoForm.keys()
            or "teacherName" not in infoForm.keys()
            or "teacherPhone" not in infoForm.keys()
            or "teacherEmail" not in infoForm.keys()
            or "teamLeaderName" not in infoForm.keys()
            or "teamLeaderPhone" not in infoForm.keys()
            or "teamLeaderEmail" not in infoForm.keys()
            or "workPlace" not in infoForm.keys()
            or "firstWage" not in infoForm.keys()
            or "secondWage" not in infoForm.keys()
            or "thirdWage" not in infoForm.keys()
            or "numForSubsidy" not in infoForm.keys()
        ):
            raise IllegalValueError(
                "Not all required data received.",
                filename=__file__,
                line=sys._getframe().f_lineno,
            )
        infoForm["date"] = datetime.datetime.strptime(infoForm["date"], "%Y-%m")
