from enum import auto
from Frame.python3.DatabaseConnector import DatabaseConnector
from Frame.python3.CustomResponsePackage import DatabaseRuntimeError
from Frame.python3.Authorization import Auth
from Frame.python3.Logger import Logger
from flask import session, request
import sys


@Logger
@Auth(({"department_id": 1, "actor": None}, {"department_id": None, "actor": "10"}))
def get_all_groups_members():
    connection = DatabaseConnector()
    connection.startCursor()

    DBAffectRows = connection.execute("SET @groupLeaderID='';")
    DBAffectRows = connection.execute(
        sql="SELECT student_id INTO @groupLeaderID FROM Authority WHERE student_id=%s and actor!='00' and department_id=1;",
        data=(session['userID'],))
    connection.fetchall()
    DBAffectRows = connection.execute(
        sql="SELECT MemberExtend.student_id AS student_id, MemberBasic.`name` as student_name, gender, \
                Department.`name` as department_name, Department.department_id AS department_id \
            FROM `Work` \
            LEFT JOIN Authority ON Authority.student_id=`Work`.student_id AND Authority.department_id=`Work`.department_id \
            LEFT JOIN Department ON Department.department_id=`Work`.department_id \
            LEFT JOIN MemberExtend ON `Work`.student_id=MemberExtend.student_id \
            LEFT JOIN MemberBasic ON MemberExtend.student_id=MemberBasic.student_id \
            WHERE actor !='00' AND Department.department_id in ( \
                SELECT department_id FROM Authority \
                    WHERE CASE WHEN @groupLeaderID=%s \
                    THEN true \
                    ELSE actor like %s AND student_id=%s \
                    END) \
            ORDER BY department_id ASC;",
        data=(session['userID'], '1%', session['userID']))
    getDatas = connection.fetchall()
    returns = dict()
    for row in getDatas:
        if row['department_id'] not in returns.keys():
            returns[row['department_id']] = {
                'group_name': row['department_name'], 'members': list()}
        returns[row['department_id']]['members'].append(row)

    return {"warning": "", "message": "", "data": returns}


@Logger
@Auth(({"department_id": 1, "actor": None}, {"department_id": None, "actor": "10"}))
def search_member():
    connection = DatabaseConnector()
    connection.startCursor()
    student_ids = request.form["student_ids"].split(",")
    student_num = len(student_ids)
    query_part = ','.join(["%s"] * student_num)
    connection.execute(
        sql="SELECT MemberExtend.student_id AS student_id, `name`, gender \
            FROM MemberExtend \
            LEFT JOIN MemberBasic ON MemberExtend.student_id=MemberBasic.student_id \
            WHERE MemberExtend.student_id IN ({});".format(query_part),
        data=student_ids)
    results = connection.fetchall()
    return {'warning': '', 'message': '', 'data': results}


@Logger
@Auth(({"department_id": 1, "actor": None}, {"department_id": None, "actor": "10"}))
def _add_member(student_id, group_id):
    connection = DatabaseConnector()
    connection.startCursor()
    connection.execute(
        sql="SELECT actor FROM Authority WHERE student_id=%s AND department_id=%s;",
        data=(student_id, group_id))
    results = connection.fetchall()
    if len(results) != 0 and results[0]['actor'][0] == '1':
        job = "组长，组员"
        actor = "11"
        wage = 650
    else:
        job = "组员"
        actor = "01"
        wage = 300
    if group_id == 1:
        job = "队长"

    DBAffectedRows = connection.execute(
        sql="INSERT INTO Authority (student_id, department_id, actor) VALUES \
            (%s,%s,%s) \
            ON DUPLICATE KEY UPDATE actor=%s;",
        data=(student_id, group_id, actor, actor),
        autoCommit=False)
    if DBAffectedRows not in [0, 1, 2]:
        print(DBAffectedRows)
        connection.rollback()
        raise DatabaseRuntimeError(
            "Insert or Update authority info error.", filename=__file__, line=sys._getframe().f_lineno)

    DBAffectedRows = connection.execute(
        sql="INSERT INTO Work (student_id,department_id,job,wage,remark) \
            VALUES (%s,%s,%s,%s,%s) \
            ON DUPLICATE KEY UPDATE job=%s,wage=%s;",
        data=(student_id, group_id, job, wage, "", job, wage),
        autoCommit=False
    )
    if DBAffectedRows not in [0, 1, 2]:
        connection.rollback()
        raise DatabaseRuntimeError(
            "Insert or Update work info error.", filename=__file__, line=sys._getframe().f_lineno)

    connection.commit()

    return {"warning": "", "message": "", "data": ""}


@Logger
@Auth(({"department_id": 1, "actor": None},))
def add_member_No1(student_id):
    returns = _add_member(student_id, 1)
    returns["warning"] = ""
    return returns


@Logger
@Auth(({"department_id": 1, "actor": None}, {"department_id": 2, "actor": "10"}))
def add_member_No2(student_id):
    returns = _add_member(student_id, 2)
    returns["warning"] = ""
    return returns


@Logger
@Auth(({"department_id": 1, "actor": None}, {"department_id": 3, "actor": "10"}))
def add_member_No3(student_id):
    returns = _add_member(student_id, 3)
    returns["warning"] = ""
    return returns


@Logger
@Auth(({"department_id": 1, "actor": None}, {"department_id": 4, "actor": "10"}))
def add_member_No4(student_id):
    returns = _add_member(student_id, 4)
    returns["warning"] = ""
    return returns


@Logger
@Auth(({"department_id": 1, "actor": None}, {"department_id": 5, "actor": "10"}))
def add_member_No5(student_id):
    returns = _add_member(student_id, 5)
    returns["warning"] = ""
    return returns


@Logger
@Auth(({"department_id": 1, "actor": None}, {"department_id": 6, "actor": "10"}))
def add_member_No6(student_id):
    returns = _add_member(student_id, 6)
    returns["warning"] = ""
    return returns


@Logger
@Auth(({"department_id": 1, "actor": None}, {"department_id": 7, "actor": "10"}))
def add_member_No7(student_id):
    returns = _add_member(student_id, 7)
    returns["warning"] = ""
    return returns


@Logger
@Auth(({"department_id": 1, "actor": None}, {"department_id": 8, "actor": "10"}))
def add_member_No8(student_id):
    returns = _add_member(student_id, 8)
    returns["warning"] = ""
    return returns


@Logger
@Auth(({"department_id": 1, "actor": None}, {"department_id": 9, "actor": "10"}))
def add_member_No9(student_id):
    returns = _add_member(student_id, 9)
    returns["warning"] = ""
    return returns


@Logger
@Auth(({"department_id": 1, "actor": None}, {"department_id": 10, "actor": "10"}))
def add_member_No10(student_id):
    returns = _add_member(student_id, 10)
    returns["warning"] = ""
    return returns


@Logger
@Auth(({"department_id": 1, "actor": None}, {"department_id": None, "actor": "10"}))
def _remove_member(student_id, group_id):
    connection = DatabaseConnector()
    connection.startCursor()
    connection.execute(
        sql="SELECT actor FROM Authority WHERE student_id=%s AND department_id=%s;",
        data=(student_id, group_id))
    results = connection.fetchall()
    
    if len(results) != 0 and results[0]['actor'][0] == '1':
        canDelete = False
        job = "组长"
        actor = "10"
        wage = 350
        if group_id == 1:
            job = "队长"
    else:
        canDelete = True
        
    if canDelete:
        DBAffectedRows = connection.execute(
            sql="DELETE FROM Authority WHERE student_id=%s AND department_id=%s;",
            data=(student_id, group_id),
            autoCommit=False
        )
        if DBAffectedRows not in [0, 1]:
            connection.rollback()
            raise DatabaseRuntimeError(
                "Delete authority info error.", filename=__file__, line=sys._getframe().f_lineno)
            
        DBAffectedRows = connection.execute(
            sql="DELETE FROM Work WHERE student_id=%s AND department_id=%s;",
            data=(student_id, group_id),
            autoCommit=False
        )
        if DBAffectedRows not in [0, 1]:
            connection.rollback()
            raise DatabaseRuntimeError(
                "Delete work info error.", filename=__file__, line=sys._getframe().f_lineno)
    else:
        DBAffectedRows = connection.execute(
            sql="INSERT INTO Authority (student_id, department_id, actor) VALUES \
                (%s,%s,%s) \
                ON DUPLICATE KEY UPDATE actor=%s;",
            data=(student_id, group_id, actor, actor),
            autoCommit=False)
        if DBAffectedRows not in [0, 1, 2]:
            print(DBAffectedRows)
            connection.rollback()
            raise DatabaseRuntimeError(
                "Insert or Update authority info error.", filename=__file__, line=sys._getframe().f_lineno)

        DBAffectedRows = connection.execute(
            sql="INSERT INTO Work (student_id,department_id,job,wage,remark) \
                VALUES (%s,%s,%s,%s,%s) \
                ON DUPLICATE KEY UPDATE job=%s,wage=%s;",
            data=(student_id, group_id, job, wage, "", job, wage),
            autoCommit=False
        )
        if DBAffectedRows not in [0, 1, 2]:
            connection.rollback()
            raise DatabaseRuntimeError(
                "Insert or Update work info error.", filename=__file__, line=sys._getframe().f_lineno)

    connection.commit()

    return {"warning": "", "message": "", "data": ""}


@Logger
@Auth(({"department_id": 1, "actor": None},))
def remove_member_No1(student_id):
    returns = _remove_member(student_id, 1)
    returns["warning"] = ""
    return returns


@Logger
@Auth(({"department_id": 1, "actor": None}, {"department_id": 2, "actor": "10"}))
def remove_member_No2(student_id):
    returns = _remove_member(student_id, 2)
    returns["warning"] = ""
    return returns


@Logger
@Auth(({"department_id": 1, "actor": None}, {"department_id": 3, "actor": "10"}))
def remove_member_No3(student_id):
    returns = _remove_member(student_id, 3)
    returns["warning"] = ""
    return returns


@Logger
@Auth(({"department_id": 1, "actor": None}, {"department_id": 4, "actor": "10"}))
def remove_member_No4(student_id):
    returns = _remove_member(student_id, 4)
    returns["warning"] = ""
    return returns


@Logger
@Auth(({"department_id": 1, "actor": None}, {"department_id": 5, "actor": "10"}))
def remove_member_No5(student_id):
    returns = _remove_member(student_id, 5)
    returns["warning"] = ""
    return returns


@Logger
@Auth(({"department_id": 1, "actor": None}, {"department_id": 6, "actor": "10"}))
def remove_member_No6(student_id):
    returns = _remove_member(student_id, 6)
    returns["warning"] = ""
    return returns


@Logger
@Auth(({"department_id": 1, "actor": None}, {"department_id": 7, "actor": "10"}))
def remove_member_No7(student_id):
    returns = _remove_member(student_id, 7)
    returns["warning"] = ""
    return returns


@Logger
@Auth(({"department_id": 1, "actor": None}, {"department_id": 8, "actor": "10"}))
def remove_member_No8(student_id):
    returns = _remove_member(student_id, 8)
    returns["warning"] = ""
    return returns


@Logger
@Auth(({"department_id": 1, "actor": None}, {"department_id": 9, "actor": "10"}))
def remove_member_No9(student_id):
    returns = _remove_member(student_id, 9)
    returns["warning"] = ""
    return returns


@Logger
@Auth(({"department_id": 1, "actor": None}, {"department_id": 10, "actor": "10"}))
def remove_member_No10(student_id):
    returns = _remove_member(student_id, 10)
    returns["warning"] = ""
    return returns


add_member_functions = {
    1: add_member_No1,
    2: add_member_No2,
    3: add_member_No3,
    4: add_member_No4,
    5: add_member_No5,
    6: add_member_No6,
    7: add_member_No7,
    8: add_member_No8,
    9: add_member_No9,
    10: add_member_No10
}

remove_member_functions = {
    1: remove_member_No1,
    2: remove_member_No2,
    3: remove_member_No3,
    4: remove_member_No4,
    5: remove_member_No5,
    6: remove_member_No6,
    7: remove_member_No7,
    8: remove_member_No8,
    9: remove_member_No9,
    10: remove_member_No10
}
