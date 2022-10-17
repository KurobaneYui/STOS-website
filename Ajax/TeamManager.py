import flask
from flask import session, request
from Frame.python3.Authorization import Auth
from Frame.python3.Logger import Logger
from Frame.python3.DatabaseConnector import DatabaseConnector
from Frame.python3.CustomResponsePackage import CustomResponsePackage, IllegalValueError
import sys
import Ajax.function_departmentManager as function_departmentManager


def TeamManager(app : flask.Flask) -> None:
    @app.route("/Ajax/TeamManager/get_department", methods=['GET'])
    @CustomResponsePackage
    @Logger
    @Auth(({"department_id":1,"actor":"11"},))
    def get_department():
        connection = DatabaseConnector()
        connection.startCursor()
        
        DBAffectRows = connection.execute(
            sql="SELECT Department.department_id as department_id, Department.name as department_name, \
                job_available, remark, MemberBasic.student_id as student_id, MemberBasic.name as student_name \
                FROM Department \
                LEFT JOIN (SELECT * FROM Authority WHERE actor LIKE '1%' OR actor IS NULL) AS Authority ON Department.department_id=Authority.department_id \
                LEFT JOIN MemberBasic ON Authority.student_id=MemberBasic.student_id;")
        returns = connection.fetchall()
        return {"warning":"", "message":"", "data":returns}
    
    
    @app.route("/Ajax/TeamManager/update_department", methods=['POST'])
    @CustomResponsePackage
    @Logger
    @Auth(({"department_id":1,"actor":"11"},))
    def update_department():
        if "max_num" not in request.form.keys() or "department_id" not in request.form.keys() \
            or "group_leader_id" not in request.form.keys() or "remark" not in request.form.keys():
            raise IllegalValueError("Not all required data received.", filename=__file__, line=sys._getframe().f_lineno)        
        
        formDict = dict(request.form)
        if not 0 <= int(formDict['max_num']) <= 50:
            raise IllegalValueError("人数上限应在0~50之间", filename=__file__, line=sys._getframe().f_lineno)
        if len(formDict['remark']) > 100:
            formDict['remark'] = formDict['remark'][:100]
        
        returns = function_departmentManager.departmentManager(formDict=formDict)
        return {"warning":"", "message":returns["message"], "data":returns["data"]}