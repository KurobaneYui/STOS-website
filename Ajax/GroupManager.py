import flask
from flask import session, request
from Frame.python3.Authorization import Auth
from Frame.python3.Logger import Logger
from Frame.python3.DatabaseConnector import DatabaseConnector
from Frame.python3.CustomResponsePackage import CustomResponsePackage, IllegalValueError
import sys
import Ajax.function_departmentManager as function_departmentManager


def GroupManager(app : flask.Flask) -> None:
    @app.route("/Ajax/GroupManager/get_all_groups_members", methods=['GET'])
    @CustomResponsePackage
    @Logger
    @Auth(({"department_id":None,"actor":"10"},))
    def get_all_groups_members():
        connection = DatabaseConnector()
        connection.startCursor()
        
        DBAffectRows = connection.execute(
            sql=";")
        returns = connection.fetchall()
        return {"warning":"", "message":"", "data":returns}