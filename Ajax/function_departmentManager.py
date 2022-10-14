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
        raise DatabaseRuntimeError("Update department info error.", filename=__file__, line=sys._getframe().f_lineno)
    
    if formDict['group_leader_id'] != "":
        DBAffectRows = connection.execute(
            sql="INSERT INTO `Work` (student_id,department_id,job,wage,remark) \
                    VALUES (%(group_leader_id)s,%(department_id)s,%(job)s,350,'') \
                ON DUPLICATE KEY UPDATE \
                    student_id=%(group_leader_id)s, department_id=%(department_id)s, \
                    job=%(job)s, wage=350;",
            data=formDict,
            autoCommit=False)
        if DBAffectRows not in [0,1]:
            raise DatabaseRuntimeError("Insert or Update work info error.", filename=__file__, line=sys._getframe().f_lineno)
        
        DBAffectRows = connection.execute(
            sql="INSERT INTO `Authority` (student_id,department_id,actor) \
                    VALUES (%(group_leader_id)s,%(department_id)s,'10') \
                ON DUPLICATE KEY UPDATE \
                    student_id=%(group_leader_id)s, department_id=%(department_id)s, actor='10';",
            data=formDict,
            autoCommit=False)
        if DBAffectRows not in [0,1]:
            raise DatabaseRuntimeError("Insert or Update authority info error.", filename=__file__, line=sys._getframe().f_lineno)
    else:
        DBAffectRows = connection.execute(
            sql="DELETE FROM `Work` \
                WHERE student_id=%(group_leader_id)s, department_id=%(department_id)s, \
                    job=%(job)s, wage=350;",
            data=formDict,
            autoCommit=False)
        if DBAffectRows not in [0,1]:
            raise DatabaseRuntimeError("Insert or Update work info error.", filename=__file__, line=sys._getframe().f_lineno)
        
        DBAffectRows = connection.execute(
            sql="INSERT INTO `Authority` (student_id,department_id,actor) \
                    VALUES (%(group_leader_id)s,%(department_id)s,'10') \
                ON DUPLICATE KEY UPDATE \
                    student_id=%(group_leader_id)s, department_id=%(department_id)s, actor='10';",
            data=formDict,
            autoCommit=False)
        if DBAffectRows not in [0,1]:
            raise DatabaseRuntimeError("Insert or Update authority info error.", filename=__file__, line=sys._getframe().f_lineno)
    
    connection.commit()
    
    return {"warning":"", "message":"", "data":""}