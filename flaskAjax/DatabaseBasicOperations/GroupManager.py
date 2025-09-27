import sys
import datetime
import json
from flask import Request
from sqlalchemy.orm import Session
from contextlib import nullcontext
from collections import defaultdict
from flaskAjax.BaseComponents.DatabaseConnector import (
    SQL_User,
    SQL_UserProfile,
    SQL_EmptyTime,
    SQL_Blacklist,
    SQL_Group,
    SQL_GroupMember,
    SQL_Campus,
    SQL_College,
    SQL_Classroom,
    SQL_StudySchedule,
    SQL_CheckInTask,
    SQL_CheckInData,
    SessionLocal,
)
from flaskAjax.BaseComponents.CustomSession import CustomSession
from flaskAjax.BaseComponents.CustomError import (
    DatabaseRuntimeError,
    IllegalValueError,
    PermissionDenyError,
)


class GroupManagerDatabase:
    @staticmethod
    def getAllGroupsMembers(db_session: Session | None = None) -> dict:
        # =====================================
        # 如果提供已经建立的数据库连接，则直接使用
        session_context = (
            SessionLocal() if db_session is None else nullcontext(db_session)
        )
        with session_context as session:
            # =================================================================
            # 获取管理的部门，如果是队长组，直接获取所有部门，如果不是则获取管理的组
            if CustomSession().getSession()["department_id"] == 1:
                results = session.query(SQL_Group).all()
                groupsInManagement = [
                    {"department_id": i.id, "department_name": i.name} for i in results
                ]
            else:
                results = (
                    session.query(SQL_GroupMember)
                    .filter_by(
                        role="manager",
                        student_id=CustomSession().getSession()["userID"],
                        group_id=CustomSession().getSession()["department_id"],
                    )
                    .all()
                )
                groupsInManagement = [
                    {"department_id": i.group_id, "department_name": i.group.name}
                    for i in results
                ]
            # =================
            # 搜索管理的组的组员
            results = dict()
            for groupInfo in groupsInManagement:
                if groupInfo["department_id"] in results.keys():
                    continue
                members = (
                    session.query(SQL_GroupMember, SQL_User)
                    .join(SQL_User, SQL_User.student_id == SQL_GroupMember.student_id)
                    .filter(
                        SQL_GroupMember.role == "member",
                        SQL_GroupMember.group_id == groupInfo["department_id"],
                    )
                    .all()
                )
                members = [
                    {
                        "student_id": u.student_id,
                        "student_name": u.name,
                        "gender": u.gender,
                        "department_name": g.group.name,
                        "department_id": g.group_id,
                    }
                    for (g, u) in members
                ]
                results[groupInfo["department_id"]] = {
                    "group_name": groupInfo["department_name"],
                    "members": members,
                }
            # ========
            # 返回内容
            return results

    @staticmethod
    def searchMember(
        infoForm: dict, db_session: Session | None = None
    ) -> tuple[dict] | list[dict]:
        # =====================================
        # 如果提供已经建立的数据库连接，则直接使用
        session_context = (
            SessionLocal() if db_session is None else nullcontext(db_session)
        )
        with session_context as session:
            # =============================
            # 查询提供的学号列表对应的学生信息
            student_ids = [i.strip() for i in infoForm["student_ids"].split(",")]
            results = (
                session.query(SQL_UserProfile, SQL_User.name, SQL_User.gender)
                .join(SQL_User, SQL_User.student_id == SQL_UserProfile.student_id)
                .filter(SQL_UserProfile.student_id.in_(student_ids))
                .all()
            )
            results = [
                {"student_id": profile.student_id, "name": name, "gender": gender}
                for (profile, name, gender) in results
            ]
            return results

    @staticmethod
    def addMember(infoForm: dict, db_session: Session | None = None) -> None:
        # =====================================
        # 如果提供已经建立的数据库连接，则直接使用
        session_context = (
            SessionLocal() if db_session is None else nullcontext(db_session)
        )
        with session_context as session:
            # =============
            # 提取学号和组号
            student_id = infoForm["student_id"]
            group_id = infoForm["group_id"]
            # ============
            # 确认学号存在，组号存在
            results = (
                session.query(SQL_UserProfile).filter_by(student_id=student_id).all()
            )
            if len(results) < 1:
                raise IllegalValueError(
                    "不存在该学号成员",
                    filename=__file__,
                    line=sys._getframe().f_lineno,
                )
            if len(results) > 1:
                raise DatabaseRuntimeError(
                    "匹配多条成员数据，请联系管理员处理",
                    filename=__file__,
                    line=sys._getframe().f_lineno,
                )
            results = session.query(SQL_Group).filter_by(id=group_id).all()
            if len(results) < 1:
                raise IllegalValueError(
                    "不存在该组号",
                    filename=__file__,
                    line=sys._getframe().f_lineno,
                )
            if len(results) > 1:
                raise DatabaseRuntimeError(
                    "组号匹配多条小组信息，请联系管理员处理",
                    filename=__file__,
                    line=sys._getframe().f_lineno,
                )
            # ============
            # 更新岗位信息
            # DBAffectedRows = database.execute(
            #     sql="INSERT IGNORE INTO Work (student_id,department_id,job,wage,remark) \
            #         VALUES (%s,%s,%s,%s,%s);",
            #     data=(student_id, group_id, 0, 300, ""),
            #     autoCommit=False,
            # )
            results = (
                session.query(SQL_GroupMember)
                .filter_by(student_id=student_id, group_id=group_id, role="member")
                .all()
            )
            if len(results) == 0:
                work = SQL_GroupMember(
                    group_id=group_id,
                    student_id=student_id,
                    role="member",
                    wage=300,
                    display_title="副队长" if int(group_id) == 1 else "组员",
                )
                session.add(work)
            # if DBAffectedRows not in [0, 1]:
            #     database.rollback()
            #     raise DatabaseRuntimeError(
            #         "Insert work info error.",
            #         filename=__file__,
            #         line=sys._getframe().f_lineno,
            #     )
            # # ===========
            # # 调整扣分信息
            # DBAffectRows = database.execute(
            #     sql="INSERT IGNORE INTO `Score` (student_id,department_id,job,date,reason,variant) VALUES \
            #         (%s,%s,%s,%s,'岗位初始',5);",
            #     data=(student_id, group_id, 0, datetime.datetime.now().date()),
            #     autoCommit=False,
            # )
            # if DBAffectRows not in [0, 1]:
            #     database.rollback()
            #     raise DatabaseRuntimeError(
            #         "Insert score info error.",
            #         filename=__file__,
            #         line=sys._getframe().f_lineno,
            #     )
            # ========
            # 提交事务
            session.commit()

    @staticmethod
    def removeMember(infoForm: dict, db_session: Session | None = None) -> None:
        # =====================================
        # 如果提供已经建立的数据库连接，则直接使用
        session_context = (
            SessionLocal() if db_session is None else nullcontext(db_session)
        )
        with session_context as session:
            # =============
            # 提取学号和组号
            student_id = infoForm["student_id"]
            group_id = infoForm["group_id"]
            # ============
            # 删除岗位信息
            # DBAffectedRows = database.execute(
            #     sql="DELETE FROM Work WHERE student_id=%s AND department_id=%s AND job=0;",
            #     data=(student_id, group_id),
            #     autoCommit=False,
            # )
            results = (
                session.query(SQL_GroupMember)
                .filter_by(student_id=student_id, group_id=group_id, role="member")
                .all()
            )
            if len(results) < 1:
                raise IllegalValueError(
                    "组号错误，或组内不存在该学号组员",
                    filename=__file__,
                    line=sys._getframe().f_lineno,
                )
            if len(results) > 1:
                raise DatabaseRuntimeError(
                    "组内匹配多条此组员数据，请联系管理员处理",
                    filename=__file__,
                    line=sys._getframe().f_lineno,
                )
            # if DBAffectedRows not in [0, 1]:
            #     database.rollback()
            #     raise DatabaseRuntimeError(
            #         "Delete work info error.",
            #         filename=__file__,
            #         line=sys._getframe().f_lineno,
            #     )
            session.delete(results[0])
            # ========
            # 提交事务
            session.commit()

    @staticmethod
    def getGroupSelfstudyCheckData(
        db_session: Session | None = None,
    ) -> dict:
        # =====================================
        # 如果提供已经建立的数据库连接，则直接使用
        session_context = (
            SessionLocal() if db_session is None else nullcontext(db_session)
        )
        with session_context as session:
            # ================================
            # 获取当日日期后3日日期，和前7日日期
            past7Date = datetime.date.today() - datetime.timedelta(days=7)
            post3Date = datetime.date.today() + datetime.timedelta(days=3)

            days_diff = (post3Date - past7Date).days
            # 生成日期列表（新到旧）
            date_list = [
                post3Date - datetime.timedelta(days=x) for x in range(days_diff + 1)
            ]
            # ================================================
            # 获取登录组，如果是队长则获取所有安排查早权限的组
            session_info = CustomSession.getSession()
            if session_info["department_id"] == 1:
                # 大队长：所有查早组
                results = (
                    session.query(SQL_Group.id, SQL_Group.name)
                    .filter(SQL_Group.chazao)
                    .all()
                )
                department_list = [
                    {"group_id": group_id, "group_name": group_name}
                    for (group_id, group_name) in results
                ]
            elif session_info["department_id"] != 0:
                # 普通组员: 仅有查早权限的自己组
                results = (
                    session.query(SQL_Group.id, SQL_Group.name)
                    .filter(
                        SQL_Group.chazao,
                        SQL_Group.id == session_info["department_id"],
                    )
                    .all()
                )
                if len(results) != 1:
                    raise PermissionDenyError(
                        "没有权限查看组内提交早自习数据.",
                        filename=__file__,
                        line=sys._getframe().f_lineno,
                    )
                department_list = [
                    {"group_id": group_id, "group_name": group_name}
                    for (group_id, group_name) in results
                ]
            else:
                raise PermissionDenyError(
                    "没有权限查看组内提交早自习数据.",
                    filename=__file__,
                    line=sys._getframe().f_lineno,
                )
            # ====================================================
            # 对每个登录组，按日期顺序获取对应日期的组员排班和数据
            results = dict()
            # 遍历所有可见部门
            for dep in department_list:
                group_id = dep["group_id"]
                group_name = dep["group_name"]
                data_per_date = dict()
                # 查出当前组在时间段内的所有排班数据
                study_datas = (
                    session.query(
                        SQL_CheckInTask.id,
                        SQL_StudySchedule,
                        SQL_User,
                        SQL_Classroom,
                        SQL_CheckInData,
                    )
                    .join(SQL_StudySchedule, SQL_CheckInTask.schedule)
                    .join(SQL_User, SQL_User.student_id == SQL_CheckInTask.student_id)
                    .join(
                        SQL_Classroom,
                        SQL_StudySchedule.classroom,
                    )
                    .filter(
                        SQL_CheckInTask.group_id == group_id,
                        SQL_StudySchedule.date >= past7Date,
                        SQL_StudySchedule.date <= post3Date,
                    )
                    .outerjoin(
                        SQL_CheckInData, SQL_CheckInData.task_id == SQL_CheckInTask.id
                    )
                    .order_by(SQL_StudySchedule.date, SQL_Classroom.id)
                    .all()
                )

                for i in date_list:
                    data_per_date[i.isoformat()] = list()
                for task_id, schedule, user, classroom, checkin_data in study_datas:
                    data_per_date[schedule.date.isoformat()].append(
                        {
                            "date": schedule.date.isoformat(),
                            "task_remark": schedule.remark,
                            "task_id": task_id,
                            "expected_headcount": schedule.expected_headcount,
                            "first_count": checkin_data.first_count
                            if checkin_data
                            else None,
                            "second_count": checkin_data.second_count
                            if checkin_data
                            else None,
                            "leave": checkin_data.leave if checkin_data else None,
                            "late": checkin_data.late if checkin_data else None,
                            "absentee": checkin_data.absentee if checkin_data else None,
                            "early_leave": checkin_data.early_leave
                            if checkin_data
                            else None,
                            "absent_list": json.loads(checkin_data.absent_list)
                            if checkin_data
                            else None,
                            "leave_list": json.loads(checkin_data.leave_list)
                            if checkin_data
                            else None,
                            "remarks": checkin_data.remarks if checkin_data else None,
                            "status": checkin_data.status if checkin_data else None,
                            "student_name": user.name,
                            "student_id": user.student_id,
                            "classroom_id": classroom.id,
                            "classroom_building": classroom.building,
                            "classroom_area": classroom.area,
                            "classroom_room_number": classroom.room_number,
                            "classroom_capacity": classroom.capacity,
                        }
                    )
                results[group_id] = {
                    "department_id": group_id,
                    "department_name": group_name,
                    "data": data_per_date,
                }

            # ========
            # 返回数据
            return results

    @staticmethod
    def submitSelfstudyRecordRecheck(
        infoForm: dict, db_session: Session | None = None
    ) -> None:
        # =====================================
        # 如果提供已经建立的数据库连接，则直接使用
        session_context = (
            SessionLocal() if db_session is None else nullcontext(db_session)
        )
        with session_context as session:
            # ===============================================
            # 查找早自习排班编号对应的查早组是否为当前登录组
            department_id = CustomSession().getSession()["department_id"]  # 登录用户ID
            task_id = infoForm.get("task_id")
            checkin_task = (
                session.query(SQL_CheckInTask)
                .filter(SQL_CheckInTask.id == task_id)
                .one_or_none()
            )
            if not checkin_task:
                raise IllegalValueError(
                    "无效的早自习任务编号",
                    filename=__file__,
                    line=sys._getframe().f_lineno,
                )
            if checkin_task.group_id != department_id and department_id != 1:
                raise PermissionDenyError(
                    "无权确认其他组任务的数据",
                    filename=__file__,
                    line=sys._getframe().f_lineno,
                )
            # ============
            # 删除已有数据
            existed_data = (
                session.query(SQL_CheckInData)
                .filter(SQL_CheckInData.task_id == task_id)
                .one_or_none()
            )
            if existed_data:
                session.delete(existed_data)
                session.flush()  # 立即删除避免唯一限制冲突
            # ============
            # 整合已有数据
            absent_list_str = json.dumps(
                infoForm.get("absent_list", []), ensure_ascii=False
            )
            leave_list_str = json.dumps(
                infoForm.get("leave_list", []), ensure_ascii=False
            )
            check_data = SQL_CheckInData(
                task_id=task_id,
                first_count=infoForm["record"].get("first_count", 0),
                second_count=infoForm["record"].get("second_count", 0),
                leave=infoForm["record"].get("leave", 0),
                late=infoForm["record"].get("late", 0),
                absentee=infoForm["record"].get("absentee", 0),
                early_leave=infoForm["record"].get("early_leave", 0),
                absent_list=absent_list_str,
                leave_list=leave_list_str,
                remarks=infoForm["record"].get("remarks", ""),
                status="checked",
            )

            # ========
            # 提交数据
            session.add(check_data)
            session.commit()

    # @staticmethod
    # def getXianchangGroupList(
    #     databaseConnector: DatabaseConnector | None = None,
    # ) -> list:
    #     # =====================================
    #     # 如果提供已经建立的数据库连接，则直接使用
    #     if databaseConnector is None:
    #         database = DatabaseConnector()
    #         database.startCursor()
    #     else:
    #         database = databaseConnector
    #     # ============
    #     # 查询部门信息
    #     if CustomSession().getSession()["department_id"] == 1:
    #         _ = database.execute(
    #             sql="SELECT department_id, Department.name as department_name \
    #                 FROM Department \
    #                 WHERE Department.name like %s;",
    #             data=("现场组%",),
    #         )
    #     else:
    #         _ = database.execute(
    #             sql="SELECT department_id, Department.name as department_name \
    #                 FROM Department \
    #                 WHERE Department.name like %s AND department_id = %s;",
    #             data=("现场组%", CustomSession().getSession()["department_id"]),
    #         )
    #     return database.fetchall()

    # @staticmethod
    # def getGroupCoursesCheckData(
    #     infoForm: dict, databaseConnector: DatabaseConnector | None = None
    # ) -> list:
    #     # =====================================
    #     # 如果提供已经建立的数据库连接，则直接使用
    #     if databaseConnector is None:
    #         database = DatabaseConnector()
    #         database.startCursor()
    #     else:
    #         database = databaseConnector
    #     # ================================
    #     # 获取当日日期后3日日期，和前7日日期
    #     past7Date = datetime.datetime.now() - datetime.timedelta(days=7)
    #     post3Date = datetime.datetime.now() + datetime.timedelta(days=3)
    #     date_list = [
    #         d.strftime("%Y-%m-%d")
    #         for d in (
    #             past7Date + datetime.timedelta(days=x)
    #             for x in range((post3Date - past7Date).days + 1)
    #         )
    #     ]
    #     date_list.reverse()
    #     # =======================
    #     # 检查给定的组符合查看要求
    #     # 并整理查看组的信息
    #     showDepartmentInfo = dict()
    #     for canShowDepartmentInfo in GroupManagerDatabase.getXianchangGroupList(
    #         databaseConnector
    #     ):
    #         showDepartmentInfo[canShowDepartmentInfo["department_id"]] = (
    #             canShowDepartmentInfo["department_name"]
    #         )
    #     delete_department_ids = []
    #     for department_id in showDepartmentInfo.keys():
    #         if department_id not in infoForm["group_id_list"]:
    #             delete_department_ids.append(department_id)

    #     for department_id in delete_department_ids:
    #         del showDepartmentInfo[department_id]

    #     # 先按日期时段顺序
    #     # 再按组和名字顺序
    #     # 再列出所有成员的所有数据
    #     results = list()
    #     # ===================
    #     # 按日期和时段获取数据
    #     for date in date_list:
    #         for period in ["1-2", "3-4", "5-6", "7-8"]:
    #             results.append({"date": date, "period": period, "data": list()})
    #             # ====================================
    #             # 对每个查看组，按组员顺序获取排班和数据
    #             for department_id, department_name in showDepartmentInfo.items():
    #                 results[-1]["data"].append(
    #                     {
    #                         "department_id": department_id,
    #                         "department_name": department_name,
    #                         "data": list(),
    #                     }
    #                 )
    #                 DBAffectedRows = database.execute(
    #                     sql="SELECT course_id, course_name, classroom_name, grade, course_order, campus, school_name, student_supposed, actual_student_id, actual_student_name \
    #                         FROM CourseCheckActualView \
    #                         WHERE CourseCheckActualView.actual_student_department_id = %s \
    #                             AND CourseCheckActualView.date = %s \
    #                             AND CourseCheckActualView.period = %s;",
    #                     data=(department_id, date, period),
    #                 )
    #                 results[-1]["data"][-1]["data"] = database.fetchall()
    #                 # =======================
    #                 # 对每一个排班获取提交数据
    #                 for one_schedules in results[-1]["data"][-1]["data"]:
    #                     course_id = one_schedules["course_id"]
    #                     # ======
    #                     # 数据表
    #                     DBAffectedRows = database.execute(
    #                         sql="SELECT coursecheckdata_id, check_result, groupleader_recheck, remark \
    #                             FROM CourseCheckData \
    #                             WHERE course_id = %s \
    #                             ORDER BY submission_time DESC \
    #                             LIMIT 1;",
    #                         data=(course_id,),
    #                     )
    #                     info = database.fetchall()
    #                     if DBAffectedRows == 0:
    #                         one_schedules.update(
    #                             {
    #                                 "coursecheckdata_id": -1,
    #                                 "record": "{}",
    #                                 "recheck_remark": "",
    #                                 "recheck": False,
    #                                 "submitted": False,
    #                             }
    #                         )
    #                     else:
    #                         one_schedules.update(
    #                             {
    #                                 "coursecheckdata_id": info[0]["coursecheckdata_id"],
    #                                 "record": info[0]["check_result"],
    #                                 "recheck_remark": info[0]["remark"],
    #                                 "recheck": int(info[0]["groupleader_recheck"]) == 1,
    #                                 "submitted": True,
    #                             }
    #                         )
    #                 # =====================
    #                 # 如果没有数据则删除条目
    #                 if len(results[-1]["data"][-1]["data"]) == 0:
    #                     del results[-1]["data"][-1]
    #             # =====================
    #             # 如果没有数据则删除条目
    #             if len(results[-1]["data"]) == 0:
    #                 del results[-1]
    #     # ========
    #     # 返回数据
    #     return results

    # @staticmethod
    # def submitCoursesRecordRecheck(
    #     infoForm: dict, databaseConnector: DatabaseConnector | None = None
    # ) -> None:
    #     # =====================================
    #     # 如果提供已经建立的数据库连接，则直接使用
    #     if databaseConnector is None:
    #         database = DatabaseConnector()
    #         database.startCursor()
    #     else:
    #         database = databaseConnector
    #     # =============================
    #     # 核验查课编号和查课数据编号匹配
    #     DBAffectedRows = database.execute(
    #         sql="SELECT coursecheckdata_id \
    #             FROM CourseCheckData \
    #             WHERE coursecheckdata_id = %(coursecheckdata_id)s \
    #                 AND course_id = %(course_id)s;",
    #         data=infoForm,
    #     )
    #     database.fetchall()
    #     if DBAffectedRows == 0:
    #         raise PermissionDenyError(
    #             "查课数据编号和查课排班编号不匹配.",
    #             filename=__file__,
    #             line=sys._getframe().f_lineno,
    #         )
    #     # ============================
    #     # 核验查课编号对应组员为本组组员
    #     DBAffectedRows = database.execute(
    #         sql="SELECT course_id \
    #             FROM CourseCheckActualView \
    #             WHERE course_id = %s \
    #                 AND actual_student_department_id = %s;",
    #         data=(infoForm["course_id"], CustomSession.getSession()["department_id"]),
    #     )
    #     database.fetchall()
    #     if DBAffectedRows == 0:
    #         raise PermissionDenyError(
    #             "数据对应查课排班的组员不属于本组.",
    #             filename=__file__,
    #             line=sys._getframe().f_lineno,
    #         )
    #     # ============
    #     # 提交确认信息
    #     DBAffectedRows = database.execute(
    #         sql="UPDATE CourseCheckData \
    #             SET groupleader_recheck = %(rechecked)s, \
    #                 remark = %(recheck_remark)s \
    #             WHERE coursecheckdata_id = %(coursecheckdata_id)s;",
    #         data=infoForm,
    #         autoCommit=True,
    #     )

    @staticmethod
    def getGroupEmptyTable(db_session: Session | None = None) -> list:
        # =====================================
        # 如果提供已经建立的数据库连接，则直接使用
        session_context = (
            SessionLocal() if db_session is None else nullcontext(db_session)
        )
        with session_context as session:
            # =========================================
            # 获取登录组id，并以此获取组员学号姓名和空课表
            groupId = CustomSession().getSession()["department_id"]
            results = (
                session.query(SQL_User)
                .join(
                    SQL_GroupMember,
                    SQL_GroupMember.student_id == SQL_User.student_id,
                )
                .filter(SQL_GroupMember.group_id == groupId)
                .all()
            )
            results = [
                {
                    "student_name": i.name,
                    "student_id": i.student_id,
                    "mon": i.profile.empty_time.mon,
                    "tue": i.profile.empty_time.tue,
                    "wed": i.profile.empty_time.wed,
                    "thu": i.profile.empty_time.thu,
                    "fri": i.profile.empty_time.fri,
                    "sat": i.profile.empty_time.sat,
                    "sun": i.profile.empty_time.sun,
                }
                for i in results
            ]
            return results

    @staticmethod
    def setMemberEmptyTable(infoForm: dict, db_session: Session | None = None) -> None:
        # =====================================
        # 如果提供已经建立的数据库连接，则直接使用
        session_context = (
            SessionLocal() if db_session is None else nullcontext(db_session)
        )
        with session_context as session:
            # =========================
            # 确认修改成员是登录组的成员
            groupId = CustomSession().getSession()["department_id"]
            results = (
                session.query(SQL_GroupMember)
                .filter_by(student_id=infoForm["student_id"], group_id=groupId)
                .all()
            )
            if len(results) == 0:
                raise PermissionDenyError(
                    "只能修改登录组组员的空课表.",
                    filename=__file__,
                    line=sys._getframe().f_lineno,
                )
            # ==============
            # 获取某日空课表
            results = (
                session.query(SQL_EmptyTime)
                .filter_by(student_id=infoForm["student_id"])
                .all()
            )
            if len(results) == 0:
                raise IllegalValueError(
                    "查询不到组员指定的空课表.",
                    filename=__file__,
                    line=sys._getframe().f_lineno,
                )
            # ==========
            # 修改对应位
            oldStr = getattr(results[0], infoForm["weekName"])
            oldVal = int(oldStr[infoForm["timePeriodOrder"]])
            # evenOrNot==False表示单（操作bit0），True表示双（操作bit1）
            bit = 1 if infoForm["evenOrNot"] else 0
            if infoForm["emptyOrNot"]:
                # 置1
                newVal = oldVal | (1 << bit)
            else:
                # 置0
                newVal = oldVal & ~(1 << bit)
            newStr = (
                oldStr[: infoForm["timePeriodOrder"]]
                + str(newVal)
                + oldStr[infoForm["timePeriodOrder"] + 1 :]
            )
            setattr(results[0], infoForm["weekName"], newStr)
            # ========
            # 提交修改
            session.commit()
