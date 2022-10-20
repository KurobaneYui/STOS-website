from typing import KeysView
from Frame.python3.DatabaseConnector import DatabaseConnector
from Frame.python3.Authorization import Auth
from Frame.python3.Logger import Logger
from Frame.python3.CustomResponsePackage import DatabaseRuntimeError, IllegalValueError
import sys


@Logger
@Auth(({"department_id":1,"actor":"11"},))
def departmentManager(formDict : dict):
    connection = DatabaseConnector()
    connection.startCursor()
    
    if formDict['group_leader_id'] != "":
        DBAffectRows = connection.execute(
            sql="SELECT `student_id` FROM `MemberExtend` WHERE `student_id`=%(group_leader_id)s;",
            data=formDict)
        if DBAffectRows != 1:
            raise IllegalValueError("学号不存在，请检查输入的学号信息。", filename=__file__, line=sys._getframe().f_lineno)
        connection.fetchall()
        formDict["student_id"] = formDict['group_leader_id']
    else:
        formDict["like"] = '1%'
        DBAffectRows = connection.execute(
            sql="SELECT `student_id` FROM `Authority` WHERE department_id=%(department_id)s AND actor LIKE %(like)s;",
            data=formDict)
        del formDict["like"]
        results = connection.fetchall()
        if DBAffectRows == 1:
            formDict["student_id"] = results[0]["student_id"]
        else:
            formDict["student_id"] = ""

    DBAffectRows = connection.execute(
        sql="SELECT `name` FROM `Department` WHERE `department_id`=%(department_id)s;",
        data=formDict)
    if DBAffectRows != 1:
        raise IllegalValueError("部门编号不存在，请检查输入的部门编号信息。", filename=__file__, line=sys._getframe().f_lineno)
    if (connection.fetchall()[0]['name']=='队长'): formDict["job"] = '队长'
    else: formDict["job"] = "组长"

    DBAffectRows = connection.execute(
        sql="UPDATE `Department` SET `job_available`=%(max_num)s, `remark`=%(remark)s \
            WHERE `department_id`=%(department_id)s;",
        data=formDict,
        autoCommit=False)
    if DBAffectRows not in [0,1]:
        connection.rollback()
        raise DatabaseRuntimeError("Update department info error.", filename=__file__, line=sys._getframe().f_lineno)

    if formDict['group_leader_id'] != "":
        DBAffectRows = connection.execute(
            sql="SELECT `actor` FROM `Authority` \
                 WHERE student_id=%(student_id)s AND department_id=%(department_id)s;",
            data=formDict,
            autoCommit=False)
        results = connection.fetchall()
        if DBAffectRows==1 and results[0]["actor"][-1] == '1':
            formDict["job"] += "，组员"
            formDict["actor"] = "11"
            formDict["wage"] = "650"
        else:
            formDict["actor"] = "10"
            formDict["wage"] = "350"
        
        DBAffectRows = connection.execute(
            sql="INSERT INTO `Work` (student_id,department_id,job,wage,remark) \
                    VALUES (%(student_id)s,%(department_id)s,%(job)s,%(wage)s,'') \
                ON DUPLICATE KEY UPDATE \
                    student_id=%(student_id)s, department_id=%(department_id)s, \
                    job=%(job)s, wage=%(wage)s;",
            data=formDict,
            autoCommit=False)
        if DBAffectRows not in [0,1,2]:
            connection.rollback()
            raise DatabaseRuntimeError("Insert or Update work info error.", filename=__file__, line=sys._getframe().f_lineno)
        
        DBAffectRows = connection.execute(
            sql="INSERT INTO `Authority` (student_id,department_id,actor) \
                    VALUES (%(student_id)s,%(department_id)s,%(actor)s) \
                ON DUPLICATE KEY UPDATE \
                    student_id=%(student_id)s, department_id=%(department_id)s, actor=%(actor)s;",
            data=formDict,
            autoCommit=False)
        if DBAffectRows not in [0,1,2]:
            connection.rollback()
            raise DatabaseRuntimeError("Insert or Update authority info error.", filename=__file__, line=sys._getframe().f_lineno)
    elif formDict['student_id'] != "":
        DBAffectRows = connection.execute(
            sql="UPDATE `Work` SET job='组员', wage=300 \
                WHERE student_id=%(student_id)s AND department_id=%(department_id)s;",
            data=formDict,
            autoCommit=False)
        if DBAffectRows not in [0,1]:
            connection.rollback()
            raise DatabaseRuntimeError("Update work info error.", filename=__file__, line=sys._getframe().f_lineno)
        
        DBAffectRows = connection.execute(
            sql="UPDATE `Authority` SET actor='01' \
                 WHERE student_id=%(student_id)s AND department_id=%(department_id)s;",
            data=formDict,
            autoCommit=False)
        if DBAffectRows not in [0,1]:
            connection.rollback()
            raise DatabaseRuntimeError("Insert or Update authority info error.", filename=__file__, line=sys._getframe().f_lineno)
    else:
        DBAffectRows = connection.execute(
            sql="DELETE FROM `Work` WHERE student_id=%(student_id)s AND department_id=%(department_id)s;",
            data=formDict,
            autoCommit=False)
        if DBAffectRows not in [0,1]:
            connection.rollback()
            raise DatabaseRuntimeError("Delete work info error.", filename=__file__, line=sys._getframe().f_lineno)
        
        DBAffectRows = connection.execute(
            sql="DELETE FROM `Authority` WHERE student_id=%(student_id)s AND department_id=%(department_id)s;",
            data=formDict,
            autoCommit=False)
        if DBAffectRows not in [0,1]:
            connection.rollback()
            raise DatabaseRuntimeError("Insert or Update authority info error.", filename=__file__, line=sys._getframe().f_lineno)
    
    connection.commit()
    
    return {"warning":"", "message":"", "data":""}