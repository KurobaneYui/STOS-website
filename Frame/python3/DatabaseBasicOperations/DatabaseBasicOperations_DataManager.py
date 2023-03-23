import re
import sys
from flask import Request
from Frame.python3.BaseComponents.DatabaseConnector import DatabaseConnector
from Frame.python3.BaseComponents.CustomError import IllegalValueError


class DatabaseBasicOperations_DataManager:
    @staticmethod
    def getCampusForForm(databaseConnector: DatabaseConnector | None = None) -> tuple[dict] | list[dict]:
        # =====================================
        # 如果提供已经建立的数据库连接，则直接使用
        if databaseConnector is None:
            database = DatabaseConnector()
            database.startCursor()
        else:
            database = databaseConnector
        # ============
        # 查询校区列表
        _ = database.execute(
            "SELECT campus FROM `School` UNION SELECT campus FROM `Classroom`;")
        return database.fetchall()

    @staticmethod
    def getSchoolForForm(flaskRequest: Request, databaseConnector: DatabaseConnector | None = None) -> tuple[dict] | list[dict]:
        # =====================================
        # 如果提供已经建立的数据库连接，则直接使用
        if databaseConnector is None:
            database = DatabaseConnector()
            database.startCursor()
        else:
            database = databaseConnector
        # ========================
        # 查询某校区下的学院名称列表
        _ = database.execute(
            "SELECT name FROM `School` WHERE campus=%(campus)s;",
            flaskRequest.form)
        return database.fetchall()

    # 在权限系统设计完成后，修改此处SQL处理
    @staticmethod
    def getSchool(databaseConnector: DatabaseConnector | None = None) -> tuple[dict] | list[dict]:
        # =====================================
        # 如果提供已经建立的数据库连接，则直接使用
        if databaseConnector is None:
            database = DatabaseConnector()
            database.startCursor()
        else:
            database = databaseConnector
        # ====================
        # 获取学院ID和名称与校区
        _ = database.execute(
            "SELECT school_id,name,campus FROM `School`;")
        return database.fetchall()

    @staticmethod
    def getClassroom(flaskRequest: Request, databaseConnector: DatabaseConnector | None = None) -> tuple[dict] | list[dict]:
        # =====================================
        # 如果提供已经建立的数据库连接，则直接使用
        if databaseConnector is None:
            database = DatabaseConnector()
            database.startCursor()
        else:
            database = databaseConnector
        # ================================================
        # 获取某校区的教室信息：楼、区域、教室编号和座位容纳量
        _ = database.execute(
            "SELECT building,area,room,sit_available FROM `Classroom` WHERE campus=%(campus)s;",
            flaskRequest.form)
        return database.fetchall()

    @staticmethod
    def updateSchool(infoForm: dict, databaseConnector: DatabaseConnector | None = None) -> None:
        # =====================================
        # 如果提供已经建立的数据库连接，则直接使用
        if databaseConnector is None:
            database = DatabaseConnector()
            database.startCursor()
        else:
            database = databaseConnector
        # ===============
        # 检查学院是否存在
        DBAffectRows = database.execute(
            "SELECT 'school_id' FROM `School` WHERE school_id=%(school_id)s;",
            infoForm)
        database.fetchall()
        if infoForm["school_id"] != infoForm["old_school_id"] and DBAffectRows != 0:
            raise IllegalValueError(
                "学院 ID 已存在，请检查输入避免重复。", filename=__file__, line=sys._getframe().f_lineno)
        # ============
        # 更新学院信息
        _ = database.execute(
            "UPDATE `School` SET school_id=%(school_id)s,name=%(name)s,campus=%(campus)s WHERE school_id=%(old_school_id)s;",
            infoForm)

    # TODO: 实现此函数
    @staticmethod
    def deleteSchool(flaskRequest: Request, databaseConnector: DatabaseConnector | None = None) -> None:
        # =====================================
        # 如果提供已经建立的数据库连接，则直接使用
        if databaseConnector is None:
            database = DatabaseConnector()
            database.startCursor()
        else:
            database = databaseConnector
        # ================================================
        return

    # TODO: 实现此函数
    @staticmethod
    def addSchool(flaskRequest: Request, databaseConnector: DatabaseConnector | None = None) -> None:
        # =====================================
        # 如果提供已经建立的数据库连接，则直接使用
        if databaseConnector is None:
            database = DatabaseConnector()
            database.startCursor()
        else:
            database = databaseConnector
        # ================================================
        return

    @staticmethod
    def getSubmittedSelfstudyDate(databaseConnector: DatabaseConnector | None = None) -> tuple[dict] | list[dict]:
        # =====================================
        # 如果提供已经建立的数据库连接，则直接使用
        if databaseConnector is None:
            database = DatabaseConnector()
            database.startCursor()
        else:
            database = databaseConnector
        # =======================
        # 获取提交的早自习检查安排
        _ = database.execute(
            "SELECT DISTINCT date FROM SelfstudyInfo ORDER BY date DESC LIMIT 15;")
        results = database.fetchall()
        for i in results:
            i['date'] = str(i['date'])
        return results

    @staticmethod
    def getSelfstudyClassroomDetails(flaskRequest: Request, databaseConnector: DatabaseConnector | None = None) -> tuple[dict] | list[dict]:
        # =====================================
        # 如果提供已经建立的数据库连接，则直接使用
        if databaseConnector is None:
            database = DatabaseConnector()
            database.startCursor()
        else:
            database = databaseConnector
        # =======================
        # 获取提交的早自习教室详情
        _ = database.execute("SELECT Classroom.campus AS campus,building,area,room,sit_available, \
                                School.name AS school_name,selfstudy_id,student_supposed,remark \
                             FROM SelfstudyInfo \
                             LEFT JOIN Classroom ON SelfstudyInfo.classroom_id=Classroom.classroom_id \
                             LEFT JOIN School ON SelfstudyInfo.school_id=School.school_id AND School.campus=Classroom.campus \
                             WHERE date=%(date)s \
                             ORDER BY campus,building,area,room;",
                             flaskRequest.form)
        return database.fetchall()

    @staticmethod
    def uploadSelfstudyClassroom(infoForm: dict, databaseConnector: DatabaseConnector | None = None) -> None:
        # =====================================
        # 如果提供已经建立的数据库连接，则直接使用
        if databaseConnector is None:
            database = DatabaseConnector()
            database.startCursor()
        else:
            database = databaseConnector
        # =====================
        # 删除指定日期的已有数据
        _ = database.execute(
            sql="DELETE FROM SelfstudyInfo WHERE date = %s;", data=infoForm['date'], autoCommit=False)
        # ===================================
        # 遍历每一条数据，检查数据，存储到列表中
        data_upload = list()
        for row in infoForm['data']:
            # ==============
            # 检查教室ID存在
            pattern = r'^([\u4e00-\u9fa5]{2,})(A|B|C|-)(\d{3}[A-Za-z]?)$'
            match = re.match(pattern, row['classroom_name'])
            if match is None:
                database.rollback()
                raise IllegalValueError(
                    "教室名称不符合要求，请检查或联系管理员。", filename=__file__, line=sys._getframe().f_lineno)
            else:
                campus = row["campus"]
                building = match.group(1)
                area = match.group(2)
                room = match.group(3)
            DBAffectedRows = database.execute(
                sql="SELECT classroom_id, sit_available FROM Classroom WHERE campus=%s AND building=%s AND area=%s AND room=%s;",
                data=(campus, building, area, room),
                autoCommit=False)
            if DBAffectedRows != 1:
                database.rollback()
                raise IllegalValueError(
                    "教室不存在或不唯一，请检查数据或联系管理员。", filename=__file__, line=sys._getframe().f_lineno)
            classroom_info = database.fetchall()[0]
            # ==============
            # 检查学院ID存在
            DBAffectedRows = database.execute(
                sql="SELECT school_id FROM School WHERE campus=%s AND name=%s;",
                data=(campus, row["school_name"]),
                autoCommit=False)
            if DBAffectedRows != 1:
                database.rollback()
                raise IllegalValueError(
                    "学院不存在或不唯一，请检查数据或联系管理员。", filename=__file__, line=sys._getframe().f_lineno)
            school_info = database.fetchall()[0]
            # ==========================
            # 检查应到人数小于等于教室容纳
            if row['student_supposed'] > classroom_info['sit_available']:
                database.rollback()
                raise IllegalValueError(
                    "应到人数大于教室容纳人数。", filename=__file__, line=sys._getframe().f_lineno)
            # ========
            # 存储数据
            data_upload.append((school_info['school_id'], classroom_info['classroom_id'],
                               row['student_supposed'], infoForm['date'], row['remark']))
        # ============
        # 提交所有数据
        if len(data_upload) > 0:
            database.execute(
                sql="INSERT INTO SelfstudyInfo (school_id,classroom_id,student_supposed,date,remark) VALUES (%s,%s,%s,%s,%s);",
                data=data_upload,
                autoCommit=False)
        database.commit()

    @staticmethod
    def submitSelfstudySchedule(infoForm: dict, databaseConnector: DatabaseConnector | None = None) -> None:
        # =====================================
        # 如果提供已经建立的数据库连接，则直接使用
        if databaseConnector is None:
            database = DatabaseConnector()
            database.startCursor()
        else:
            database = databaseConnector
        # =====================
        # 删除指定日期的已有数据
        _ = database.execute(
            sql="DELETE FROM SelfstudyCheckSchedule \
                WHERE selfstudy_id IN (SELECT selfstudy_id FROM SelfstudyInfo WHERE date=%s);",
            data=infoForm['date'], autoCommit=False)
        # =========================================================
        # 遍历每一条数据，检查数据，存储到列表中。先处理沙河再处理清水河
        data_upload = list()
        for row in infoForm['data']['shahe']:
            # ==============
            # 检查自习表ID存在
            DBAffectedRows = database.execute(
                sql="SELECT SelfstudyInfo.selfstudy_id FROM SelfstudyInfo \
                    LEFT JOIN School ON SelfstudyInfo.school_id = School.school_id \
                    WHERE selfstudy_id = %s AND School.campus = '沙河' AND date=%s;",
                data=(row['selfstudy_id'], infoForm['date']),
                autoCommit=False)
            if DBAffectedRows != 1:
                database.rollback()
                raise IllegalValueError(
                    "早自习表不存在或不唯一，请检查数据或联系管理员。", filename=__file__, line=sys._getframe().f_lineno)
            database.fetchall()
            # ==============
            # 检查学生ID存在
            DBAffectedRows = database.execute(
                sql="SELECT student_id FROM Work \
                    LEFT JOIN Department ON Work.department_id = Department.department_id \
                    WHERE student_id = %s AND job = 0 AND Department.name LIKE %s;",
                data=(row['student_id'], '现场组%'),
                autoCommit=False)
            if DBAffectedRows != 1:
                database.rollback()
                raise IllegalValueError(
                    "学号不存在或不唯一，请检查数据或联系管理员。", filename=__file__, line=sys._getframe().f_lineno)
            database.fetchall()
            # ========
            # 存储数据
            data_upload.append(
                (row["selfstudy_id"], row["student_id"], row["student_id"], ''))
        for row in infoForm['data']['qingshuihe']:
            # ==============
            # 检查自习表ID存在
            DBAffectedRows = database.execute(
                sql="SELECT SelfstudyInfo.selfstudy_id FROM SelfstudyInfo \
                    LEFT JOIN School ON SelfstudyInfo.school_id = School.school_id \
                    WHERE selfstudy_id = %s AND School.campus = '清水河' AND date=%s;",
                data=(row['selfstudy_id'], infoForm['date']),
                autoCommit=False)
            if DBAffectedRows != 1:
                database.rollback()
                raise IllegalValueError(
                    "早自习表不存在或不唯一，请检查数据或联系管理员。", filename=__file__, line=sys._getframe().f_lineno)
            database.fetchall()
            # ==============
            # 检查学生ID存在
            DBAffectedRows = database.execute(
                sql="SELECT student_id FROM Work \
                    LEFT JOIN Department ON Work.department_id = Department.department_id \
                    WHERE student_id = %s AND job = 0 AND Department.name LIKE %s;",
                data=(row["student_id"], '现场组%'),
                autoCommit=False)
            if DBAffectedRows != 1:
                database.rollback()
                raise IllegalValueError(
                    "学号不存在或不唯一，请检查数据或联系管理员。", filename=__file__, line=sys._getframe().f_lineno)
            database.fetchall()
            # ========
            # 存储数据
            data_upload.append(
                (int(row["selfstudy_id"]), row["student_id"], row["student_id"], ''))
        # ============
        # 提交所有数据
        if len(data_upload) > 0:
            database.execute(
                sql="INSERT INTO SelfstudyCheckSchedule (selfstudy_id,schedule_student_id,actual_student_id,remark) VALUES (%s,%s,%s,%s);",
                data=data_upload,
                autoCommit=False)
        database.commit()

    @staticmethod
    def removeSelfstudySchedule(infoForm: dict, databaseConnector: DatabaseConnector | None = None) -> None:
        # =====================================
        # 如果提供已经建立的数据库连接，则直接使用
        if databaseConnector is None:
            database = DatabaseConnector()
            database.startCursor()
        else:
            database = databaseConnector
        # =======================
        # 删除指定日期的早自习排班
        _ = database.execute(
            sql="DELETE FROM SelfstudyCheckSchedule \
                WHERE selfstudy_id IN (SELECT selfstudy_id FROM SelfstudyInfo WHERE date=%(date)s);",
            data=infoForm)

    @staticmethod
    def getScheduleOnDate(infoForm: dict, databaseConnector: DatabaseConnector | None = None) -> dict:
        # =====================================
        # 如果提供已经建立的数据库连接，则直接使用
        if databaseConnector is None:
            database = DatabaseConnector()
            database.startCursor()
        else:
            database = databaseConnector
        # ==============
        # 准备返回值字典
        results = {"date": infoForm['date']}
        # ==========================
        # 获取排班信息和对应的队员信息
        _ = database.execute(
            sql="SELECT selfstudy_id, classroom_name,campus,school_name,selfstudy_info_remark,schedule_student_name,schedule_student_id,schedule_student_department_name \
                FROM SelfstudyCheckScheduleView \
                WHERE date = %s;",
            data=(infoForm['date'],))
        results["scheduled"] = database.fetchall()
        # ========================
        # 获取没有安排排班的队员信息
        _ = database.execute(
            sql="SELECT School.campus AS campus, Work.student_id AS student_id, MemberBasic.name AS student_name, Department.name AS student_department_name \
                FROM Work \
                LEFT JOIN Department ON Work.department_id = Department.department_id \
                LEFT JOIN MemberBasic ON Work.student_id = MemberBasic.student_id \
                LEFT JOIN MemberExtend ON Work.student_id = MemberExtend.student_id \
                LEFT JOIN School ON School.school_id = MemberExtend.school_id \
                WHERE Work.job = 0 AND Department.name LIKE %s \
                    AND Work.student_id NOT IN (SELECT DISTINCT schedule_student_id FROM SelfstudyCheckScheduleView WHERE date = %s AND schedule_student_id IS NOT NULL) \
                ORDER BY School.campus ASC, Department.name ASC, Work.student_id ASC;",
            data=('现场组%', infoForm['date']))
        results["unscheduled"] = database.fetchall()
        # ============
        # 返回结果字典
        return results
