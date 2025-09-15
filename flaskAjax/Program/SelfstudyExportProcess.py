# -*- coding:utf-8 -*-
import datetime
import time
import sys
import os
import json
from openpyxl.worksheet.worksheet import Worksheet
import pandas as pd
import numpy as np
import openpyxl
from openpyxl.utils.dataframe import dataframe_to_rows
from openpyxl.styles import Border, Side, Alignment, Font
from sqlalchemy.orm import Session
from flaskAjax.BaseComponents.DatabaseConnector import (
    SessionLocal,
    SQL_User,
    SQL_Group,
    SQL_Classroom,
    SQL_StudySchedule,
    SQL_CheckInTask,
    SQL_CheckInData,
)
from openpyxl.utils import get_column_letter

# 写入Excel文件数据


def writedata(
    path: str,
    database: Session,
    startDate: datetime.date,
    endDate: datetime.date,
):
    """
    path: 文件保存路径
    database: MySQL连接
    selfstudy_start_data 起始日期
    selfstudy_end_data 结束日期
    """
    # 创建excel，建立第一个表单“数据”和第二个表单“名单”
    wb = openpyxl.Workbook()
    ws_sheet1 = wb.active
    assert ws_sheet1 is not None
    ws_sheet1.title = "数据"
    ws_sheet2 = wb.create_sheet("缺勤")
    assert isinstance(ws_sheet2, Worksheet)
    ws_sheet3 = wb.create_sheet("请假")
    assert isinstance(ws_sheet3, Worksheet)

    # 从排班中获取给定日期范围内的排班，并获取教室、学院、编号、检查人员姓名学号、所属组等信息
    # 便利每一个排班，利用编号搜索数据
    # # 利用编号查找早自习数据和人员名单数据，只取第一个可行解
    # # 将获取到的数据用json解包，并更新入排班的字典内
    # 将每一个排班字典按规范导入excel

    # 生成日期列表（旧到新）
    days_diff = (endDate - startDate).days
    date_list = [endDate - datetime.timedelta(days=x) for x in range(days_diff + 1)]
    date_list.reverse()
    # ================================================
    # 获取所有安排查早权限的组
    results = (
        database.query(SQL_Group.id, SQL_Group.name).filter(SQL_Group.chazao).all()
    )
    department_list = [
        {"group_id": group_id, "group_name": group_name}
        for (group_id, group_name) in results
    ]
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
            database.query(
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
                SQL_StudySchedule.date >= startDate,
                SQL_StudySchedule.date <= endDate,
            )
            .outerjoin(SQL_CheckInData, SQL_CheckInData.task_id == SQL_CheckInTask.id)
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
                    "campus": classroom.campus.name,
                    "school_name": schedule.college.name,
                    "task_id": task_id,
                    "expected_headcount": schedule.expected_headcount,
                    "first_count": checkin_data.first_count if checkin_data else None,
                    "second_count": checkin_data.second_count if checkin_data else None,
                    "leave": checkin_data.leave if checkin_data else None,
                    "late": checkin_data.late if checkin_data else None,
                    "absentee": checkin_data.absentee if checkin_data else None,
                    "early_leave": checkin_data.early_leave if checkin_data else None,
                    "absent_list": json.loads(checkin_data.absent_list)
                    if checkin_data
                    else [],
                    "leave_list": json.loads(checkin_data.leave_list)
                    if checkin_data
                    else [],
                    "remarks": checkin_data.remarks if checkin_data else None,
                    "status": checkin_data.status if checkin_data else None,
                    "student_name": user.name,
                    "student_id": user.student_id,
                    "classroom_name": classroom.building
                    + classroom.area
                    + classroom.room_number,
                    "classroom_capacity": classroom.capacity,
                }
            )
        results[group_id] = {
            "department_id": group_id,
            "department_name": group_name,
            "data": data_per_date,
        }

    # ============
    # 开始制作表格
    ws_sheet1.append(
        [
            "日期",
            "教室",
            "校区",
            "学院",
            "排班备注",
            "应到人数",
            "第一次出勤",
            "迟到人数",
            "第二次出勤",
            "早退人数",
            "请假人数",
            "缺勤人数",
            "备注",
            "组长确认",
            "查早组员",
            "组员学号",
            "所属组",
        ]
    )
    ws_sheet2.append(
        [
            "日期",
            "教室",
            "校区",
            "学院",
            "缺勤人姓名",
            "缺勤人学号",
            "查早组员",
            "组员学号",
            "所属组",
        ]
    )
    ws_sheet3.append(
        [
            "日期",
            "教室",
            "校区",
            "学院",
            "请假人姓名",
            "请假人学号",
            "事由",
            "查早组员",
            "组员学号",
            "所属组",
        ]
    )

    mainOffset = 2
    absentOffset = 2
    askForLeaveOffset = 2
    for group in results.values():
        for date in group["data"].keys():
            for one_schedule in group["data"][date]:
                ws_sheet1.cell(row=mainOffset, column=1, value=date)
                ws_sheet1.cell(
                    row=mainOffset, column=2, value=one_schedule["classroom_name"]
                )
                ws_sheet1.cell(row=mainOffset, column=3, value=one_schedule["campus"])
                ws_sheet1.cell(
                    row=mainOffset, column=4, value=one_schedule["school_name"]
                )
                ws_sheet1.cell(
                    row=mainOffset, column=5, value=one_schedule["task_remark"]
                )
                ws_sheet1.cell(
                    row=mainOffset, column=6, value=one_schedule["expected_headcount"]
                )
                ws_sheet1.cell(
                    row=mainOffset, column=7, value=one_schedule["first_count"]
                )
                ws_sheet1.cell(row=mainOffset, column=8, value=one_schedule["late"])
                ws_sheet1.cell(
                    row=mainOffset, column=9, value=one_schedule["second_count"]
                )
                ws_sheet1.cell(
                    row=mainOffset, column=10, value=one_schedule["early_leave"]
                )
                ws_sheet1.cell(row=mainOffset, column=11, value=one_schedule["leave"])
                ws_sheet1.cell(
                    row=mainOffset, column=12, value=one_schedule["absentee"]
                )
                ws_sheet1.cell(row=mainOffset, column=13, value=one_schedule["remarks"])
                ws_sheet1.cell(
                    row=mainOffset,
                    column=14,
                    value=one_schedule["status"],
                )
                ws_sheet1.cell(
                    row=mainOffset, column=15, value=one_schedule["student_name"]
                )
                ws_sheet1.cell(
                    row=mainOffset, column=16, value=one_schedule["student_id"]
                )
                ws_sheet1.cell(
                    row=mainOffset,
                    column=17,
                    value=group["department_name"],
                )

                mainOffset += 1

                for one_absent_student in one_schedule["absent_list"]:
                    ws_sheet2.cell(row=absentOffset, column=1, value=date)
                    ws_sheet2.cell(
                        row=absentOffset, column=2, value=one_schedule["classroom_name"]
                    )
                    ws_sheet2.cell(
                        row=absentOffset, column=3, value=one_schedule["campus"]
                    )
                    ws_sheet2.cell(
                        row=absentOffset, column=4, value=one_schedule["school_name"]
                    )

                    ws_sheet2.cell(
                        row=absentOffset,
                        column=5,
                        value=one_absent_student["student_name"],
                    )
                    ws_sheet2.cell(
                        row=absentOffset,
                        column=6,
                        value=one_absent_student["student_id"],
                    )

                    ws_sheet2.cell(
                        row=absentOffset,
                        column=7,
                        value=one_schedule["student_name"],
                    )
                    ws_sheet2.cell(
                        row=absentOffset,
                        column=8,
                        value=one_schedule["student_id"],
                    )
                    ws_sheet2.cell(
                        row=absentOffset,
                        column=9,
                        value=group["department_name"],
                    )

                    absentOffset += 1

                for one_askForLeave_student in one_schedule["leave_list"]:
                    ws_sheet3.cell(row=askForLeaveOffset, column=1, value=date)
                    ws_sheet3.cell(
                        row=askForLeaveOffset,
                        column=2,
                        value=one_schedule["classroom_name"],
                    )
                    ws_sheet3.cell(
                        row=askForLeaveOffset, column=3, value=one_schedule["campus"]
                    )
                    ws_sheet3.cell(
                        row=askForLeaveOffset,
                        column=4,
                        value=one_schedule["school_name"],
                    )

                    ws_sheet3.cell(
                        row=askForLeaveOffset,
                        column=5,
                        value=one_askForLeave_student["student_name"],
                    )
                    ws_sheet3.cell(
                        row=askForLeaveOffset,
                        column=6,
                        value=one_askForLeave_student["student_id"],
                    )
                    ws_sheet3.cell(
                        row=askForLeaveOffset,
                        column=7,
                        value=one_askForLeave_student["reason"],
                    )

                    ws_sheet3.cell(
                        row=askForLeaveOffset,
                        column=8,
                        value=one_schedule["student_name"],
                    )
                    ws_sheet3.cell(
                        row=askForLeaveOffset,
                        column=9,
                        value=one_schedule["student_id"],
                    )
                    ws_sheet3.cell(
                        row=askForLeaveOffset,
                        column=10,
                        value=group["department_name"],
                    )

                    askForLeaveOffset += 1

    # 保存文件
    wb.save(path)


if __name__ == "__main__":
    """
    以下是所需参数及其顺序和类型:
        path:str 文件保存路径目录
        startDate:datetime 起始日期
        endDate:datetime 结束日期
    """
    try:
        input_len = len(sys.argv) - 1
        if input_len == 3:
            with SessionLocal() as session:
                path = os.path.join(sys.argv[1], str(time.time()))
                os.makedirs(path, exist_ok=True)
                path = os.path.join(path, "早自习数据.xlsx")
                params = {
                    "path": path,
                    "database": session,
                    "startDate": datetime.datetime.strptime(sys.argv[2], "%Y-%m-%d"),
                    "endDate": datetime.datetime.strptime(sys.argv[3], "%Y-%m-%d"),
                }
                writedata(**params)
        else:
            raise Exception(
                "程序输入参数数量与需求不一致，获得了{}个参数".format(input_len)
            )
    except Exception as e:
        errors = {
            "ReturnCode": "417",
            "ReturnString": "程序出错",
            "ShowMessage": repr(e),
            "Data": "",
        }
        print(json.dumps(errors, ensure_ascii=False))
    else:
        datetime.datetime.strptime("2013-2-3", "%Y-%m-%d")
        print(
            json.dumps(
                {
                    "ReturnCode": "200",
                    "ReturnString": "成功",
                    "ShowMessage": "",
                    "Data": path,
                },
                ensure_ascii=False,
            )
        )
