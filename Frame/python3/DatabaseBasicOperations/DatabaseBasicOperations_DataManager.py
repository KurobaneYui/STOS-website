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
