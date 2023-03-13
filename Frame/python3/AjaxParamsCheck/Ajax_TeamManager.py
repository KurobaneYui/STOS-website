import sys
from flask import Request
from Frame.python3.BaseComponents.CustomError import IllegalValueError


class Ajax_TeamManager:
    @staticmethod
    def updateDepartmentParamsCheck(infoForm: dict) -> None:
        if "max_num" not in infoForm.keys() or "department_id" not in infoForm.keys() \
                or "group_leader_id" not in infoForm.keys() or "remark" not in infoForm.keys():
            raise IllegalValueError(
                "Not all required data received.", filename=__file__, line=sys._getframe().f_lineno)

        if not 0 <= int(infoForm['max_num']) <= 50:
            raise IllegalValueError(
                "人数上限应在0~50之间", filename=__file__, line=sys._getframe().f_lineno)
        if len(infoForm['remark']) > 100:
            infoForm['remark'] = infoForm['remark'][:100]

    # TODO: Check parameters !!!
    @staticmethod
    def downloadFinanceEXCELParamsCheck(infoForm: dict) -> None:
        infoForm
        return
