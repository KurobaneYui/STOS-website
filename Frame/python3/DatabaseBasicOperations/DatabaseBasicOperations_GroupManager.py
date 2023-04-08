import sys
import datetime
from flask import Request
from Frame.python3.BaseComponents.CustomSession import CustomSession
from Frame.python3.BaseComponents.DatabaseConnector import DatabaseConnector
from Frame.python3.BaseComponents.CustomError import DatabaseRuntimeError, IllegalValueError


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
        date_list.reverse()
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
                        sql="SELECT selfstudycheckdata_id, check_result, groupleader_recheck, remark \
                            FROM SelfstudyCheckData \
                            WHERE selfstudy_id = %s \
                            ORDER BY submission_time DESC \
                            LIMIT 1;",
                        data=(selfstudy_id,))
                    info = database.fetchall()
                    if DBAffectedRows == 0:
                        one_schedules.update({"selfstudycheckdata_id": -1,
                                              "record": "{}",
                                              "recheck_remark": "",
                                              "recheck": False,
                                              "submitted": False})
                    else:
                        one_schedules.update({"selfstudycheckdata_id": info[0]["selfstudycheckdata_id"],
                                              "record": info[0]["check_result"],
                                              "recheck_remark": info[0]["remark"],
                                              "recheck": int(info[0]["groupleader_recheck"]) == 1,
                                              "submitted": True})
                    # ======
                    # 缺勤表
                    DBAffectedRows = database.execute(
                        sql="SELECT selfstudycheckabsent_id, check_result AS absentList \
                            FROM SelfstudyCheckAbsent \
                            WHERE selfstudy_id = %s \
                            ORDER BY submission_time DESC \
                            LIMIT 1;",
                        data=(selfstudy_id,))
                    if DBAffectedRows == 0:
                        one_schedules.update({
                            "selfstudycheckabsent_id": -1,
                            "absentList": "[]"
                        })
                        database.fetchall()
                    else:
                        one_schedules.update(database.fetchall()[0])
                    # =================
                    # 排班和数据一并存入
                    results[department_id]["data"][date].append(one_schedules)
        # ========
        # 返回数据
        return results

    @staticmethod
    def submitSelfstudyRecordRecheck(infoForm: dict, databaseConnector: DatabaseConnector | None = None) -> None:
        # =====================================
        # 如果提供已经建立的数据库连接，则直接使用
        if databaseConnector is None:
            database = DatabaseConnector()
            database.startCursor()
        else:
            database = databaseConnector
        # ================================
        # 核验早自习编号和早自习数据编号匹配
        DBAffectedRows = database.execute(
            sql="SELECT selfstudycheckdata_id \
                FROM SelfstudyCheckData \
                WHERE selfstudycheckdata_id = %(selfstudycheckdata_id)s \
                    AND selfstudy_id = %(selfstudy_id)s;",
            data=infoForm)
        database.fetchall()
        if DBAffectedRows == 0:
            raise PermissionError(
                "早自习数据编号和早自习排班编号不匹配.", filename=__file__, line=sys._getframe().f_lineno)
        # ==============================
        # 核验早自习编号对应组员为本组组员
        DBAffectedRows = database.execute(
            sql="SELECT selfstudy_id \
                FROM SelfstudyCheckActualView \
                WHERE selfstudy_id = %s \
                    AND actual_student_department_id = %s;",
            data=(infoForm['selfstudy_id'], CustomSession.getSession()['department_id']))
        database.fetchall()
        if DBAffectedRows == 0:
            raise PermissionError(
                "数据对应早自习排班的组员不属于本组.", filename=__file__, line=sys._getframe().f_lineno)
        # ============
        # 提交确认信息
        DBAffectedRows = database.execute(
            sql="UPDATE SelfstudyCheckData \
                SET groupleader_recheck = %(rechecked)s, \
                    remark = %(recheck_remark)s \
                WHERE selfstudycheckdata_id = %(selfstudycheckdata_id)s;",
            data=infoForm,
            autoCommit=True)

    @staticmethod
    def getGroupEmptyTable(databaseConnector: DatabaseConnector | None = None) -> dict:
        # =====================================
        # 如果提供已经建立的数据库连接，则直接使用
        if databaseConnector is None:
            database = DatabaseConnector()
            database.startCursor()
        else:
            database = databaseConnector
        # =========================================
        # 获取登录组id，并以此获取组员学号姓名和空课表
        _ = database.execute(
            sql="SELECT MemberBasic.name AS student_name, MemberBasic.student_id AS student_id, mon, tue, wed, thu, fri, sat, sun, EmptyTime.remark as emptyTimeRemark \
                FROM EmptyTime \
                LEFT JOIN MemberBasic ON EmptyTime.student_id = MemberBasic.student_id \
                LEFT JOIN Work ON EmptyTime.student_id = Work.student_id \
                LEFT JOIN Department ON Work.department_id = Department.department_id \
                WHERE Department.department_id = %(department_id)s \
                    AND Work.job = 0;",
            data=CustomSession().getSession())
        return database.fetchall()

    @staticmethod
    def setMemberEmptyTable(infoForm: dict, databaseConnector: DatabaseConnector | None = None) -> None:
        # =====================================
        # 如果提供已经建立的数据库连接，则直接使用
        if databaseConnector is None:
            database = DatabaseConnector()
            database.startCursor()
        else:
            database = databaseConnector
        # =========================
        # 确认修改成员是登录组的成员
        DBAffectedRows = database.execute(
            sql="SELECT student_id FROM Work \
                WHERE department_id = %s \
                    AND student_id = %s\
                    AND Work.job = 0;",
            data=(CustomSession().getSession()[
                  "department_id"], infoForm["student_id"]),
            autoCommit=False)
        database.fetchall()
        if DBAffectedRows == 0:
            raise PermissionError(
                "只能修改登录组组员的空课表.", filename=__file__, line=sys._getframe().f_lineno)
        # ==============
        # 获取某日空课表
        DBAffectedRows = database.execute(
            sql="SELECT * FROM EmptyTime WHERE student_id = %(student_id)s;",
            data=infoForm,
            autoCommit=False)
        if DBAffectedRows == 0:
            raise IllegalValueError(
                "查询不到组员指定的空课表.", filename=__file__, line=sys._getframe().f_lineno)
        emptyString = database.fetchall()[0][infoForm["weekName"]]
        emptyBit = int(emptyString[infoForm["timePeriodOrder"]])
        oddEmpty = emptyBit in [1, 3]
        evenEmpty = emptyBit in [2, 3]
        # ==========
        # 修改对应位
        if infoForm["evenOrNot"]:
            evenEmpty = infoForm["emptyOrNot"]
        else:
            oddEmpty = infoForm["emptyOrNot"]

        emptyBit = 0
        if oddEmpty:
            emptyBit += 1
        if evenEmpty:
            emptyBit += 2

        emptyString = emptyString[:infoForm["timePeriodOrder"]] + \
            str(emptyBit)+emptyString[infoForm["timePeriodOrder"]+1:]
        # ============
        # 再提交空课表
        DBAffectedRows = database.execute(
            sql=f"UPDATE EmptyTime \
                SET {infoForm['weekName']} = %s \
                WHERE student_id = %s;",
            data=(emptyString, infoForm["student_id"]),
            autoCommit=False)
        # ========
        # 提交修改
        database.commit()
