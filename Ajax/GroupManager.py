import flask
from flask import session, request
from Frame.python3.Authorization import Auth
from Frame.python3.Logger import Logger
from Frame.python3.DatabaseConnector import DatabaseConnector
from Frame.python3.CustomResponsePackage import CustomResponsePackage, IllegalValueError, MaintenanceError
import sys
import Ajax.function_departmentManager as function_departmentManager
import Ajax.function_groupManager as function_groupManager


def GroupManager(app : flask.Flask) -> None:
    @app.route("/Ajax/GroupManager/get_all_groups_members", methods=['GET'])
    @CustomResponsePackage
    @Logger
    @Auth(({"department_id":1,"actor":None},{"department_id":None,"actor":"10"}))
    def get_all_groups_members():
        results = function_groupManager.get_all_groups_members()
        return {"warning":"", "message":results["message"], "data":results["data"]}
    
    
    @app.route("/Ajax/GroupManager/search_member", methods=['POST'])
    @CustomResponsePackage
    @Logger
    @Auth(({"department_id":1,"actor":None},{"department_id":None,"actor":"10"}))
    def search_member():
        if "student_ids" not in request.form.keys():
            raise IllegalValueError("Not all required data received.", filename=__file__, line=sys._getframe().f_lineno)
        
        results = function_groupManager.search_member()
        return {"warning":"", "message":results["message"], "data":results["data"]}
    
    
    @app.route("/Ajax/GroupManager/add_member", methods=['POST'])
    @CustomResponsePackage
    @Logger
    @Auth(({"department_id":1,"actor":None},{"department_id":None,"actor":"10"}))
    def add_member():
        if "student_id" not in request.form.keys() or "group_id" not in request.form.keys():
            raise IllegalValueError("Not all required data received.", filename=__file__, line=sys._getframe().f_lineno)
        
        results = function_groupManager.add_member_functions[int(request.form["group_id"])](request.form["student_id"])
        
        return {"warning":"", "message":results["message"], "data":results["data"]}
    
    
    @app.route("/Ajax/GroupManager/remove_member", methods=['POST'])
    @CustomResponsePackage
    @Logger
    @Auth(({"department_id":1,"actor":None},{"department_id":None,"actor":"10"}))
    def remove_member():
        if "student_id" not in request.form.keys() or "group_id" not in request.form.keys():
            raise IllegalValueError("Not all required data received.", filename=__file__, line=sys._getframe().f_lineno)
        raise MaintenanceError("Not all required data received.", filename=__file__, line=sys._getframe().f_lineno)
        results = function_groupManager.remove_member_functions[int(request.form["group_id"])](request.form["student_id"])
        
        return {"warning":"", "message":results["message"], "data":results["data"]}