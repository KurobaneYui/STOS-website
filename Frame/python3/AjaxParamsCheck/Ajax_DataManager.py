import sys
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
