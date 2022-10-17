import flask
from flask import session, request
from Frame.python3.Authorization import Auth
from Frame.python3.Logger import Logger
from Frame.python3.DatabaseConnector import DatabaseConnector
from Frame.python3.CustomResponsePackage import CustomResponsePackage, IllegalValueError
import sys
import Ajax.function_departmentManager as function_departmentManager


def DataManager(app : flask.Flask) -> None:
    @app.route("/Ajax/DataManager/get_school", methods=['GET'])
    @CustomResponsePackage
    @Logger
    @Auth(({"department_id":3,"actor":"10"},{"department_id":1,"actor":None}))
    def get_school():
        connection = DatabaseConnector()
        connection.startCursor()
        DBAffectRows = connection.execute("SELECT school_id,name,campus FROM `School`;")
        returns = connection.fetchall()
        return {"warning":"", "message":"", "data":returns}
    
    
    # @app.route("/Ajax/DataManager/update_school", methods=['POST'])
    # @CustomResponsePackage
    # @Logger
    # @Auth(({"department_id":3,"actor":"10"},{"department_id":1,"actor":None}))
    # def update_school():
    #     if "name" not in request.form.keys() or \
    #         "school_id" not in request.form.keys() or "campus" not in request.form.keys():
    #         raise IllegalValueError("Not all required data received.", filename=__file__, line=sys._getframe().f_lineno)        
        
    #     formDict = dict(request.form)
    #     if not 0 <= int(formDict['school_id']) <= 50:
    #         raise IllegalValueError("人数上限应在0~50之间", filename=__file__, line=sys._getframe().f_lineno)
        
    #     returns = function_departmentManager.departmentManager(formDict=formDict)
    #     return {"warning":"", "message":returns["message"], "data":returns["data"]}