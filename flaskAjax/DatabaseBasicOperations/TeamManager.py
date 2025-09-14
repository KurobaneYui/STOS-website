import sys
import random
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
import flaskAjax.Program.FinanceProcess as FinanceProcess


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
                session.query(SQL_User, SQL_Blacklist.reason, SQL_Blacklist.start_time)
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
                    "start_time": start_time.isoformat(),
                }
                for idx, (user, reason, start_time) in enumerate(results)
            ]
            return results

    @staticmethod
    def deleteBlocked(infoForm: dict, db_session: Session | None = None) -> None:
        # =====================================
        # 如果提供已经建立的数据库连接，则直接使用
        session_context = (
            SessionLocal() if db_session is None else nullcontext(db_session)
        )
        with session_context as session:
            # ==============
            # 查询黑名单数据
            session.query(SQL_Blacklist).filter_by(
                student_id=infoForm["student_id"]
            ).delete()
            session.commit()

    @staticmethod
    def addBlocked(infoForm: dict, db_session: Session | None = None) -> None:
        # =====================================
        # 如果提供已经建立的数据库连接，则直接使用
        session_context = (
            SessionLocal() if db_session is None else nullcontext(db_session)
        )
        with session_context as session:
            # ==============
            # 查询是否有基本信息条目，没有则添加
            results = (
                session.query(SQL_User)
                .filter_by(student_id=infoForm["student_id"])
                .all()
            )
            if len(results) < 1:
                user = SQL_User(
                    student_id=infoForm["student_id"],
                    gender=infoForm["gender"],
                    name=infoForm["name"],
                )
                session.add(user)
            # ==============
            # 查询是否有基本信息条目，没有则添加，有则更新
            results = (
                session.query(SQL_Blacklist)
                .filter_by(student_id=infoForm["student_id"])
                .all()
            )
            if len(results) < 1:
                blocked_one = SQL_Blacklist(
                    student_id=infoForm["student_id"],
                    reason=infoForm["reason"],
                    start_time=infoForm["date"],
                )
                session.add(blocked_one)
            else:
                results[0].reason = infoForm["reason"]
                results[0].start_time = infoForm["date"]
            session.commit()

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
                    SQL_Group.chazao.label("chazao"),
                    SQL_Group.chake.label("chake"),
                    SQL_Group.datamanager.label("datamanager"),
                    SQL_Group.remark.label("remark"),
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
                    "chazao": row.chazao,
                    "chake": row.chake,
                    "datamanager": row.datamanager,
                    "student_name": row.student_name or "",
                }
                for row in rows
            ]
            return results

    @staticmethod
    def updateDepartment(infoForm: dict, db_session: Session | None = None) -> None:
        # =====================================
        # 如果提供已经建立的数据库连接，则直接使用
        session_context = (
            SessionLocal() if db_session is None else nullcontext(db_session)
        )
        with session_context as session:
            # =================================
            # 如果提供了组长学号，确保存在即可继续
            if infoForm["group_leader_id"] != "":
                results = (
                    session.query(SQL_UserProfile)
                    .filter_by(student_id=infoForm["group_leader_id"])
                    .all()
                )
                if len(results) != 1:
                    raise IllegalValueError(
                        "学号不存在，请检查输入的学号信息。",
                        filename=__file__,
                        line=sys._getframe().f_lineno,
                    )
            infoForm["new_group_leader"] = infoForm["group_leader_id"]
            # ==============
            # 查询原组长信息
            results = (
                session.query(SQL_GroupMember)
                .filter_by(group_id=infoForm["department_id"], role="manager")
                .all()
            )
            if len(results) > 0:
                infoForm["ori_group_leader"] = results[0].student_id
            else:
                infoForm["ori_group_leader"] = ""
            # =======================
            # 确保部门新id不与现有其他部门id重复
            if infoForm["department_id"] != infoForm["old_department_id"]:
                results = (
                    session.query(SQL_Group)
                    .filter_by(id=infoForm["department_id"])
                    .all()
                )
                if len(results) > 0:
                    raise IllegalValueError(
                        "部门ID已存在，请更换其他ID。",
                        filename=__file__,
                        line=sys._getframe().f_lineno,
                    )
            # =======================
            # 更新部门信息
            results = (
                session.query(SQL_Group)
                .filter_by(id=infoForm["old_department_id"])
                .one()
            )
            results.id = infoForm["department_id"]
            results.name = infoForm["department_name"]
            results.chazao = infoForm["chazao"]
            results.chake = infoForm["chake"]
            results.datamanager = infoForm["datamanager"]
            results.remark = infoForm["remark"]
            session.commit()
            # if ??? not in [0, 1]:
            #     database.rollback()
            #     raise DatabaseRuntimeError(
            #         "Update department info error.",
            #         filename=__file__,
            #         line=sys._getframe().f_lineno,
            #     )

            # =======================================
            # 对卸任组长取消岗位信息、权限信息
            if infoForm["ori_group_leader"] != "":
                results = (
                    session.query(SQL_GroupMember)
                    .filter_by(
                        student_id=infoForm["ori_group_leader"],
                        group_id=infoForm["department_id"],
                        role="manager",
                    )
                    .one()
                )
                session.delete(results)
                session.commit()
                # if DBAffectRows not in [0, 1]:
                #     database.rollback()
                #     raise DatabaseRuntimeError(
                #         "Delete work info error.",
                #         filename=__file__,
                #         line=sys._getframe().f_lineno,
                #     )
            # =======================================
            # 对新任组长分配岗位信息、权限信息
            if infoForm["new_group_leader"] != "":
                workInfo = SQL_GroupMember(
                    group_id=infoForm["department_id"],
                    student_id=infoForm["new_group_leader"],
                    role="manager",
                    wage=350,
                    display_title=["组长", "队长"][infoForm["department_id"] == 1],
                    remark="",
                )
                session.add(workInfo)
                # if DBAffectRows not in [0, 1]:
                #     database.rollback()
                #     raise DatabaseRuntimeError(
                #         "Insert work info error.",
                #         filename=__file__,
                #         line=sys._getframe().f_lineno,
                #     )
            # ========
            # 提交事务
            session.commit()

    @staticmethod
    def addDepartment(infoForm: dict, db_session: Session | None = None) -> None:
        # =====================================
        # 如果提供已经建立的数据库连接，则直接使用
        session_context = (
            SessionLocal() if db_session is None else nullcontext(db_session)
        )
        with session_context as session:
            # =================================
            # 如果提供了组长学号，确保存在即可继续
            if infoForm["group_leader_id"] != "":
                results = (
                    session.query(SQL_UserProfile)
                    .filter_by(student_id=infoForm["group_leader_id"])
                    .all()
                )
                if len(results) != 1:
                    raise IllegalValueError(
                        "学号不存在，请检查输入的学号信息。",
                        filename=__file__,
                        line=sys._getframe().f_lineno,
                    )
            # =======================
            # 确保部门新id不与现有其他部门id重复
            results = (
                session.query(SQL_Group).filter_by(id=infoForm["department_id"]).all()
            )
            if len(results) > 0:
                raise IllegalValueError(
                    "部门ID已存在，请更换其他ID。",
                    filename=__file__,
                    line=sys._getframe().f_lineno,
                )
            # =======================
            # 更新部门信息
            group = SQL_Group(
                id=infoForm["department_id"],
                name=infoForm["department_name"],
                chazao=infoForm["chazao"],
                chake=infoForm["chake"],
                datamanager=infoForm["datamanager"],
                remark=infoForm["remark"],
            )
            session.add(group)
            session.commit()
            # if ??? not in [0, 1]:
            #     database.rollback()
            #     raise DatabaseRuntimeError(
            #         "Update department info error.",
            #         filename=__file__,
            #         line=sys._getframe().f_lineno,
            #     )

            # =======================================
            # 对新任组长分配岗位信息、权限信息
            if infoForm["group_leader_id"] != "":
                workInfo = SQL_GroupMember(
                    group_id=infoForm["department_id"],
                    student_id=infoForm["group_leader_id"],
                    role="manager",
                    wage=350,
                    display_title=["组长", "队长"][infoForm["department_id"] == 1],
                    remark="",
                )
                session.add(workInfo)
                # if DBAffectRows not in [0, 1]:
                #     database.rollback()
                #     raise DatabaseRuntimeError(
                #         "Insert work info error.",
                #         filename=__file__,
                #         line=sys._getframe().f_lineno,
                #     )
            # ========
            # 提交事务
            session.commit()

    @staticmethod
    def deleteDepartment(infoForm: dict, db_session: Session | None = None) -> None:
        # =====================================
        # 如果提供已经建立的数据库连接，则直接使用
        session_context = (
            SessionLocal() if db_session is None else nullcontext(db_session)
        )
        with session_context as session:
            # =================================
            # 查询是否存在所在组，如果存在连带组长组员信息一并删除
            results = (
                session.query(SQL_Group).filter_by(id=infoForm["department_id"]).all()
            )
            if len(results) < 1:
                raise IllegalValueError(
                    "部门ID不存在，请检查输入的部门ID信息。",
                    filename=__file__,
                    line=sys._getframe().f_lineno,
                )
            elif len(results) > 1:
                raise DatabaseRuntimeError(
                    "Department ID duplicate error.",
                    filename=__file__,
                    line=sys._getframe().f_lineno,
                )
            session.delete(results[0])
            # ========
            # 提交事务
            session.commit()

    @staticmethod
    def downloadFinanceEXCEL(infoForm: dict, db_session: Session | None = None) -> str:
        # =====================================
        # 如果提供已经建立的数据库连接，则直接使用
        session_context = (
            SessionLocal() if db_session is None else nullcontext(db_session)
        )
        with session_context as session:
            # ====================================
            # 调用python程序处理财务信息并导出财务表
            # 整理调用参数
            path = f"tmpFiles/finance_EXCEL_{str(int(random.random() * 10e5))}.xlsx"
            infoForm["path"] = path
            infoForm["database"] = session
            # 开始调用
            FinanceProcess.writedata(**infoForm)
            FinanceProcess.SetStyle(path)

            return "/" + path
