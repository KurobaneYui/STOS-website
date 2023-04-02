import re
import sys
import time
import json
import random
import datetime
from flask import Request
from Frame.python3.BaseComponents.CustomSession import CustomSession
from Frame.python3.BaseComponents.CustomError import IllegalValueError
from Frame.python3.BaseComponents.DatabaseConnector import DatabaseConnector


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
                WHERE selfstudy_id IN ( \
                    SELECT selfstudy_id \
                    FROM SelfstudyInfo \
                    WHERE date=%(date)s);",
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
                    AND Work.student_id NOT IN ( \
                        SELECT DISTINCT schedule_student_id \
                        FROM SelfstudyCheckScheduleView \
                        WHERE date = %s AND schedule_student_id IS NOT NULL) \
                ORDER BY School.campus ASC, Department.name ASC, Work.student_id ASC;",
            data=('现场组%', infoForm['date']))
        results["unscheduled"] = database.fetchall()
        # ============
        # 返回结果字典
        return results

    @staticmethod
    def resetScheduleOnDate(infoForm: dict, databaseConnector: DatabaseConnector | None = None) -> dict:
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
        # ============================================
        # 获取指定日期前最近一日的排班信息和对应的队员信息
        # _ = database.execute(
        #     sql="SELECT classroom_name,campus,school_name,schedule_student_id \
        #         FROM SelfstudyCheckScheduleView \
        #         WHERE schedule_student_department_name like %s \
        #             AND campus = %s \
        #             AND date IN ( \
        #                 SELECT DISTINCT date \
        #                 FROM SelfstudyCheckSchedule \
        #                 LEFT JOIN SelfstudyInfo ON SelfstudyCheckSchedule.selfstudy_id = SelfstudyInfo.selfstudy_id \
        #                 WHERE date < %s \
        #                 ORDER BY date DESC \
        #                 LIMIT 1);",
        #     data=("现场组%", infoForm['campus'], infoForm['date']))
        # 不用上面的语句是因为MySQL还没有支持在IN子句内使用LIMIT子句
        _ = database.execute(
            sql="SELECT classroom_name,campus,school_name,schedule_student_id,schedule_student_name,schedule_student_department_name \
                FROM SelfstudyCheckScheduleView \
                WHERE schedule_student_department_name like %s \
                    AND campus = %s \
                    AND date IN ( \
                        SELECT a.* \
                        FROM ( \
                            SELECT DISTINCT date \
                            FROM SelfstudyCheckSchedule \
                            LEFT JOIN SelfstudyInfo ON SelfstudyCheckSchedule.selfstudy_id = SelfstudyInfo.selfstudy_id \
                            WHERE date < %s \
                            ORDER BY date DESC \
                            LIMIT 1) AS a \
                    );",
            data=("现场组%", infoForm['campus'], infoForm['date']))
        # ======================
        # 将最近的排班列表存入字典
        recentScheduleDict = dict()
        for one_schedule in database.fetchall():
            recentScheduleDict[one_schedule['campus']+one_schedule['school_name'] + one_schedule['classroom_name']] = {
                "schedule_student_id": one_schedule['schedule_student_id'],
                "schedule_student_name": one_schedule['schedule_student_name'],
                "schedule_student_department_name": one_schedule['schedule_student_department_name']
            }
        # ==============
        # 搜索当日待查表
        _ = database.execute(
            sql="SELECT selfstudy_id,classroom_name,campus,school_name,selfstudy_info_remark,schedule_student_id,schedule_student_name,schedule_student_department_name \
                FROM SelfstudyCheckScheduleView \
                WHERE campus = %s AND date = %s;",
            data=(infoForm['campus'], infoForm['date']))
        currentSchedule = database.fetchall()
        # =================================================================
        # 对当日待查表遍历，如果最近排班字典中有则加入，没有则清除（均指成员信息）
        scheduledStudentID = set()
        for one_schedule in currentSchedule:
            key = one_schedule['campus']+one_schedule['school_name'] + \
                one_schedule['classroom_name']
            if key in recentScheduleDict.keys():
                scheduledStudentID.add(
                    recentScheduleDict[key]["schedule_student_id"])
                one_schedule['schedule_student_id'] = recentScheduleDict[key]['schedule_student_id']
                one_schedule['schedule_student_name'] = recentScheduleDict[key]['schedule_student_name']
                one_schedule['schedule_student_department_name'] = recentScheduleDict[key]['schedule_student_department_name']
            else:
                one_schedule['schedule_student_id'] = ''
                one_schedule['schedule_student_name'] = ''
                one_schedule['schedule_student_department_name'] = ''
        # =====================
        # 获取所有现场组队员信息
        _ = database.execute(
            sql="SELECT Work.student_id AS student_id, MemberBasic.name AS student_name, Department.name AS student_department_name \
                FROM Work \
                LEFT JOIN Department ON Work.department_id = Department.department_id \
                LEFT JOIN MemberBasic ON Work.student_id = MemberBasic.student_id \
                LEFT JOIN MemberExtend ON Work.student_id = MemberExtend.student_id \
                WHERE Work.job = 0 AND Department.name LIKE %s \
                ORDER BY Department.name ASC, Work.student_id ASC;",
            data=("现场组%组" if infoForm['campus'] == "清水河" else "现场组沙河",))
        # =====================
        # 获取没有排班的成员信息
        unscheduledStudent = list()
        for student_info in database.fetchall():
            if student_info['student_id'] in scheduledStudentID:
                continue
            unscheduledStudent.append({
                "student_id": student_info['student_id'],
                "student_name": student_info['student_name'],
                "student_department_name": student_info['student_department_name']
            })
        # ============
        # 整理结果字典
        results['scheduled'] = currentSchedule
        results['unscheduled'] = unscheduledStudent
        # ============
        # 返回结果字典
        return results

    @staticmethod
    def randomScheduleOnDate(infoForm: dict, databaseConnector: DatabaseConnector | None = None) -> dict:
        # =====================================
        # 如果提供已经建立的数据库连接，则直接使用
        if databaseConnector is None:
            database = DatabaseConnector()
            database.startCursor()
        else:
            database = databaseConnector
        # ================================
        # 获取最近一次排班的第一位成员的组号
        # _ = database.execute(
        #     sql="SELECT schedule_student_department_name \
        #         FROM SelfstudyCheckScheduleView \
        #         WHERE schedule_student_department_name like %s \
        #             AND campus = %s \
        #             AND date IN ( \
        #                 SELECT DISTINCT date \
        #                 FROM SelfstudyCheckSchedule \
        #                 LEFT JOIN SelfstudyInfo ON SelfstudyCheckSchedule.selfstudy_id = SelfstudyInfo.selfstudy_id \
        #                 WHERE date < %s \
        #                 ORDER BY date DESC \
        #                 LIMIT 1) \
        #         LIMIT 1;",
        #     data=("现场组%", infoForm['campus'], infoForm['date']))
        # 不用上面的语句是因为MySQL目前还没有支持IN子句内使用LIMIT子句
        DBAffectedRows = database.execute(
            sql="SELECT schedule_student_department_name \
                FROM SelfstudyCheckScheduleView \
                WHERE schedule_student_department_name like %s \
                    AND campus = %s \
                    AND date IN ( \
                        SELECT a.* \
                        FROM ( \
                            SELECT DISTINCT date \
                            FROM SelfstudyCheckSchedule \
                            LEFT JOIN SelfstudyInfo ON SelfstudyCheckSchedule.selfstudy_id = SelfstudyInfo.selfstudy_id \
                            WHERE date < %s \
                            ORDER BY date DESC \
                            LIMIT 1) AS a \
                    ) \
                LIMIT 1;",
            data=("现场组%", infoForm['campus'], infoForm['date']))
        recentGroupName = database.fetchall()
        if DBAffectedRows == 0:
            recentFirstGroupNumber = 0
        else:
            pattern = r'^现场组(\d)组$'
            match = re.match(
                pattern, recentGroupName[0]["schedule_student_department_name"])
            if match is None:
                recentFirstGroupNumber = 0
            else:
                recentFirstGroupNumber = int(match.group(1))
        # ===================
        # 获取校区下现场组数量
        _ = database.execute(
            sql="SELECT DISTINCT name FROM Department WHERE Department.name LIKE %s;",
            data=("现场组%组" if infoForm['campus'] == "清水河" else "现场组沙河",))
        groupCounter = len(database.fetchall())
        # =================
        # 生成本次现场组组序
        tmp = ["现场组%s组" % (i) for i in range(1, groupCounter+1)]
        tmp = tmp[recentFirstGroupNumber:]+tmp[:recentFirstGroupNumber]
        # ======================
        # 按组序获取各组组员并打乱
        random.seed(time.time())
        if infoForm['campus'] == '清水河':
            allMembers = list()
            for one_group_name in tmp:
                _ = database.execute(
                    sql="SELECT MemberBasic.student_id AS schedule_student_id, MemberBasic.name AS schedule_student_name, Department.name AS schedule_student_department_name \
                        FROM Work \
                        LEFT JOIN MemberBasic ON MemberBasic.student_id = Work.student_id \
                        LEFT JOIN Department ON Work.department_id = Department.department_id \
                        WHERE Work.job = 0 AND Department.name = %s \
                        ORDER BY Department.name ASC, Work.student_id ASC;",
                    data=(one_group_name,))
                members = list(database.fetchall())
                random.shuffle(members)
                allMembers.extend(members)
        elif infoForm['campus'] == '沙河':
            _ = database.execute(
                sql="SELECT MemberBasic.student_id AS schedule_student_id, MemberBasic.name AS schedule_student_name, Department.name AS schedule_student_department_name \
                    FROM Work \
                    LEFT JOIN MemberBasic ON MemberBasic.student_id = Work.student_id \
                    LEFT JOIN Department ON Work.department_id = Department.department_id \
                    WHERE Work.job = 0 AND Department.name = '现场组沙河' \
                    ORDER BY Department.name ASC, Work.student_id ASC;")
            allMembers = list(database.fetchall())
            random.shuffle(allMembers)
        else:
            raise IllegalValueError(
                "Campus must be one of '清水河' and '沙河'.", filename=__file__, line=sys._getframe().f_lineno)
        # ============
        # 获取所有排班
        _ = database.execute(
            sql="SELECT selfstudy_id, classroom_name, school_name, selfstudy_info_remark, campus \
                FROM SelfstudyCheckScheduleView \
                WHERE campus = %s AND date = %s;",
            data=("清水河" if infoForm['campus'] == "清水河" else "沙河", infoForm['date']))
        allSchedules = list(database.fetchall())
        # =========================
        # 将所有组员依次填入排班表中
        unscheduled_students = list()
        maxCoOrderNumber = min(len(allSchedules), len(allMembers))
        for orderNumber in range(maxCoOrderNumber):
            allSchedules[orderNumber].update(allMembers[orderNumber])
        for orderNumber in range(maxCoOrderNumber, len(allSchedules)):
            allSchedules[orderNumber].update({
                "schedule_student_id": '',
                "schedule_student_name": '',
                "schedule_student_department_name": ''
            })
        for orderNumber in range(maxCoOrderNumber, len(allMembers)):
            unscheduled_students.append({
                "student_id": allMembers[orderNumber]["schedule_student_id"],
                "student_name": allMembers[orderNumber]["schedule_student_name"],
                "student_department_name": allMembers[orderNumber]["schedule_student_department_name"]
            })
        # ============
        # 整合所有数据
        results = {
            "date": infoForm['date'],
            "scheduled": allSchedules,
            "unscheduled": unscheduled_students
        }
        # ========
        # 返回数据
        return results

    # TODO: 这里只提供了早自习历史任务，未来加入查课历史任务
    @staticmethod
    def getScheduleHistory(databaseConnector: DatabaseConnector | None = None) -> dict:
        # =====================================
        # 如果提供已经建立的数据库连接，则直接使用
        if databaseConnector is None:
            database = DatabaseConnector()
            database.startCursor()
        else:
            database = databaseConnector
        # =========================
        # 获取当日日期，和前20日日期
        currentDate = datetime.datetime.now()
        past20Date = currentDate - datetime.timedelta(days=20)
        currentDate = currentDate.strftime("%Y-%m-%d")
        past20Date = past20Date.strftime("%Y-%m-%d")
        # ==============================
        # 查询日期范围内，此组员的排班信息
        DBAffectedRows = database.execute(
            sql="SELECT selfstudy_id, date, classroom_name \
                FROM SelfstudyCheckActualView \
                WHERE SelfstudyCheckActualView.actual_student_id = %s \
                    AND (SelfstudyCheckActualView.date >= %s AND SelfstudyCheckActualView.date <= %s) \
                ORDER BY SelfstudyCheckActualView.date DESC, SelfstudyCheckActualView.classroom_name ASC;",
            data=(CustomSession.getSession()["userID"], past20Date, currentDate))
        historyData = list(database.fetchall())
        # ===================
        # 查询排班对应的数据表
        for one_history in historyData:
            # ==============================
            # 处理一下日期，转化为可以JSON化的
            one_history["date"] = one_history["date"].strftime("%Y-%m-%d")
            # ======
            # 数据表
            DBAffectedRows = database.execute(
                sql="SELECT groupleader_recheck \
                    FROM SelfstudyCheckData \
                    WHERE selfstudy_id = %s \
                    ORDER BY submission_time DESC \
                    LIMIT 1;",
                data=(one_history['selfstudy_id'],))
            if DBAffectedRows == 0:
                database.fetchall()
                one_history.update({"groupleader_recheck": 0})
                one_history['submitted'] = False
                one_history['recheck'] = False
            else:
                tmpData = database.fetchall()[0]
                one_history.update(tmpData)
                one_history['submitted'] = True
                one_history['recheck'] = int(
                    tmpData["groupleader_recheck"]) == 1
        # ========
        # 整合数据
        results = dict()
        results['selfstudy'] = historyData
        results['courses'] = list()
        # ========
        # 返回数据
        return results

    @staticmethod
    def getSelfstudyCheckData(databaseConnector: DatabaseConnector | None = None) -> dict:
        # =====================================
        # 如果提供已经建立的数据库连接，则直接使用
        if databaseConnector is None:
            database = DatabaseConnector()
            database.startCursor()
        else:
            database = databaseConnector
        # ================================
        # 获取当日日期后3日日期，和前5日日期
        past5Date = (datetime.datetime.now() -
                     datetime.timedelta(days=5)).strftime("%Y-%m-%d")
        post3Date = (datetime.datetime.now() +
                     datetime.timedelta(days=3)).strftime("%Y-%m-%d")
        # ==============================
        # 查询日期范围内，此组员的排班信息
        DBAffectedRows = database.execute(
            sql="SELECT selfstudy_id, date, classroom_name, campus, school_name, student_supposed \
                FROM SelfstudyCheckActualView \
                WHERE SelfstudyCheckActualView.actual_student_id = %s \
                    AND (SelfstudyCheckActualView.date >= %s AND SelfstudyCheckActualView.date <= %s) \
                ORDER BY SelfstudyCheckActualView.date DESC, SelfstudyCheckActualView.classroom_name ASC;",
            data=(CustomSession.getSession()["userID"], past5Date, post3Date))
        historyData = list(database.fetchall())
        # ===================================
        # 查询排班对应的数据表，并按排班编号整理
        results = list()
        for one_history in historyData:
            # ==============================
            # 处理一下日期，转化为可以JSON化的
            one_history["date"] = one_history["date"].strftime("%Y-%m-%d")
            # ======
            # 数据表
            DBAffectedRows = database.execute(
                sql="SELECT check_result \
                    FROM SelfstudyCheckData \
                    WHERE selfstudy_id = %s \
                    ORDER BY submission_time DESC \
                    LIMIT 1;",
                data=(one_history['selfstudy_id'],))
            if DBAffectedRows == 0:
                database.fetchall()
                selfstudyRecord = "{}"
            else:
                selfstudyRecord = database.fetchall()[0]["check_result"]
            # ======
            # 缺勤表
            DBAffectedRows = database.execute(
                sql="SELECT check_result \
                    FROM SelfstudyCheckAbsent \
                    WHERE selfstudy_id = %s \
                    ORDER BY submission_time DESC \
                    LIMIT 1;",
                data=(one_history['selfstudy_id'],))
            if DBAffectedRows == 0:
                selfstudyAbsentList = "[]"
                database.fetchall()
            else:
                selfstudyAbsentList = database.fetchall()[0]["check_result"]
            # ========
            # 整合数据
            results.append({
                'date': one_history['date'],
                'selfstudy_id': one_history['selfstudy_id'],
                'classroom_name': one_history['classroom_name'],
                'school_name': one_history['school_name'],
                'campus': one_history['campus'],
                'student_supposed': one_history['student_supposed'],
                'record': selfstudyRecord,
                'absent': selfstudyAbsentList
            })
        # ========
        # 返回数据
        return results

    @staticmethod
    def submitSelfstudyRecord(infoForm: dict, databaseConnector: DatabaseConnector | None = None) -> None:
        # =====================================
        # 如果提供已经建立的数据库连接，则直接使用
        if databaseConnector is None:
            database = DatabaseConnector()
            database.startCursor()
        else:
            database = databaseConnector
        # ==========================================
        # 查找早自习排班编号对应的实际查早组员是否为本人
        DBAffectedRows = database.execute(
            sql="SELECT selfstudy_id \
                FROM SelfstudyCheckActualView \
                WHERE SelfstudyCheckActualView.actual_student_id = %s \
                    AND SelfstudyCheckActualView.selfstudy_id = %s;",
            data=(CustomSession.getSession()["userID"], infoForm['selfstudy_id']))
        database.fetchall()
        if DBAffectedRows == 0:
            raise PermissionError(
                "此早自习排班的实际检查组员与本人核验不匹配，请联系管理员.", filename=__file__, line=sys._getframe().f_lineno)
        # ============
        # 提交查早数据
        check_result = json.dumps(infoForm['record'], ensure_ascii=False)
        DBAffectedRows = database.execute(
            sql="INSERT INTO SelfstudyCheckData \
                    (selfstudy_id,check_result,submit_student_id,groupleader_recheck,remark) \
                VALUES \
                    (%s,%s,%s,0,'');",
            data=(infoForm['selfstudy_id'], check_result,
                  CustomSession.getSession()["userID"]),
            autoCommit=False)

        check_result = json.dumps(infoForm['absentList'], ensure_ascii=False)
        DBAffectedRows = database.execute(
            sql="INSERT INTO SelfstudyCheckAbsent \
                    (selfstudy_id,check_result,submit_student_id) \
                VALUES \
                    (%s,%s,%s);",
            data=(infoForm['selfstudy_id'], check_result,
                  CustomSession.getSession()["userID"]),
            autoCommit=False)
        # ========
        # 提交数据
        database.commit()

    @staticmethod
    def getGroupSelfstudyCheckData(databaseConnector: DatabaseConnector | None = None) -> dict:
        # =====================================
        # 如果提供已经建立的数据库连接，则直接使用
        if databaseConnector is None:
            database = DatabaseConnector()
            database.startCursor()
        else:
            database = databaseConnector
        # ================================
        # 获取当日日期后3日日期，和前7日日期
        past7Date = datetime.datetime.now() - datetime.timedelta(days=7)
        post3Date = datetime.datetime.now() + datetime.timedelta(days=3)
        date_list = [d.strftime("%Y-%m-%d") for d in (past7Date + datetime.timedelta(days=x)
                                                      for x in range((post3Date - past7Date).days + 1))]
        # =============================================
        # 获取登录组，如果是队长则登录组列表填入所有现场组
        if CustomSession.getSession()["department_id"] == 1:
            DBAffectedRows = database.execute(
                sql="SELECT department_id \
                    FROM Department \
                    WHERE name LIKE %s;",
                data=("现场组%",))
            department_list = list(database.fetchall())
        elif CustomSession.getSession()["department_id"] != 0:
            department_list = [
                {"department_id": CustomSession.getSession()["department_id"]},]
        else:
            raise PermissionError(
                "没有权限查看组内提交早自习数据.", filename=__file__, line=sys._getframe().f_lineno)
        # ================================================
        # 对每个登录组，按日期顺序获取对应日期的组员排班和数据
        results = dict()
        for department_id in department_list:
            department_id = department_id['department_id']
            # ============
            # 按组获取数据
            DBAffectedRows = database.execute(
                sql="SELECT department_id, name AS department_name \
                    FROM Department \
                    WHERE name LIKE %s AND department_id = %s;",
                data=("现场组%", department_id))
            if DBAffectedRows != 1:
                continue
            results[department_id] = database.fetchall()[0]
            results[department_id]["data"] = dict()
            for date in date_list:
                # =================
                # 子项按日期获取数据
                DBAffectedRows = database.execute(
                    sql="SELECT selfstudy_id, classroom_name, campus, school_name, student_supposed, actual_student_id, actual_student_name \
                        FROM SelfstudyCheckActualView \
                        WHERE SelfstudyCheckActualView.actual_student_department_id = %s \
                            AND SelfstudyCheckActualView.date = %s \
                        ORDER BY SelfstudyCheckActualView.actual_student_id ASC,SelfstudyCheckActualView.classroom_name ASC;",
                    data=(department_id, date))
                all_schedules = database.fetchall()
                if DBAffectedRows != 0:
                    results[department_id]["data"][date] = list()
                for one_schedules in all_schedules:
                    # =======================
                    # 对每一个排班获取提交数据
                    selfstudy_id = one_schedules['selfstudy_id']
                    # ======
                    # 数据表
                    DBAffectedRows = database.execute(
                        sql="SELECT check_result, groupleader_recheck, remark \
                            FROM SelfstudyCheckData \
                            WHERE selfstudy_id = %s \
                            ORDER BY submission_time DESC \
                            LIMIT 1;",
                        data=(selfstudy_id,))
                    info = database.fetchall()
                    if DBAffectedRows == 0:
                        one_schedules.update({"record": "{}",
                                              "recheck_remark":"",
                                              "recheck": False,
                                              "submitted": False})
                    else:
                        one_schedules.update({"record": info[0]["check_result"],
                                              "recheck_remark":info[0]["remark"],
                                              "recheck": int(info[0]["groupleader_recheck"]) == 1,
                                              "submitted": True})
                    # ======
                    # 缺勤表
                    DBAffectedRows = database.execute(
                        sql="SELECT check_result AS absentList \
                            FROM SelfstudyCheckAbsent \
                            WHERE selfstudy_id = %s \
                            ORDER BY submission_time DESC \
                            LIMIT 1;",
                        data=(selfstudy_id,))
                    if DBAffectedRows == 0:
                        one_schedules.update({"absentList": "[]"})
                        database.fetchall()
                    else:
                        one_schedules.update(database.fetchall()[0])
                    # =================
                    # 排班和数据一并存入
                    results[department_id]["data"][date].append(one_schedules)
        # ========
        # 返回数据
        return results
