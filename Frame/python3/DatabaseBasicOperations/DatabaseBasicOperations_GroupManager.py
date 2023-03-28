import sys
import datetime
from flask import Request
from Frame.python3.BaseComponents.CustomSession import CustomSession
from Frame.python3.BaseComponents.DatabaseConnector import DatabaseConnector
from Frame.python3.BaseComponents.CustomError import DatabaseRuntimeError


class DatabaseBasicOperations_GroupManager:
    @staticmethod
    def getAllGroupsMembers(databaseConnector: DatabaseConnector | None = None) -> dict:
        # =====================================
        # 如果提供已经建立的数据库连接，则直接使用
        if databaseConnector is None:
            database = DatabaseConnector()
            database.startCursor()
        else:
            database = databaseConnector
        # =================================================================
        # 获取管理的部门，如果是队长组，直接获取所有部门，如果不是则获取管理的组
        if CustomSession().getSession()["department_id"] == 1:
            _ = database.execute(
                sql="SELECT DISTINCT department_id, name AS department_name \
                    FROM Department \
                    WHERE department_id != 0 \
                    ORDER BY department_id ASC;",
                data=CustomSession().getSession())
        else:
            _ = database.execute(
                sql="SELECT DISTINCT Work.department_id AS department_id, Department.name AS department_name \
                    FROM Work \
                    LEFT JOIN Department ON Work.department_id = Department.department_id \
                    WHERE student_id = %(userID)s AND (job = 1 OR Work.department_id = 1) AND Work.department_id != 0 \
                    ORDER BY Work.department_id ASC;",
                data=CustomSession().getSession())
        groupsInManagement = database.fetchall()
        # =================
        # 搜索管理的组的组员
        results = dict()
        for groupInfo in groupsInManagement:
            if groupInfo["department_id"] in results.keys():
                continue
            _ = database.execute(
                sql="SELECT MemberExtend.student_id AS student_id, MemberBasic.`name` as student_name, gender, \
                        Department.`name` as department_name, Department.department_id AS department_id \
                    FROM `Work` \
                    LEFT JOIN Department ON Department.department_id=`Work`.department_id \
                    LEFT JOIN MemberExtend ON `Work`.student_id=MemberExtend.student_id \
                    LEFT JOIN MemberBasic ON MemberExtend.student_id=MemberBasic.student_id \
                    WHERE job = 0 AND Department.department_id = %(department_id)s \
                    ORDER BY Work.department_id ASC;",
                data=groupInfo)
            members = database.fetchall()
            results[groupInfo["department_id"]] = {
                "group_name": groupInfo["department_name"],
                "members": members
            }
        # ========
        # 返回内容
        return results

    @staticmethod
    def searchMember(flaskRequest: Request, databaseConnector: DatabaseConnector | None = None) -> tuple[dict] | list[dict]:
        # =====================================
        # 如果提供已经建立的数据库连接，则直接使用
        if databaseConnector is None:
            database = DatabaseConnector()
            database.startCursor()
        else:
            database = databaseConnector
        # =============================
        # 查询提供的学号列表对应的学生信息
        student_ids = flaskRequest.form["student_ids"].split(",")
        student_num = len(student_ids)
        query_part = ','.join(["%s"] * student_num)
        database.execute(
            sql="SELECT MemberExtend.student_id AS student_id, `name`, gender \
                FROM MemberExtend \
                LEFT JOIN MemberBasic ON MemberExtend.student_id=MemberBasic.student_id \
                WHERE MemberExtend.student_id IN ({});".format(query_part),
            data=student_ids)
        return database.fetchall()

    # 在权限系统设计完成后，修改此处SQL处理
    @staticmethod
    def addMember(flaskRequest: Request, databaseConnector: DatabaseConnector | None = None) -> None:
        # =====================================
        # 如果提供已经建立的数据库连接，则直接使用
        if databaseConnector is None:
            database = DatabaseConnector()
            database.startCursor()
        else:
            database = databaseConnector
        # =============
        # 提取学号和组号
        student_id = flaskRequest.form["student_id"]
        group_id = int(flaskRequest.form["group_id"])
        # ============
        # 更新权限信息
        DBAffectedRows = database.execute(
            sql="INSERT IGNORE INTO Authority (student_id, department_id, actor) VALUES \
                (%s,%s,%s);",
            data=(student_id, group_id, 0),
            autoCommit=False)
        if DBAffectedRows not in [0, 1]:
            print(DBAffectedRows)
            database.rollback()
            raise DatabaseRuntimeError(
                "Insert authority info error.", filename=__file__, line=sys._getframe().f_lineno)
        # ============
        # 更新岗位信息
        DBAffectedRows = database.execute(
            sql="INSERT IGNORE INTO Work (student_id,department_id,job,wage,remark) \
                VALUES (%s,%s,%s,%s,%s);",
            data=(student_id, group_id, 0, 300, ""),
            autoCommit=False
        )
        if DBAffectedRows not in [0, 1]:
            database.rollback()
            raise DatabaseRuntimeError(
                "Insert work info error.", filename=__file__, line=sys._getframe().f_lineno)
        # ===========
        # 调整扣分信息
        DBAffectRows = database.execute(
            sql="INSERT IGNORE INTO `Score` (student_id,department_id,job,date,reason,variant) VALUES \
                (%s,%s,%s,%s,'岗位初始',5);",
            data=(student_id, group_id, 0, datetime.datetime.now().date()),
            autoCommit=False)
        if DBAffectRows not in [0, 1]:
            database.rollback()
            raise DatabaseRuntimeError(
                "Insert score info error.", filename=__file__, line=sys._getframe().f_lineno)
        # ==================================
        # 对加入的成员提供额外的全队成员权限
        # 对加入队长组的提供额外的全队管理权限
        DBAffectRows = database.execute(
            sql="INSERT IGNORE INTO `Authority` (student_id,department_id,actor) \
                    VALUES (%s,0,0);",
            data=(student_id,),
            autoCommit=False)
        if DBAffectRows not in [0, 1]:
            database.rollback()
            raise DatabaseRuntimeError(
                "Insert special authority info error.", filename=__file__, line=sys._getframe().f_lineno)
        DBAffectRows = database.execute(
            sql="SELECT * FROM `Work` WHERE student_id=%s AND department_id=1;",
            data=(student_id,))
        database.fetchall()
        if DBAffectRows == 1:
            DBAffectRows = database.execute(
                sql="INSERT IGNORE INTO `Authority` (student_id,department_id,actor) \
                        VALUES (%s,0,1);",
                data=(student_id,),
                autoCommit=False)
            if DBAffectRows not in [0, 1]:
                database.rollback()
                raise DatabaseRuntimeError(
                    "Insert special authority info error.", filename=__file__, line=sys._getframe().f_lineno)
        # ========
        # 提交事务
        database.commit()

    @staticmethod
    def removeMember(flaskRequest: Request, databaseConnector: DatabaseConnector | None = None) -> None:
        # =====================================
        # 如果提供已经建立的数据库连接，则直接使用
        if databaseConnector is None:
            database = DatabaseConnector()
            database.startCursor()
        else:
            database = databaseConnector
        # =============
        # 提取学号和组号
        student_id = flaskRequest.form["student_id"]
        group_id = int(flaskRequest.form["group_id"])
        # ============
        # 删除权限信息
        DBAffectedRows = database.execute(
            sql="DELETE FROM Authority WHERE student_id=%s AND department_id=%s AND actor=0;",
            data=(student_id, group_id),
            autoCommit=False
        )
        if DBAffectedRows not in [0, 1]:
            database.rollback()
            raise DatabaseRuntimeError(
                "Delete authority info error.", filename=__file__, line=sys._getframe().f_lineno)
        # ============
        # 删除岗位信息
        DBAffectedRows = database.execute(
            sql="DELETE FROM Work WHERE student_id=%s AND department_id=%s AND job=0;",
            data=(student_id, group_id),
            autoCommit=False
        )
        if DBAffectedRows not in [0, 1]:
            database.rollback()
            raise DatabaseRuntimeError(
                "Delete work info error.", filename=__file__, line=sys._getframe().f_lineno)
        # ================================
        # 对移出队长组的移除全队管理权限
        # 对不再有任何岗位的移除全队成员权限
        DBAffectRows = database.execute(
            sql="SELECT * FROM `Work` WHERE student_id=%s AND department_id=1;",
            data=(student_id,))
        database.fetchall()
        if DBAffectRows == 0:
            DBAffectRows = database.execute(
                sql="DELETE FROM `Authority` \
                    WHERE student_id=%s AND department_id=0 AND actor=1;",
                data=(student_id,),
                autoCommit=False)
            if DBAffectRows not in [0, 1]:
                database.rollback()
                raise DatabaseRuntimeError(
                    "Delete special authority info error.", filename=__file__, line=sys._getframe().f_lineno)
        DBAffectRows = database.execute(
            sql="SELECT * FROM `Work` WHERE student_id=%s;",
            data=(student_id,))
        database.fetchall()
        if DBAffectRows == 0:
            DBAffectRows = database.execute(
                sql="DELETE FROM `Authority` \
                    WHERE student_id=%s AND department_id=0 AND actor=0;",
                data=(student_id,),
                autoCommit=False)
            if DBAffectRows not in [0, 1]:
                database.rollback()
                raise DatabaseRuntimeError(
                    "Delete special authority info error.", filename=__file__, line=sys._getframe().f_lineno)
        # ========
        # 提交事务
        database.commit()
