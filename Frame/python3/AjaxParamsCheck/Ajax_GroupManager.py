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
