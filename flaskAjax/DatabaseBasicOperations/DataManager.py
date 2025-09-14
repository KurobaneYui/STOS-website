import re
import sys
import time
import random
import datetime
from sqlalchemy import desc as DESC, or_ as OR, func
from sqlalchemy.orm import Session
from contextlib import nullcontext
from flask import Request
from flaskAjax.BaseComponents.Authorization import SQL_GroupMember
from flaskAjax.BaseComponents.CustomError import DatabaseRuntimeError, IllegalValueError
from flaskAjax.BaseComponents.DatabaseConnector import (
    SQL_Campus,
    SQL_CheckInTask,
    SQL_Classroom,
    SQL_College,
    SQL_Group,
    SQL_User,
    SQL_UserProfile,
    SQL_StudySchedule,
    SessionLocal,
)
# import Program.python.SelfstudyExportProcess as SelfstudyExportProcess
# import Program.python.CourseExportProcess as CourseExportProcess
# import Program.python.EmptyTimeTableProcess as EmptyTimeTableProcess


class DataManagerDatabase:
    @staticmethod
    def getCampus(
        db_session: Session | None = None,
    ) -> tuple[dict] | list[dict]:
        # =====================================
        # 如果提供已经建立的数据库连接，则直接使用
        session_context = (
            SessionLocal() if db_session is None else nullcontext(db_session)
        )
        with session_context as session:
            # ============
            # 查询校区列表
            results = session.query(SQL_Campus).all()
            results = [{"campus": i.name} for i in results]
            return results

    @staticmethod
    def getClassroom(
        db_session: Session | None = None,
    ) -> tuple[dict] | list[dict]:
        # =====================================
        # 如果提供已经建立的数据库连接，则直接使用
        session_context = (
            SessionLocal() if db_session is None else nullcontext(db_session)
        )
        with session_context as session:
            # ================================================
            # 获取教室信息：楼、区域、教室编号和座位容纳量
            results = session.query(SQL_Classroom).all()
            results = [
                {
                    "id": i.id,
                    "campus": i.campus.name,
                    "building": i.building,
                    "area": i.area,
                    "room_number": i.room_number,
                    "capacity": i.capacity,
                }
                for i in results
            ]
            return results

    @staticmethod
    def deleteClassroom(
        infoForm: dict,
        db_session: Session | None = None,
    ) -> None:
        # =====================================
        # 如果提供已经建立的数据库连接，则直接使用
        session_context = (
            SessionLocal() if db_session is None else nullcontext(db_session)
        )
        with session_context as session:
            # ================================================
            # 确认教室信息是否存在
            results = session.query(SQL_Classroom).filter_by(id=infoForm["id"]).all()
            if len(results) < 1:
                raise IllegalValueError(
                    "教室ID不存在，请确认",
                    filename=__file__,
                    line=sys._getframe().f_lineno,
                )
            if len(results) > 1:
                raise DatabaseRuntimeError(
                    "classroom id duplicated error",
                    filename=__file__,
                    line=sys._getframe().f_lineno,
                )
            session.delete(results[0])
            # ========
            # 提交修改
            session.commit()

    @staticmethod
    def addClassroom(
        infoForm: dict,
        db_session: Session | None = None,
    ) -> None:
        # =====================================
        # 如果提供已经建立的数据库连接，则直接使用
        session_context = (
            SessionLocal() if db_session is None else nullcontext(db_session)
        )
        with session_context as session:
            # ================================================
            # 确保新添加教室ID不与现有重复
            results = session.query(SQL_Classroom).filter_by(id=infoForm["id"]).all()
            if len(results) > 0:
                raise IllegalValueError(
                    "教室ID重复，请确认",
                    filename=__file__,
                    line=sys._getframe().f_lineno,
                )
            # ====================
            # 获取学院ID和名称与校区
            results = session.query(SQL_Campus).filter_by(name=infoForm["campus"]).all()
            if len(results) != 1:
                raise IllegalValueError(
                    "校区名称不存在或不唯一，请确认",
                    filename=__file__,
                    line=sys._getframe().f_lineno,
                )
            campus = results[0]
            session.add(
                SQL_Classroom(
                    id=infoForm["id"],
                    campus_id=campus.id,
                    building=infoForm["building"],
                    area=infoForm["area"],
                    room_number=infoForm["room_number"],
                    capacity=infoForm["capacity"],
                )
            )
            # ========
            # 提交修改
            session.commit()

    @staticmethod
    def updateClassroom(
        infoForm: dict,
        db_session: Session | None = None,
    ) -> None:
        # =====================================
        # 如果提供已经建立的数据库连接，则直接使用
        session_context = (
            SessionLocal() if db_session is None else nullcontext(db_session)
        )
        with session_context as session:
            # ================================================
            # 确保更新的教室ID不与现有重复
            if infoForm["old_id"] != infoForm["id"]:
                results = (
                    session.query(SQL_Classroom).filter_by(id=infoForm["id"]).all()
                )
                if len(results) > 0:
                    raise IllegalValueError(
                        "教室新ID重复，请确认",
                        filename=__file__,
                        line=sys._getframe().f_lineno,
                    )
            results = (
                session.query(SQL_Classroom).filter_by(id=infoForm["old_id"]).all()
            )
            if len(results) < 1:
                raise IllegalValueError(
                    "教室原有ID不存在，请确认",
                    filename=__file__,
                    line=sys._getframe().f_lineno,
                )
            if len(results) > 1:
                raise DatabaseRuntimeError(
                    "classroom id duplicated error",
                    filename=__file__,
                    line=sys._getframe().f_lineno,
                )
            classroom = results[0]
            # ====================
            # 获取学院ID和名称与校区
            results = session.query(SQL_Campus).filter_by(name=infoForm["campus"]).all()
            if len(results) != 1:
                raise IllegalValueError(
                    "校区名称不存在或不唯一，请确认",
                    filename=__file__,
                    line=sys._getframe().f_lineno,
                )
            campus = results[0]
            # ====================
            # 更新信息
            classroom.id = infoForm["id"]
            classroom.campus_id = campus.id
            classroom.building = infoForm["building"]
            classroom.area = infoForm["area"]
            classroom.room_number = infoForm["room_number"]
            classroom.capacity = infoForm["capacity"]
            # ========
            # 提交修改
            session.commit()

    @staticmethod
    def getSchool(
        db_session: Session | None = None,
    ) -> tuple[dict] | list[dict]:
        # =====================================
        # 如果提供已经建立的数据库连接，则直接使用
        session_context = (
            SessionLocal() if db_session is None else nullcontext(db_session)
        )
        with session_context as session:
            # ====================
            # 获取学院ID和名称与校区
            results = session.query(SQL_College).all()
            return [{"school_id": i.id, "name": i.name} for i in results]

    @staticmethod
    def deleteSchool(infoForm: dict, db_session: Session | None = None) -> None:
        # =====================================
        # 如果提供已经建立的数据库连接，则直接使用
        session_context = (
            SessionLocal() if db_session is None else nullcontext(db_session)
        )
        with session_context as session:
            # ================================================
            # 检查学院id是否存在
            results = (
                session.query(SQL_College).filter_by(id=infoForm["school_id"]).all()
            )
            if len(results) < 1:
                raise IllegalValueError(
                    "学院ID不存在，请确认",
                    filename=__file__,
                    line=sys._getframe().f_lineno,
                )
            if len(results) > 1:
                raise DatabaseRuntimeError(
                    "College ID duplicated error.",
                    filename=__file__,
                    line=sys._getframe().f_lineno,
                )
            session.delete(results[0])
            session.commit()

    @staticmethod
    def addSchool(infoForm: dict, db_session: Session | None = None) -> None:
        # =====================================
        # 如果提供已经建立的数据库连接，则直接使用
        session_context = (
            SessionLocal() if db_session is None else nullcontext(db_session)
        )
        with session_context as session:
            # ================================================
            # 检查学院id是否重复
            results = (
                session.query(SQL_College).filter_by(id=infoForm["school_id"]).all()
            )
            if len(results) > 0:
                raise IllegalValueError(
                    "学院ID已存在，请确认",
                    filename=__file__,
                    line=sys._getframe().f_lineno,
                )
            # ==========
            # 插入新学院
            school = SQL_College(id=infoForm["school_id"], name=infoForm["name"])
            session.add(school)
            # ========
            # 提交修改
            session.commit()

    @staticmethod
    def updateSchool(infoForm: dict, db_session: Session | None = None) -> None:
        # =====================================
        # 如果提供已经建立的数据库连接，则直接使用
        session_context = (
            SessionLocal() if db_session is None else nullcontext(db_session)
        )
        with session_context as session:
            # ===============
            # 检查学院是否存在
            results = (
                session.query(SQL_College).filter_by(id=infoForm["school_id"]).all()
            )
            if infoForm["school_id"] != infoForm["old_school_id"] and len(results) != 0:
                raise IllegalValueError(
                    "学院 ID 已存在，请检查输入避免重复。",
                    filename=__file__,
                    line=sys._getframe().f_lineno,
                )
            # ============
            # 更新学院信息
            results = (
                session.query(SQL_College).filter_by(id=infoForm["old_school_id"]).one()
            )
            results.id = infoForm["school_id"]
            results.name = infoForm["name"]
            # ========
            # 提交修改
            session.commit()

    @staticmethod
    def getSubmittedSelfstudyDate(
        db_session: Session | None = None,
    ) -> tuple[dict] | list[dict]:
        # =====================================
        # 如果提供已经建立的数据库连接，则直接使用
        session_context = (
            SessionLocal() if db_session is None else nullcontext(db_session)
        )
        with session_context as session:
            # =======================
            # 获取提交的早自习检查安排
            results = (
                session.query(SQL_StudySchedule.date)
                .distinct()  # 应用 DISTINCT 关键字
                .order_by(DESC(SQL_StudySchedule.date))
                .limit(15)  # 限制结果为前15条
                .all()  # 执行查询并获取所有结果
            )
            results = [{"date": i.isoformat()} for (i,) in results]
            return results

    @staticmethod
    def getSelfstudyClassroomDetails(
        infoForm: dict, db_session: Session | None = None
    ) -> tuple[dict] | list[dict]:
        # =====================================
        # 如果提供已经建立的数据库连接，则直接使用
        session_context = (
            SessionLocal() if db_session is None else nullcontext(db_session)
        )
        with session_context as session:
            # =======================
            # 获取提交的早自习教室详情
            results = (
                session.query(
                    SQL_Campus.name.label("campus"),
                    SQL_Classroom.id.label("classroom_id"),
                    SQL_Classroom.building,
                    SQL_Classroom.area,
                    SQL_Classroom.room_number.label("room"),
                    SQL_Classroom.capacity.label("capacity"),
                    SQL_College.id.label("school_id"),
                    SQL_College.name.label("school_name"),
                    SQL_StudySchedule.id.label("selfstudy_id"),
                    SQL_StudySchedule.expected_headcount.label("student_supposed"),
                    SQL_StudySchedule.remark,
                )
                .join(SQL_Classroom, SQL_StudySchedule.classroom_id == SQL_Classroom.id)
                .join(
                    SQL_Campus, SQL_Classroom.campus_id == SQL_Campus.id
                )  # 添加Campus的join
                .join(
                    SQL_College,
                    SQL_StudySchedule.college_id == SQL_College.id,
                    isouter=True,
                )
                .filter(SQL_StudySchedule.date == infoForm["date"])
                .order_by(
                    SQL_Classroom.id,
                    SQL_Classroom.building,
                    SQL_Classroom.area,
                    SQL_Classroom.room_number,
                )
            ).all()
            resutls = [
                {
                    "campus": campus,
                    "classroom_id": classroom_id,
                    "building": building,
                    "area": area,
                    "room": room,
                    "capacity": capacity,
                    "school_id": school_id,
                    "school_name": school_name,
                    "selfstudy_id": selfstudy_id,
                    "student_supposed": student_supposed,
                    "remark": remark,
                }
                for (
                    campus,
                    classroom_id,
                    building,
                    area,
                    room,
                    capacity,
                    school_id,
                    school_name,
                    selfstudy_id,
                    student_supposed,
                    remark,
                ) in results
            ]
            return resutls

    @staticmethod
    def uploadSelfstudyClassroom(
        infoForm: dict, db_session: Session | None = None
    ) -> None:
        # =====================================
        # 如果提供已经建立的数据库连接，则直接使用
        session_context = (
            SessionLocal() if db_session is None else nullcontext(db_session)
        )
        with session_context as session:
            # =====================
            # 删除指定日期的已有数据
            session.query(SQL_StudySchedule).filter_by(date=infoForm["date"]).delete()
            # ===================================
            # 遍历每一条数据，检查数据，存储到列表中
            data_upload = list()
            for row in infoForm["data"]:
                # ==============
                # 检查教室ID存在
                results = (
                    session.query(SQL_Classroom).filter_by(id=row["classroom_id"]).all()
                )
                if len(results) != 1:
                    session.rollback()
                    raise IllegalValueError(
                        "教室不存在或不唯一，请检查数据或联系管理员。",
                        filename=__file__,
                        line=sys._getframe().f_lineno,
                    )
                elif results[0].campus.name != row["campus"]:
                    session.rollback()
                    raise IllegalValueError(
                        "当前校区不存在对应教室，请检查数据或联系管理员。",
                        filename=__file__,
                        line=sys._getframe().f_lineno,
                    )
                classroom_info = results[0]
                # ==============
                # 检查学院ID存在
                results = (
                    session.query(SQL_College).filter_by(id=row["school_id"]).all()
                )
                if len(results) != 1:
                    session.rollback()
                    raise IllegalValueError(
                        "学院不存在或不唯一，请检查数据或联系管理员。",
                        filename=__file__,
                        line=sys._getframe().f_lineno,
                    )
                # ==========================
                # 检查应到人数小于等于教室容纳
                if row["student_supposed"] > classroom_info.capacity:
                    session.rollback()
                    raise IllegalValueError(
                        "应到人数大于教室容纳人数。",
                        filename=__file__,
                        line=sys._getframe().f_lineno,
                    )
                # ========
                # 存储数据
                data_upload.append(
                    SQL_StudySchedule(
                        date=infoForm["date"],
                        classroom_id=row["classroom_id"],
                        college_id=row["school_id"],
                        expected_headcount=row["student_supposed"],
                        remark=row["remark"],
                    )
                )
            # ============
            # 提交所有数据
            for i in data_upload:
                session.add(i)
            session.commit()

    @staticmethod
    def getSubmittedSelfstudyScheduleDate(
        db_session: Session | None = None,
    ) -> tuple[dict] | list[dict]:
        # =====================================
        # 如果提供已经建立的数据库连接，则直接使用
        session_context = (
            SessionLocal() if db_session is None else nullcontext(db_session)
        )
        with session_context as session:
            # =======================
            # 获取提交的早自习检查安排
            # 子查询，找到每个date下最大（非空）的created_at
            subq = (
                session.query(
                    SQL_StudySchedule.date,
                    func.max(SQL_CheckInTask.created_at).label("created_at"),
                )
                .outerjoin(
                    SQL_CheckInTask, SQL_StudySchedule.id == SQL_CheckInTask.schedule_id
                )
                .group_by(SQL_StudySchedule.date)
                .subquery()
            )

            # 再排序并限制数量
            results = (
                session.query(subq.c.date, subq.c.created_at)
                .order_by(DESC(subq.c.date))
                .limit(15)
                .all()
            )
            results = [
                {
                    "date": date.isoformat(),
                    "submitted_at": None
                    if created_at is None
                    else created_at.isoformat(),
                }
                for (date, created_at) in results
            ]
            return results

    @staticmethod
    def removeScheduleOnDate(infoForm: dict, db_session: Session | None = None) -> None:
        # =====================================
        # 如果提供已经建立的数据库连接，则直接使用
        session_context = (
            SessionLocal() if db_session is None else nullcontext(db_session)
        )
        with session_context as session:
            # =======================
            # 删除指定日期的早自习排班
            results = (
                session.query(SQL_CheckInTask)
                .join(
                    SQL_StudySchedule,
                    SQL_StudySchedule.id == SQL_CheckInTask.schedule_id,
                )
                .filter(SQL_StudySchedule.date == infoForm["date"])
                .all()
            )
            for i in results:
                session.delete(i)
            # ========
            # 提交数据
            session.commit()

    @staticmethod
    def getScheduleOnDate(infoForm: dict, db_session: Session | None = None) -> dict:
        # =====================================
        # 如果提供已经建立的数据库连接，则直接使用
        session_context = (
            SessionLocal() if db_session is None else nullcontext(db_session)
        )
        with session_context as session:
            # ==========================
            # 获取当日早自习安排教室信息
            results = (
                session.query(SQL_StudySchedule)
                .join(SQL_Classroom, SQL_Classroom.id == SQL_StudySchedule.classroom_id)
                .filter(SQL_StudySchedule.date == infoForm["date"])
                .order_by(SQL_Classroom.id)
                .all()
            )
            schedule = {
                i.id: {
                    "schedule_id": i.id,
                    "campus": i.classroom.campus.name,
                    "classroom_id": i.classroom_id,
                    "classroom_name": i.classroom.building
                    + i.classroom.area
                    + i.classroom.room_number,
                    "remark": i.remark,
                }
                for i in results
            }
            # ==========================
            # 获取排班和未排班的队员信息
            # 子查询：当天打卡任务 schedule
            task_schedule_subq = (
                session.query(
                    SQL_CheckInTask.student_id.label("student_id"),
                    SQL_CheckInTask.schedule_id.label("schedule_id"),
                )
                .join(
                    SQL_StudySchedule,
                    SQL_CheckInTask.schedule_id == SQL_StudySchedule.id,
                )
                .filter(SQL_StudySchedule.date == infoForm["date"])
                .subquery()
            )
            results = (
                session.query(
                    SQL_GroupMember,
                    SQL_User.name,
                    SQL_Group.name,
                    task_schedule_subq.c.schedule_id,
                )
                .join(SQL_Group, SQL_GroupMember.group_id == SQL_Group.id)
                .filter(SQL_Group.chazao, SQL_GroupMember.role == "member")
                .join(SQL_User, SQL_User.student_id == SQL_GroupMember.student_id)
                .outerjoin(
                    task_schedule_subq,
                    task_schedule_subq.c.student_id == SQL_GroupMember.student_id,
                )
                .order_by(
                    task_schedule_subq.c.schedule_id, SQL_Group.id, SQL_User.student_id
                )
                .all()
            )
            unassigned = list()
            for i, name, group_name, schedule_id in results:
                if schedule_id is None:
                    unassigned.append(
                        {
                            "student_id": i.student_id,
                            "name": name,
                            "group_name": group_name,
                            "campus": i.profile.campus.name,
                        }
                    )
                else:
                    schedule[schedule_id].update(
                        {
                            "student_id": i.student_id,
                            "name": name,
                            "group_name": group_name,
                            "campus": i.profile.campus.name,
                        }
                    )
            # ============
            # 返回结果字典
            results = {
                "date": infoForm["date"].isoformat(),
                "schedule": list(schedule.values()),
                "unassigned": unassigned,
            }
            return results

    @staticmethod
    def submitSelfstudySchedule(
        infoForm: dict, db_session: Session | None = None
    ) -> None:
        # =====================================
        # 如果提供已经建立的数据库连接，则直接使用
        session_context = (
            SessionLocal() if db_session is None else nullcontext(db_session)
        )
        with session_context as session:
            # =======================
            # 删除指定日期的早自习排班
            results = (
                session.query(SQL_CheckInTask)
                .join(
                    SQL_StudySchedule,
                    SQL_StudySchedule.id == SQL_CheckInTask.schedule_id,
                )
                .filter(SQL_StudySchedule.date == infoForm["date"])
                .all()
            )
            for i in results:
                session.delete(i)
            # =========================================================
            # 遍历每一条数据，检查数据，存储到列表中。先处理沙河再处理清水河
            data_upload = list()
            for row in infoForm["data"]["shahe"]:
                # ================
                # 检查自习表ID存在
                results = (
                    session.query(SQL_StudySchedule)
                    .join(
                        SQL_Classroom,
                        SQL_StudySchedule.classroom_id == SQL_Classroom.id,
                    )
                    .join(SQL_Campus, SQL_Campus.id == SQL_Classroom.campus_id)
                    .filter(
                        SQL_StudySchedule.date == infoForm["date"],
                        SQL_StudySchedule.id == row["selfstudy_id"],
                        SQL_Campus.name == "沙河",
                    )
                    .all(),
                )
                if len(results) != 1:
                    session.rollback()
                    raise IllegalValueError(
                        "早自习表不存在或不唯一，请检查数据或联系管理员。",
                        filename=__file__,
                        line=sys._getframe().f_lineno,
                    )
                # ==============
                # 检查学生ID存在
                if row["student_id"] == "":
                    continue
                results = (
                    session.query(SQL_GroupMember)
                    .filter_by(role="member")
                    .join(SQL_Group, SQL_Group.id == SQL_GroupMember.group_id)
                    .filter(SQL_Group.chazao)
                    .join(
                        SQL_UserProfile,
                        SQL_UserProfile.student_id == SQL_GroupMember.student_id,
                    )
                    .filter(SQL_UserProfile.student_id == row["student_id"])
                    .join(SQL_Campus, SQL_Campus.id == SQL_UserProfile.campus_id)
                    .filter(SQL_Campus.name == "沙河")
                    .all()
                )
                if len(results) < 1:
                    session.rollback()
                    raise IllegalValueError(
                        "学号不存在或不唯一，请检查数据或联系管理员。",
                        filename=__file__,
                        line=sys._getframe().f_lineno,
                    )
                row["group_id"] = results[0].group_id
                # ========
                # 存储数据
                data_upload.append(
                    {
                        "schedule_id": row["selfstudy_id"],
                        "student_id": row["student_id"],
                        "group_id": row["group_id"],
                    }
                )
            for row in infoForm["data"]["qingshuihe"]:
                # ================
                # 检查自习表ID存在
                results = (
                    session.query(SQL_StudySchedule)
                    .join(
                        SQL_Classroom,
                        SQL_StudySchedule.classroom_id == SQL_Classroom.id,
                    )
                    .join(SQL_Campus, SQL_Campus.id == SQL_Classroom.campus_id)
                    .filter(
                        SQL_StudySchedule.date == infoForm["date"],
                        SQL_StudySchedule.id == row["selfstudy_id"],
                        SQL_Campus.name == "清水河",
                    )
                    .all(),
                )
                if len(results) != 1:
                    session.rollback()
                    raise IllegalValueError(
                        "早自习表不存在或不唯一，请检查数据或联系管理员。",
                        filename=__file__,
                        line=sys._getframe().f_lineno,
                    )
                # ==============
                # 检查学生ID存在
                if row["student_id"] == "":
                    continue
                results = (
                    session.query(SQL_GroupMember)
                    .filter_by(role="member")
                    .join(SQL_Group, SQL_Group.id == SQL_GroupMember.group_id)
                    .filter(SQL_Group.chazao)
                    .join(
                        SQL_UserProfile,
                        SQL_UserProfile.student_id == SQL_GroupMember.student_id,
                    )
                    .filter(SQL_UserProfile.student_id == row["student_id"])
                    .join(SQL_Campus, SQL_Campus.id == SQL_UserProfile.campus_id)
                    .filter(SQL_Campus.name == "清水河")
                    .all()
                )
                if len(results) < 1:
                    session.rollback()
                    raise IllegalValueError(
                        "学号不存在或不唯一，请检查数据或联系管理员。",
                        filename=__file__,
                        line=sys._getframe().f_lineno,
                    )
                row["group_id"] = results[0].group_id
                # ========
                # 存储数据
                data_upload.append(
                    {
                        "schedule_id": row["selfstudy_id"],
                        "student_id": row["student_id"],
                        "group_id": row["group_id"],
                    }
                )
            # ============
            # 提交所有数据
            session.commit()
            for i in data_upload:
                session.add(
                    SQL_CheckInTask(
                        schedule_id=i["schedule_id"],
                        student_id=i["student_id"],
                        group_id=i["group_id"],
                    )
                )
            session.commit()

    @staticmethod
    def lastScheduleOnDate(infoForm: dict, db_session: Session | None = None) -> dict:
        # =====================================
        # 如果提供已经建立的数据库连接，则直接使用
        session_context = (
            SessionLocal() if db_session is None else nullcontext(db_session)
        )
        with session_context as session:
            # ====================================
            # 获取此日期之前最近一次已提交排班信息
            results = (
                session.query(SQL_StudySchedule.date)
                .join(
                    SQL_CheckInTask,
                    SQL_StudySchedule.id == SQL_CheckInTask.schedule_id,
                    isouter=True,
                )
                .filter(
                    SQL_StudySchedule.date < infoForm["date"],
                    SQL_CheckInTask.created_at.is_not(None),
                )
                .distinct()
                .order_by(DESC(SQL_StudySchedule.date))
                .limit(1)  # 限制结果为前1条
                .all()  # 执行查询并获取所有结果
            )
            if len(results) < 1:
                # ============
                # 返回结果字典
                results = {
                    "qingshuihe": list(),
                    "shahe": list(),
                }
                return results
            infoForm["date"] = results[0][0]
            # results = [
            #     {
            #         "date": date.isoformat(),
            #         "submitted_at": None
            #         if created_at is None
            #         else created_at.isoformat(),
            #     }
            #     for (date, created_at) in results
            # ]
            # ==========================
            # 获取当日早自习安排教室信息
            results = (
                session.query(SQL_StudySchedule)
                .join(SQL_Classroom, SQL_Classroom.id == SQL_StudySchedule.classroom_id)
                .filter(SQL_StudySchedule.date == infoForm["date"])
                .order_by(SQL_Classroom.id)
                .all()
            )
            schedule = {
                i.id: {
                    "schedule_id": i.id,
                    "campus": i.classroom.campus.name,
                    "classroom_id": i.classroom_id,
                    "classroom_name": i.classroom.building
                    + i.classroom.area
                    + i.classroom.room_number,
                    "remark": i.remark,
                }
                for i in results
            }
            # ==========================
            # 获取已排班的队员信息
            results = (
                session.query(
                    SQL_GroupMember,
                    SQL_User.name,
                    SQL_Group.name,
                    SQL_CheckInTask.schedule_id,
                )
                .join(SQL_Group, SQL_GroupMember.group_id == SQL_Group.id)
                .filter(SQL_Group.chazao, SQL_GroupMember.role == "member")
                .join(SQL_User, SQL_User.student_id == SQL_GroupMember.student_id)
                .join(
                    SQL_CheckInTask,
                    SQL_CheckInTask.student_id == SQL_GroupMember.student_id,
                    isouter=True,
                )
                .join(
                    SQL_StudySchedule,
                    SQL_CheckInTask.schedule_id == SQL_StudySchedule.id,
                    isouter=True,
                )
                .filter(
                    SQL_StudySchedule.date == infoForm["date"],
                )
                .order_by(SQL_StudySchedule.id, SQL_Group.id, SQL_User.student_id)
                .all()
            )
            for i, name, group_name, schedule_id in results:
                if schedule_id is not None:
                    schedule[schedule_id].update(
                        {
                            "student_id": i.student_id,
                            "name": name,
                            "group_name": group_name,
                            "campus": i.profile.campus.name,
                        }
                    )
            # ========================================================================
            # 剔除没有排班的教室，将键从schedule_id转为classroom_id，拆分清水河和沙河
            qingshuihe = list()
            shahe = list()
            for i in schedule.values():
                if "student_id" not in i.keys():
                    continue
                if i["campus"] == "清水河":
                    qingshuihe.append({i["classroom_id"]: i["student_id"]})
                elif i["campus"] == "沙河":
                    shahe.append({i["classroom_id"]: i["student_id"]})
            # ============
            # 返回结果字典
            results = {
                "qingshuihe": qingshuihe,
                "shahe": shahe,
            }
            return results

    # @staticmethod
    # def downloadSelfstudyAllData(
    #     infoForm: dict, databaseConnector: DatabaseConnector | None = None
    # ) -> str:
    #     # =====================================
    #     # 如果提供已经建立的数据库连接，则直接使用
    #     if databaseConnector is None:
    #         database = DatabaseConnector()
    #         database.startCursor()
    #     else:
    #         database = databaseConnector
    #     # ====================================
    #     # 调用python程序处理财务信息并导出财务表
    #     # 整理调用参数
    #     path = f"tmpFiles/selfstudy_export_{str(int(random.random() * 10e5))}.xlsx"
    #     infoForm["path"] = path
    #     infoForm["database"] = database
    #     infoForm["startDate"] = datetime.datetime.strptime(
    #         infoForm["startDate"], "%Y-%m-%d"
    #     )
    #     infoForm["endDate"] = datetime.datetime.strptime(
    #         infoForm["endDate"], "%Y-%m-%d"
    #     )
    #     # 开始调用
    #     SelfstudyExportProcess.writedata(**infoForm)

    #     return "/" + path

    # @staticmethod
    # def downloadCoursesAllData(
    #     infoForm: dict, databaseConnector: DatabaseConnector | None = None
    # ) -> str:
    #     # =====================================
    #     # 如果提供已经建立的数据库连接，则直接使用
    #     if databaseConnector is None:
    #         database = DatabaseConnector()
    #         database.startCursor()
    #     else:
    #         database = databaseConnector
    #     # ====================================
    #     # 调用python程序处理财务信息并导出财务表
    #     # 整理调用参数
    #     path = f"tmpFiles/courses_export_{str(int(random.random() * 10e5))}.xlsx"
    #     infoForm["path"] = path
    #     infoForm["database"] = database
    #     infoForm["startDate"] = datetime.datetime.strptime(
    #         infoForm["startDate"], "%Y-%m-%d"
    #     )
    #     infoForm["endDate"] = datetime.datetime.strptime(
    #         infoForm["endDate"], "%Y-%m-%d"
    #     )
    #     # 开始调用
    #     CourseExportProcess.writedata(**infoForm)

    #     return "/" + path

    # @staticmethod
    # def downloadEmptyTimeAllData(
    #     databaseConnector: DatabaseConnector | None = None,
    # ) -> str:
    #     # =====================================
    #     # 如果提供已经建立的数据库连接，则直接使用
    #     if databaseConnector is None:
    #         database = DatabaseConnector()
    #         database.startCursor()
    #     else:
    #         database = databaseConnector
    #     # ====================================
    #     # 调用python程序处理财务信息并导出财务表
    #     # 整理调用参数
    #     path = f"tmpFiles/empty_time_table_export_{str(int(random.random() * 10e5))}.7z"
    #     infoForm = {"path": path, "database": database}
    #     # 开始调用
    #     EmptyTimeTableProcess.writedata(**infoForm)

    #     return "/" + path
