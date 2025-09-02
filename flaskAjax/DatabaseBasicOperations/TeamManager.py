import sys
import random
import datetime
from flask import Request
from sqlalchemy.orm import Session
from contextlib import nullcontext
from flaskAjax.BaseComponents.DatabaseConnector import (
    SQL_Blacklist,
    SQL_Campus,
    SQL_College,
    SQL_Group,
    SQL_GroupMember,
    SQL_User,
    SQL_UserProfile,
    SessionLocal,
)
from flaskAjax.BaseComponents.CustomError import DatabaseRuntimeError, IllegalValueError
# import Program.python.FinanceProcess as FinanceProcess


class TeamManagerDatabase:
    @staticmethod
    def getBlacklist(db_session: Session | None = None) -> tuple[dict] | list[dict]:
        # =====================================
        # 如果提供已经建立的数据库连接，则直接使用
        session_context = (
            SessionLocal() if db_session is None else nullcontext(db_session)
        )
        with session_context as session:
            # ==============
            # 查询黑名单数据
            results = (
                session.query(SQL_User, SQL_Blacklist.reason)
                .join(SQL_Blacklist, SQL_User.student_id == SQL_Blacklist.student_id)
                .all()
            )
            results = [
                {
                    "rowNum": idx + 1,
                    "student_id": user.student_id,
                    "name": user.name,
                    "gender": user.gender,
                    "reason": reason,
                }
                for idx, (user, reason) in enumerate(results)
            ]
            return results

    @staticmethod
    def getDepartment(db_session: Session | None = None) -> tuple[dict] | list[dict]:
        # =====================================
        # 如果提供已经建立的数据库连接，则直接使用
        session_context = (
            SessionLocal() if db_session is None else nullcontext(db_session)
        )
        with session_context as session:
            # ORM: groups LEFT JOIN group_members（仅 role='manager'） LEFT JOIN users
            gm = SQL_GroupMember
            q = (
                session.query(
                    SQL_Group.id.label("department_id"),
                    SQL_Group.name.label("department_name"),
                    gm.student_id.label("student_id"),
                    SQL_User.name.label("student_name"),
                    gm.remark.label("remark"),
                )
                .outerjoin(gm, (SQL_Group.id == gm.group_id) & (gm.role == "manager"))
                .outerjoin(SQL_User, SQL_User.student_id == gm.student_id)
                .filter(SQL_Group.id != 0)
            )
            rows = q.all()
            results = [
                {
                    "department_id": row.department_id,
                    "department_name": row.department_name,
                    "remark": row.remark or "",
                    "student_id": row.student_id or "",
                    "student_name": row.student_name or "",
                }
                for row in rows
            ]
            return results

    # # 在权限系统设计完成后，修改此处SQL处理
    # @staticmethod
    # def updateDepartment(infoForm: dict, db_session: Session | None = None) -> None:
    #     # =====================================
    #     # 如果提供已经建立的数据库连接，则直接使用
    #     session_context = (
    #         SessionLocal() if db_session is None else nullcontext(db_session)
    #     )
    #     with session_context as session:
    #         # =================================
    #         # 如果提供了组长学号，确保存在即可继续
    #         if infoForm["group_leader_id"] != "":
    #             DBAffectRows = database.execute(
    #                 sql="SELECT `student_id` FROM `MemberExtend` WHERE `student_id`=%(group_leader_id)s;",
    #                 data=infoForm,
    #             )
    #             if DBAffectRows != 1:
    #                 raise IllegalValueError(
    #                     "学号不存在，请检查输入的学号信息。",
    #                     filename=__file__,
    #                     line=sys._getframe().f_lineno,
    #                 )
    #             database.fetchall()
    #         infoForm["new_group_leader"] = infoForm["group_leader_id"]
    #         # ==============
    #         # 查询原组长信息
    #         DBAffectRows = database.execute(
    #             sql="SELECT `student_id` FROM `Work` WHERE department_id=%(department_id)s AND job = 1;",
    #             data=infoForm,
    #         )
    #         results = database.fetchall()
    #         if DBAffectRows > 0:
    #             infoForm["ori_group_leader"] = results[0]["student_id"]
    #         else:
    #             infoForm["ori_group_leader"] = ""
    #         # =======================
    #         # 更新部门人数和备注等信息
    #         DBAffectRows = database.execute(
    #             sql="UPDATE `Department` SET `job_available`=%(max_num)s, `remark`=%(remark)s \
    #                 WHERE `department_id`=%(department_id)s;",
    #             data=infoForm,
    #             autoCommit=False,
    #         )
    #         if DBAffectRows not in [0, 1]:
    #             database.rollback()
    #             raise DatabaseRuntimeError(
    #                 "Update department info error.",
    #                 filename=__file__,
    #                 line=sys._getframe().f_lineno,
    #             )
    #         # =======================================
    #         # 对卸任组长取消岗位信息、权限信息、分数信息
    #         if infoForm["ori_group_leader"] != "":
    #             # ===============
    #             # 开始删除岗位信息
    #             DBAffectRows = database.execute(
    #                 sql="DELETE FROM `Work` \
    #                     WHERE student_id=%(ori_group_leader)s AND department_id=%(department_id)s AND job=1;",
    #                 data=infoForm,
    #                 autoCommit=False,
    #             )
    #             if DBAffectRows not in [0, 1]:
    #                 database.rollback()
    #                 raise DatabaseRuntimeError(
    #                     "Delete work info error.",
    #                     filename=__file__,
    #                     line=sys._getframe().f_lineno,
    #                 )
    #             # ===============
    #             # 开始删除权限信息
    #             DBAffectRows = database.execute(
    #                 sql="DELETE FROM `Authority` \
    #                     WHERE student_id=%(ori_group_leader)s AND department_id=%(department_id)s AND actor=1;",
    #                 data=infoForm,
    #                 autoCommit=False,
    #             )
    #             if DBAffectRows not in [0, 1]:
    #                 database.rollback()
    #                 raise DatabaseRuntimeError(
    #                     "Delete authority info error.",
    #                     filename=__file__,
    #                     line=sys._getframe().f_lineno,
    #                 )
    #         # =======================================
    #         # 对新任组长分配岗位信息、权限信息、分数信息
    #         if infoForm["new_group_leader"] != "":
    #             # ===============
    #             # 开始插入岗位信息
    #             DBAffectRows = database.execute(
    #                 sql="INSERT IGNORE INTO `Work` (student_id,department_id,job,wage,remark) \
    #                         VALUES (%(new_group_leader)s,%(department_id)s,1,350,'');",
    #                 data=infoForm,
    #                 autoCommit=False,
    #             )
    #             if DBAffectRows not in [0, 1]:
    #                 database.rollback()
    #                 raise DatabaseRuntimeError(
    #                     "Insert work info error.",
    #                     filename=__file__,
    #                     line=sys._getframe().f_lineno,
    #                 )
    #             # ===============
    #             # 开始插入权限信息
    #             DBAffectRows = database.execute(
    #                 sql="INSERT IGNORE INTO `Authority` (student_id,department_id,actor) \
    #                         VALUES (%(new_group_leader)s,%(department_id)s,1);",
    #                 data=infoForm,
    #                 autoCommit=False,
    #             )
    #             if DBAffectRows not in [0, 1]:
    #                 database.rollback()
    #                 raise DatabaseRuntimeError(
    #                     "Insert authority info error.",
    #                     filename=__file__,
    #                     line=sys._getframe().f_lineno,
    #                 )
    #             # ===============
    #             # 开始插入分数信息
    #             infoForm["date"] = datetime.datetime.now().date()
    #             DBAffectRows = database.execute(
    #                 sql="INSERT IGNORE INTO `Score` (student_id,department_id,job,date,reason,variant) VALUES \
    #                     (%(new_group_leader)s,%(department_id)s,1,%(date)s,'岗位初始',5);",
    #                 data=infoForm,
    #                 autoCommit=False,
    #             )
    #             if DBAffectRows not in [0, 1]:
    #                 database.rollback()
    #                 raise DatabaseRuntimeError(
    #                     "Insert score info error.",
    #                     filename=__file__,
    #                     line=sys._getframe().f_lineno,
    #                 )
    #         # ==================================
    #         # 对加入的成员提供额外的全队成员权限
    #         # 对加入队长组的提供额外的全队管理权限
    #         DBAffectRows = database.execute(
    #             sql="INSERT IGNORE INTO `Authority` (student_id,department_id,actor) \
    #                     VALUES (%(new_group_leader)s,0,0);",
    #             data=infoForm,
    #             autoCommit=False,
    #         )
    #         if DBAffectRows not in [0, 1]:
    #             database.rollback()
    #             raise DatabaseRuntimeError(
    #                 "Insert special authority info error.",
    #                 filename=__file__,
    #                 line=sys._getframe().f_lineno,
    #             )
    #         DBAffectRows = database.execute(
    #             sql="SELECT * FROM `Work` WHERE student_id=%(new_group_leader)s AND department_id=1;",
    #             data=infoForm,
    #         )
    #         database.fetchall()
    #         if DBAffectRows == 1:
    #             DBAffectRows = database.execute(
    #                 sql="INSERT IGNORE INTO `Authority` (student_id,department_id,actor) \
    #                         VALUES (%(new_group_leader)s,0,1);",
    #                 data=infoForm,
    #                 autoCommit=False,
    #             )
    #             if DBAffectRows not in [0, 1]:
    #                 database.rollback()
    #                 raise DatabaseRuntimeError(
    #                     "Insert special authority info error.",
    #                     filename=__file__,
    #                     line=sys._getframe().f_lineno,
    #                 )
    #         # ================================
    #         # 对移出队长组的移除全队管理权限
    #         # 对不再有任何岗位的移除全队成员权限
    #         DBAffectRows = database.execute(
    #             sql="SELECT * FROM `Work` WHERE student_id=%(ori_group_leader)s AND department_id=1;",
    #             data=infoForm,
    #         )
    #         database.fetchall()
    #         if DBAffectRows == 0:
    #             DBAffectRows = database.execute(
    #                 sql="DELETE FROM `Authority` \
    #                     WHERE student_id=%(ori_group_leader)s AND department_id=0 AND actor=1;",
    #                 data=infoForm,
    #                 autoCommit=False,
    #             )
    #             if DBAffectRows not in [0, 1]:
    #                 database.rollback()
    #                 raise DatabaseRuntimeError(
    #                     "Delete special authority info error.",
    #                     filename=__file__,
    #                     line=sys._getframe().f_lineno,
    #                 )
    #         DBAffectRows = database.execute(
    #             sql="SELECT * FROM `Work` WHERE student_id=%(ori_group_leader)s;",
    #             data=infoForm,
    #         )
    #         database.fetchall()
    #         if DBAffectRows == 0:
    #             DBAffectRows = database.execute(
    #                 sql="DELETE FROM `Authority` \
    #                     WHERE student_id=%(ori_group_leader)s AND department_id=0 AND actor=0;",
    #                 data=infoForm,
    #                 autoCommit=False,
    #             )
    #             if DBAffectRows not in [0, 1]:
    #                 database.rollback()
    #                 raise DatabaseRuntimeError(
    #                     "Delete special authority info error.",
    #                     filename=__file__,
    #                     line=sys._getframe().f_lineno,
    #                 )
    #         # ========
    #         # 提交事务
    #         database.commit()

    # @staticmethod
    # def downloadFinanceEXCEL(infoForm: dict, db_session: Session | None = None) -> str:
    #     # =====================================
    #     # 如果提供已经建立的数据库连接，则直接使用
    #     session_context = (
    #         SessionLocal() if db_session is None else nullcontext(db_session)
    #     )
    #     with session_context as session:
    #         # ====================================
    #         # 调用python程序处理财务信息并导出财务表
    #         # 整理调用参数
    #         path = f"tmpFiles/finance_EXCEL_{str(int(random.random()*10e5))}.xlsx"
    #         infoForm["path"] = path
    #         infoForm["database"] = database
    #         infoForm["date"] = datetime.datetime.strptime(
    #             infoForm["date"], "%Y-%m")
    #         # 开始调用
    #         FinanceProcess.writedata(**infoForm)
    #         FinanceProcess.SetStyle(path)

    #         return "/"+path
