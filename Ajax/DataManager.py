"""
This file is for background data manager.
In this file, all URL apis are designed for manage STSA background data.
Usually, all functions need team leader, Data-Management-Group member or leader auth.
"""


import flask
from flask import session, request
from Frame.python3.Authorization import Auth
from Frame.python3.Logger import Logger
from Frame.python3.DatabaseConnector import DatabaseConnector
from Frame.python3.CustomResponsePackage import CustomResponsePackage, IllegalValueError, MaintenanceError
import sys
import Ajax.function_departmentManager as function_departmentManager


def DataManager(app: flask.Flask) -> None:
    @app.route("/Ajax/DataManager/get_campus_for_form", methods=['GET'])
    @CustomResponsePackage
    @Logger
    def get_campus_for_form():
        connection = DatabaseConnector()
        connection.startCursor()
        DBAffectRows = connection.execute(
            "SELECT campus FROM `School` UNION SELECT campus FROM `Classroom`;")
        returns = connection.fetchall()
        return {"warning": "", "message": "", "data": returns}

    @app.route("/Ajax/DataManager/get_school_for_form", methods=['POST'])
    @CustomResponsePackage
    @Logger
    def get_school_for_form():
        connection = DatabaseConnector()
        connection.startCursor()
        DBAffectRows = connection.execute(
            "SELECT name FROM `School` WHERE campus=%(campus)s;",
            request.form)
        returns = connection.fetchall()
        return {"warning": "", "message": "", "data": returns}

    @app.route("/Ajax/DataManager/get_school", methods=['GET'])
    @CustomResponsePackage
    @Logger
    @Auth(({"department_id": 3, "actor": "10"}, {"department_id": 1, "actor": None}))
    def get_school():
        connection = DatabaseConnector()
        connection.startCursor()
        DBAffectRows = connection.execute(
            "SELECT school_id,name,campus FROM `School`;")
        returns = connection.fetchall()
        return {"warning": "", "message": "", "data": returns}

    @app.route("/Ajax/DataManager/update_school", methods=['POST'])
    @CustomResponsePackage
    @Logger
    @Auth(({"department_id": 3, "actor": "10"}, {"department_id": 1, "actor": None}))
    def update_school():
        if "name" not in request.form.keys() or "old_school_id" not in request.form.keys() or \
                "school_id" not in request.form.keys() or "campus" not in request.form.keys():
            raise IllegalValueError(
                "Not all required data received.", filename=__file__, line=sys._getframe().f_lineno)

        formDict = dict(request.form)
        if not 1 <= int(formDict['school_id']) <= 50 or not 1 <= int(formDict['old_school_id']) <= 50:
            raise IllegalValueError(
                "学院编号目前支持1~50之间", filename=__file__, line=sys._getframe().f_lineno)

        connection = DatabaseConnector()
        connection.startCursor()

        DBAffectRows = connection.execute(
            "SELECT 'school_id' FROM `School` WHERE school_id=%(school_id)s;",
            formDict)
        results = connection.fetchall()
        if formDict["school_id"] != formDict["old_school_id"] and DBAffectRows != 0:
            raise IllegalValueError(
                "学院 ID 已存在，请检查输入避免重复。", filename=__file__, line=sys._getframe().f_lineno)

        DBAffectRows = connection.execute(
            "UPDATE `School` SET school_id=%(school_id)s,name=%(name)s,campus=%(campus)s WHERE school_id=%(old_school_id)s;",
            formDict)

        return {"warning": "", "message": "", "data": ""}

    @app.route("/Ajax/DataManager/delete_school", methods=['POST'])
    @CustomResponsePackage
    @Logger
    @Auth(({"department_id": 3, "actor": "10"}, {"department_id": 1, "actor": None}))
    def delete_school():
        raise MaintenanceError("Function has not been developed yet.",
                               filename=__file__, line=sys._getframe().f_lineno)

        return {"warning": "", "message": "", "data": ""}

    @app.route("/Ajax/DataManager/add_school", methods=['POST'])
    @CustomResponsePackage
    @Logger
    @Auth(({"department_id": 3, "actor": "10"}, {"department_id": 1, "actor": None}))
    def add_school():
        raise MaintenanceError("Function has not been developed yet.",
                               filename=__file__, line=sys._getframe().f_lineno)

        return {"warning": "", "message": "", "data": ""}

    @app.route("/Ajax/DataManager/get_submitted_selfstudy_date", methods=['GET'])
    @CustomResponsePackage
    @Logger
    @Auth(({"department_id": None, "actor": "11"},))
    def get_submitted_selfstudy_date():
        connection = DatabaseConnector()
        connection.startCursor()
        DBAffectRows = connection.execute(
            "SELECT DISTINCT date FROM SelfstudyInfo ORDER BY date DESC LIMIT 15;")
        returns = connection.fetchall()
        for i in returns:
            i['date'] = str(i['date'])
        return {"warning": "", "message": "", "data": returns}

    @app.route("/Ajax/DataManager/get_selfstudy_classroom_details", methods=['POST'])
    @CustomResponsePackage
    @Logger
    @Auth(({"department_id": None, "actor": "11"},))
    def get_selfstudy_classroom_details():
        connection = DatabaseConnector()
        connection.startCursor()
        DBAffectRows = connection.execute("SELECT Classroom.campus AS campus,building,area,room,sit_available, \
                                               School.name AS school_name,selfstudy_id,student_supposed,remark \
                                           FROM SelfstudyInfo \
                                           LEFT JOIN Classroom ON SelfstudyInfo.classroom_id=Classroom.classroom_id \
                                           LEFT JOIN School ON SelfstudyInfo.school_id=School.school_id AND School.campus=Classroom.campus \
                                           WHERE date=%(date)s \
                                           ORDER BY campus,building,area,room;",
                                          request.form)
        returns = connection.fetchall()
        return {"warning": "", "message": "", "data": returns}
