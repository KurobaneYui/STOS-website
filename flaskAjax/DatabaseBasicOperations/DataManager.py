import re
import sys
import time
import random
import datetime
from sqlalchemy import desc as DESC
from sqlalchemy.orm import Session
from contextlib import nullcontext
from flask import Request
from flaskAjax.BaseComponents.CustomError import DatabaseRuntimeError, IllegalValueError
from flaskAjax.BaseComponents.DatabaseConnector import (
    SQL_Campus,
    SQL_Classroom,
    SQL_College,
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
                .limit(10)  # 限制结果为前10条
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

    # @staticmethod
    # def submitSelfstudySchedule(
    #     infoForm: dict, databaseConnector: DatabaseConnector | None = None
    # ) -> None:
    #     # =====================================
    #     # 如果提供已经建立的数据库连接，则直接使用
    #     if databaseConnector is None:
    #         database = DatabaseConnector()
    #         database.startCursor()
    #     else:
    #         database = databaseConnector
    #     # =====================
    #     # 删除指定日期的已有数据
    #     _ = database.execute(
    #         sql="DELETE FROM SelfstudyCheckSchedule \
    #             WHERE selfstudy_id IN (SELECT selfstudy_id FROM SelfstudyInfo WHERE date=%s);",
    #         data=infoForm["date"],
    #         autoCommit=False,
    #     )
    #     # =========================================================
    #     # 遍历每一条数据，检查数据，存储到列表中。先处理沙河再处理清水河
    #     data_upload = list()
    #     for row in infoForm["data"]["shahe"]:
    #         # ==============
    #         # 检查自习表ID存在
    #         DBAffectedRows = database.execute(
    #             sql="SELECT SelfstudyInfo.selfstudy_id FROM SelfstudyInfo \
    #                 LEFT JOIN School ON SelfstudyInfo.school_id = School.school_id \
    #                 WHERE selfstudy_id = %s AND School.campus = '沙河' AND date=%s;",
    #             data=(row["selfstudy_id"], infoForm["date"]),
    #             autoCommit=False,
    #         )
    #         if DBAffectedRows != 1:
    #             database.rollback()
    #             raise IllegalValueError(
    #                 "早自习表不存在或不唯一，请检查数据或联系管理员。",
    #                 filename=__file__,
    #                 line=sys._getframe().f_lineno,
    #             )
    #         database.fetchall()
    #         # ==============
    #         # 检查学生ID存在
    #         DBAffectedRows = database.execute(
    #             sql="SELECT student_id FROM Work \
    #                 LEFT JOIN Department ON Work.department_id = Department.department_id \
    #                 WHERE student_id = %s AND job = 0 AND Department.name LIKE %s;",
    #             data=(row["student_id"], "现场组%"),
    #             autoCommit=False,
    #         )
    #         if DBAffectedRows != 1:
    #             database.rollback()
    #             raise IllegalValueError(
    #                 "学号不存在或不唯一，请检查数据或联系管理员。",
    #                 filename=__file__,
    #                 line=sys._getframe().f_lineno,
    #             )
    #         database.fetchall()
    #         # ========
    #         # 存储数据
    #         data_upload.append(
    #             (row["selfstudy_id"], row["student_id"], row["student_id"], "")
    #         )
    #     for row in infoForm["data"]["qingshuihe"]:
    #         # ==============
    #         # 检查自习表ID存在
    #         DBAffectedRows = database.execute(
    #             sql="SELECT SelfstudyInfo.selfstudy_id FROM SelfstudyInfo \
    #                 LEFT JOIN School ON SelfstudyInfo.school_id = School.school_id \
    #                 WHERE selfstudy_id = %s AND School.campus = '清水河' AND date=%s;",
    #             data=(row["selfstudy_id"], infoForm["date"]),
    #             autoCommit=False,
    #         )
    #         if DBAffectedRows != 1:
    #             database.rollback()
    #             raise IllegalValueError(
    #                 "早自习表不存在或不唯一，请检查数据或联系管理员。",
    #                 filename=__file__,
    #                 line=sys._getframe().f_lineno,
    #             )
    #         database.fetchall()
    #         # ==============
    #         # 检查学生ID存在
    #         DBAffectedRows = database.execute(
    #             sql="SELECT student_id FROM Work \
    #                 LEFT JOIN Department ON Work.department_id = Department.department_id \
    #                 WHERE student_id = %s AND job = 0 AND Department.name LIKE %s;",
    #             data=(row["student_id"], "现场组%"),
    #             autoCommit=False,
    #         )
    #         if DBAffectedRows != 1:
    #             database.rollback()
    #             raise IllegalValueError(
    #                 "学号不存在或不唯一，请检查数据或联系管理员。",
    #                 filename=__file__,
    #                 line=sys._getframe().f_lineno,
    #             )
    #         database.fetchall()
    #         # ========
    #         # 存储数据
    #         data_upload.append(
    #             (int(row["selfstudy_id"]), row["student_id"], row["student_id"], "")
    #         )
    #     # ============
    #     # 提交所有数据
    #     if len(data_upload) > 0:
    #         database.execute(
    #             sql="INSERT INTO SelfstudyCheckSchedule (selfstudy_id,schedule_student_id,actual_student_id,remark) VALUES (%s,%s,%s,%s);",
    #             data=data_upload,
    #             autoCommit=False,
    #         )
    #     database.commit()

    # @staticmethod
    # def removeSelfstudySchedule(
    #     infoForm: dict, databaseConnector: DatabaseConnector | None = None
    # ) -> None:
    #     # =====================================
    #     # 如果提供已经建立的数据库连接，则直接使用
    #     if databaseConnector is None:
    #         database = DatabaseConnector()
    #         database.startCursor()
    #     else:
    #         database = databaseConnector
    #     # =======================
    #     # 删除指定日期的早自习排班
    #     _ = database.execute(
    #         sql="DELETE FROM SelfstudyCheckSchedule \
    #             WHERE selfstudy_id IN ( \
    #                 SELECT selfstudy_id \
    #                 FROM SelfstudyInfo \
    #                 WHERE date=%(date)s);",
    #         data=infoForm,
    #     )

    # @staticmethod
    # def getScheduleOnDate(
    #     infoForm: dict, databaseConnector: DatabaseConnector | None = None
    # ) -> dict:
    #     # =====================================
    #     # 如果提供已经建立的数据库连接，则直接使用
    #     if databaseConnector is None:
    #         database = DatabaseConnector()
    #         database.startCursor()
    #     else:
    #         database = databaseConnector
    #     # ==============
    #     # 准备返回值字典
    #     results = {"date": infoForm["date"]}
    #     # ==========================
    #     # 获取排班信息和对应的队员信息
    #     _ = database.execute(
    #         sql="SELECT selfstudy_id, classroom_name,campus,school_name,selfstudy_info_remark,schedule_student_name,schedule_student_id,schedule_student_department_name \
    #             FROM SelfstudyCheckScheduleView \
    #             WHERE date = %s;",
    #         data=(infoForm["date"],),
    #     )
    #     results["scheduled"] = database.fetchall()
    #     # ========================
    #     # 获取没有安排排班的队员信息
    #     _ = database.execute(
    #         sql="SELECT School.campus AS campus, Work.student_id AS student_id, MemberBasic.name AS student_name, Department.name AS student_department_name \
    #             FROM Work \
    #             LEFT JOIN Department ON Work.department_id = Department.department_id \
    #             LEFT JOIN MemberBasic ON Work.student_id = MemberBasic.student_id \
    #             LEFT JOIN MemberExtend ON Work.student_id = MemberExtend.student_id \
    #             LEFT JOIN School ON School.school_id = MemberExtend.school_id \
    #             WHERE Work.job = 0 AND Department.name LIKE %s \
    #                 AND Work.student_id NOT IN ( \
    #                     SELECT DISTINCT schedule_student_id \
    #                     FROM SelfstudyCheckScheduleView \
    #                     WHERE date = %s AND schedule_student_id IS NOT NULL) \
    #             ORDER BY School.campus ASC, Department.name ASC, Work.student_id ASC;",
    #         data=("现场组%", infoForm["date"]),
    #     )
    #     results["unscheduled"] = database.fetchall()
    #     # ============
    #     # 返回结果字典
    #     return results

    # @staticmethod
    # def resetScheduleOnDate(
    #     infoForm: dict, databaseConnector: DatabaseConnector | None = None
    # ) -> dict:
    #     # =====================================
    #     # 如果提供已经建立的数据库连接，则直接使用
    #     if databaseConnector is None:
    #         database = DatabaseConnector()
    #         database.startCursor()
    #     else:
    #         database = databaseConnector
    #     # ==============
    #     # 准备返回值字典
    #     results = {"date": infoForm["date"]}
    #     # ============================================
    #     # 获取指定日期前最近一日的排班信息和对应的队员信息
    #     # _ = database.execute(
    #     #     sql="SELECT classroom_name,campus,school_name,schedule_student_id \
    #     #         FROM SelfstudyCheckScheduleView \
    #     #         WHERE schedule_student_department_name like %s \
    #     #             AND campus = %s \
    #     #             AND date IN ( \
    #     #                 SELECT DISTINCT date \
    #     #                 FROM SelfstudyCheckSchedule \
    #     #                 LEFT JOIN SelfstudyInfo ON SelfstudyCheckSchedule.selfstudy_id = SelfstudyInfo.selfstudy_id \
    #     #                 WHERE date < %s \
    #     #                 ORDER BY date DESC \
    #     #                 LIMIT 1);",
    #     #     data=("现场组%", infoForm['campus'], infoForm['date']))
    #     # 不用上面的语句是因为MySQL还没有支持在IN子句内使用LIMIT子句
    #     _ = database.execute(
    #         sql="SELECT classroom_name,campus,school_name,schedule_student_id,schedule_student_name,schedule_student_department_name \
    #             FROM SelfstudyCheckScheduleView \
    #             WHERE schedule_student_department_name like %s \
    #                 AND campus = %s \
    #                 AND date IN ( \
    #                     SELECT a.* \
    #                     FROM ( \
    #                         SELECT DISTINCT date \
    #                         FROM SelfstudyCheckSchedule \
    #                         LEFT JOIN SelfstudyInfo ON SelfstudyCheckSchedule.selfstudy_id = SelfstudyInfo.selfstudy_id \
    #                         WHERE date < %s \
    #                         ORDER BY date DESC \
    #                         LIMIT 1) AS a \
    #                 );",
    #         data=("现场组%", infoForm["campus"], infoForm["date"]),
    #     )
    #     # ======================
    #     # 将最近的排班列表存入字典
    #     recentScheduleDict = dict()
    #     for one_schedule in database.fetchall():
    #         recentScheduleDict[
    #             one_schedule["campus"]
    #             + one_schedule["school_name"]
    #             + one_schedule["classroom_name"]
    #         ] = {
    #             "schedule_student_id": one_schedule["schedule_student_id"],
    #             "schedule_student_name": one_schedule["schedule_student_name"],
    #             "schedule_student_department_name": one_schedule[
    #                 "schedule_student_department_name"
    #             ],
    #         }
    #     # ==============
    #     # 搜索当日待查表
    #     _ = database.execute(
    #         sql="SELECT selfstudy_id,classroom_name,campus,school_name,selfstudy_info_remark,schedule_student_id,schedule_student_name,schedule_student_department_name \
    #             FROM SelfstudyCheckScheduleView \
    #             WHERE campus = %s AND date = %s;",
    #         data=(infoForm["campus"], infoForm["date"]),
    #     )
    #     currentSchedule = database.fetchall()
    #     # =================================================================
    #     # 对当日待查表遍历，如果最近排班字典中有则加入，没有则清除（均指成员信息）
    #     scheduledStudentID = set()
    #     for one_schedule in currentSchedule:
    #         key = (
    #             one_schedule["campus"]
    #             + one_schedule["school_name"]
    #             + one_schedule["classroom_name"]
    #         )
    #         if key in recentScheduleDict.keys():
    #             scheduledStudentID.add(recentScheduleDict[key]["schedule_student_id"])
    #             one_schedule["schedule_student_id"] = recentScheduleDict[key][
    #                 "schedule_student_id"
    #             ]
    #             one_schedule["schedule_student_name"] = recentScheduleDict[key][
    #                 "schedule_student_name"
    #             ]
    #             one_schedule["schedule_student_department_name"] = recentScheduleDict[
    #                 key
    #             ]["schedule_student_department_name"]
    #         else:
    #             one_schedule["schedule_student_id"] = ""
    #             one_schedule["schedule_student_name"] = ""
    #             one_schedule["schedule_student_department_name"] = ""
    #     # =====================
    #     # 获取所有现场组队员信息
    #     _ = database.execute(
    #         sql="SELECT Work.student_id AS student_id, MemberBasic.name AS student_name, Department.name AS student_department_name \
    #             FROM Work \
    #             LEFT JOIN Department ON Work.department_id = Department.department_id \
    #             LEFT JOIN MemberBasic ON Work.student_id = MemberBasic.student_id \
    #             LEFT JOIN MemberExtend ON Work.student_id = MemberExtend.student_id \
    #             WHERE Work.job = 0 AND Department.name LIKE %s \
    #             ORDER BY Department.name ASC, Work.student_id ASC;",
    #         data=("现场组%组" if infoForm["campus"] == "清水河" else "现场组沙河",),
    #     )
    #     # =====================
    #     # 获取没有排班的成员信息
    #     unscheduledStudent = list()
    #     for student_info in database.fetchall():
    #         if student_info["student_id"] in scheduledStudentID:
    #             continue
    #         unscheduledStudent.append(
    #             {
    #                 "student_id": student_info["student_id"],
    #                 "student_name": student_info["student_name"],
    #                 "student_department_name": student_info["student_department_name"],
    #             }
    #         )
    #     # ============
    #     # 整理结果字典
    #     results["scheduled"] = currentSchedule
    #     results["unscheduled"] = unscheduledStudent
    #     # ============
    #     # 返回结果字典
    #     return results

    # @staticmethod
    # def randomScheduleOnDate(
    #     infoForm: dict, databaseConnector: DatabaseConnector | None = None
    # ) -> dict:
    #     # =====================================
    #     # 如果提供已经建立的数据库连接，则直接使用
    #     if databaseConnector is None:
    #         database = DatabaseConnector()
    #         database.startCursor()
    #     else:
    #         database = databaseConnector
    #     # ================================
    #     # 获取最近一次排班的第一位成员的组号
    #     # _ = database.execute(
    #     #     sql="SELECT schedule_student_department_name \
    #     #         FROM SelfstudyCheckScheduleView \
    #     #         WHERE schedule_student_department_name like %s \
    #     #             AND campus = %s \
    #     #             AND date IN ( \
    #     #                 SELECT DISTINCT date \
    #     #                 FROM SelfstudyCheckSchedule \
    #     #                 LEFT JOIN SelfstudyInfo ON SelfstudyCheckSchedule.selfstudy_id = SelfstudyInfo.selfstudy_id \
    #     #                 WHERE date < %s \
    #     #                 ORDER BY date DESC \
    #     #                 LIMIT 1) \
    #     #         LIMIT 1;",
    #     #     data=("现场组%", infoForm['campus'], infoForm['date']))
    #     # 不用上面的语句是因为MySQL目前还没有支持IN子句内使用LIMIT子句
    #     DBAffectedRows = database.execute(
    #         sql="SELECT schedule_student_department_name \
    #             FROM SelfstudyCheckScheduleView \
    #             WHERE schedule_student_department_name like %s \
    #                 AND campus = %s \
    #                 AND date IN ( \
    #                     SELECT a.* \
    #                     FROM ( \
    #                         SELECT DISTINCT date \
    #                         FROM SelfstudyCheckSchedule \
    #                         LEFT JOIN SelfstudyInfo ON SelfstudyCheckSchedule.selfstudy_id = SelfstudyInfo.selfstudy_id \
    #                         WHERE date < %s \
    #                         ORDER BY date DESC \
    #                         LIMIT 1) AS a \
    #                 ) \
    #             LIMIT 1;",
    #         data=("现场组%", infoForm["campus"], infoForm["date"]),
    #     )
    #     recentGroupName = database.fetchall()
    #     if DBAffectedRows == 0:
    #         recentFirstGroupNumber = 0
    #     else:
    #         pattern = r"^现场组(\d)组$"
    #         match = re.match(
    #             pattern, recentGroupName[0]["schedule_student_department_name"]
    #         )
    #         if match is None:
    #             recentFirstGroupNumber = 0
    #         else:
    #             recentFirstGroupNumber = int(match.group(1))
    #     # ===================
    #     # 获取校区下现场组数量
    #     _ = database.execute(
    #         sql="SELECT DISTINCT name FROM Department WHERE Department.name LIKE %s;",
    #         data=("现场组%组" if infoForm["campus"] == "清水河" else "现场组沙河",),
    #     )
    #     groupCounter = len(database.fetchall())
    #     # =================
    #     # 生成本次现场组组序
    #     tmp = ["现场组%s组" % (i) for i in range(1, groupCounter + 1)]
    #     tmp = tmp[recentFirstGroupNumber:] + tmp[:recentFirstGroupNumber]
    #     # ======================
    #     # 按组序获取各组组员并打乱
    #     random.seed(time.time())
    #     if infoForm["campus"] == "清水河":
    #         allMembers = list()
    #         for one_group_name in tmp:
    #             _ = database.execute(
    #                 sql="SELECT MemberBasic.student_id AS schedule_student_id, MemberBasic.name AS schedule_student_name, Department.name AS schedule_student_department_name \
    #                     FROM Work \
    #                     LEFT JOIN MemberBasic ON MemberBasic.student_id = Work.student_id \
    #                     LEFT JOIN Department ON Work.department_id = Department.department_id \
    #                     WHERE Work.job = 0 AND Department.name = %s \
    #                     ORDER BY Department.name ASC, Work.student_id ASC;",
    #                 data=(one_group_name,),
    #             )
    #             members = list(database.fetchall())
    #             random.shuffle(members)
    #             allMembers.extend(members)
    #     elif infoForm["campus"] == "沙河":
    #         _ = database.execute(
    #             sql="SELECT MemberBasic.student_id AS schedule_student_id, MemberBasic.name AS schedule_student_name, Department.name AS schedule_student_department_name \
    #                 FROM Work \
    #                 LEFT JOIN MemberBasic ON MemberBasic.student_id = Work.student_id \
    #                 LEFT JOIN Department ON Work.department_id = Department.department_id \
    #                 WHERE Work.job = 0 AND Department.name = '现场组沙河' \
    #                 ORDER BY Department.name ASC, Work.student_id ASC;"
    #         )
    #         allMembers = list(database.fetchall())
    #         random.shuffle(allMembers)
    #     else:
    #         raise IllegalValueError(
    #             "Campus must be one of '清水河' and '沙河'.",
    #             filename=__file__,
    #             line=sys._getframe().f_lineno,
    #         )
    #     # ============
    #     # 获取所有排班
    #     _ = database.execute(
    #         sql="SELECT selfstudy_id, classroom_name, school_name, selfstudy_info_remark, campus \
    #             FROM SelfstudyCheckScheduleView \
    #             WHERE campus = %s AND date = %s;",
    #         data=(
    #             "清水河" if infoForm["campus"] == "清水河" else "沙河",
    #             infoForm["date"],
    #         ),
    #     )
    #     allSchedules = list(database.fetchall())
    #     # =========================
    #     # 将所有组员依次填入排班表中
    #     unscheduled_students = list()
    #     maxCoOrderNumber = min(len(allSchedules), len(allMembers))
    #     for orderNumber in range(maxCoOrderNumber):
    #         allSchedules[orderNumber].update(allMembers[orderNumber])
    #     for orderNumber in range(maxCoOrderNumber, len(allSchedules)):
    #         allSchedules[orderNumber].update(
    #             {
    #                 "schedule_student_id": "",
    #                 "schedule_student_name": "",
    #                 "schedule_student_department_name": "",
    #             }
    #         )
    #     for orderNumber in range(maxCoOrderNumber, len(allMembers)):
    #         unscheduled_students.append(
    #             {
    #                 "student_id": allMembers[orderNumber]["schedule_student_id"],
    #                 "student_name": allMembers[orderNumber]["schedule_student_name"],
    #                 "student_department_name": allMembers[orderNumber][
    #                     "schedule_student_department_name"
    #                 ],
    #             }
    #         )
    #     # ============
    #     # 整合所有数据
    #     results = {
    #         "date": infoForm["date"],
    #         "scheduled": allSchedules,
    #         "unscheduled": unscheduled_students,
    #     }
    #     # ========
    #     # 返回数据
    #     return results

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
