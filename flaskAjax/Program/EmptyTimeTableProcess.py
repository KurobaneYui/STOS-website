# -*- coding:utf-8 -*-
import os
import sys
import json
import time
import datetime
import py7zr
from openpyxl.worksheet.worksheet import Worksheet
import pandas as pd
import numpy as np
import openpyxl
from openpyxl.utils.dataframe import dataframe_to_rows
from openpyxl.styles import Border, Side, Alignment, Font
from sqlalchemy.orm import Session
from flaskAjax.BaseComponents.Authorization import SQL_Group, SQL_GroupMember
from flaskAjax.BaseComponents.DatabaseConnector import (
    SQL_EmptyTime,
    SQL_User,
    SessionLocal,
)
from openpyxl.utils import get_column_letter

# 写入Excel文件数据


def writedata(path: str, database: Session):
    """
    path: 文件保存路径
    database: MySQL连接
    """
    # 每一个组的excel文件路径和名称列表
    excel_list = list()

    # 获取现场组每个组组号
    # 遍历每个组
    # # 对每个组建立一个excel文件
    # # 获取每个组的组员：姓名、学号、空课表数据
    # # 遍历获取到的组员信息
    # # # 对每个组员建立一个表单
    # # # 表单写入每个组员的空课信息
    # # 保存文件
    # 对所有的excel打包成7z文件
    # 删除中间excel

    results = database.query(SQL_Group).filter(SQL_Group.chake).all()
    departmentsInfo = [
        {"department_id": i.id, "department_name": i.name} for i in results
    ]
    for one_department in departmentsInfo:
        wb = openpyxl.Workbook()
        ws_sheet_removable = wb.active
        assert ws_sheet_removable is not None
        ws_sheet_removable.title = "removable"

        results = (
            database.query(SQL_GroupMember.student_id, SQL_User.name, SQL_EmptyTime)
            .filter(SQL_GroupMember.group_id == one_department["department_id"])
            .join(SQL_User, SQL_User.student_id == SQL_GroupMember.student_id)
            .join(SQL_EmptyTime, SQL_EmptyTime.student_id == SQL_User.student_id)
            .all()
        )
        studentsInfo = [
            {
                "student_id": student_id,
                "student_name": student_name,
                "mon": empty_time.mon,
                "tue": empty_time.tue,
                "wed": empty_time.wed,
                "thu": empty_time.thu,
                "fri": empty_time.fri,
            }
            for (student_id, student_name, empty_time) in results
        ]

        if len(results) != 0:
            for one_student in studentsInfo:
                mon = one_student["mon"]
                tue = one_student["tue"]
                wed = one_student["wed"]
                thu = one_student["thu"]
                fri = one_student["fri"]

                ws = wb.create_sheet(one_student["student_name"])
                assert isinstance(ws, Worksheet)
                ws.append(
                    [one_student["student_id"], "周一", "周二", "周三", "周四", "周五"]
                )
                ws.append(
                    [
                        "12节",
                        int(mon[0]),
                        int(tue[0]),
                        int(wed[0]),
                        int(thu[0]),
                        int(fri[0]),
                    ]
                )
                ws.append(
                    [
                        "34节",
                        int(mon[1]),
                        int(tue[1]),
                        int(wed[1]),
                        int(thu[1]),
                        int(fri[1]),
                    ]
                )
                ws.append(
                    [
                        "56节",
                        int(mon[2]),
                        int(tue[2]),
                        int(wed[2]),
                        int(thu[2]),
                        int(fri[2]),
                    ]
                )
                ws.append(
                    [
                        "78节",
                        int(mon[3]),
                        int(tue[3]),
                        int(wed[3]),
                        int(thu[3]),
                        int(fri[3]),
                    ]
                )

            del wb["removable"]

        # 保存为组名+ID确保不重名，由于目前现场组1组的编号为5，所以用减4
        wb.save(
            os.path.join(
                os.path.split(path)[0],
                one_department["department_name"]
                + "_ID-"
                + str(one_department["department_id"])
                + ".xlsx",
            )
        )
        excel_list.append(
            os.path.join(
                os.path.split(path)[0],
                one_department["department_name"]
                + "_ID-"
                + str(one_department["department_id"])
                + ".xlsx",
            )
        )

    with py7zr.SevenZipFile(path, "w") as archive:
        for i in excel_list:
            archive.write(i)

    for i in excel_list:
        os.remove(i)


if __name__ == "__main__":
    """
    以下是所需参数及其顺序和类型:
        path:str 文件保存路径目录
    """
    try:
        input_len = len(sys.argv) - 1
        if input_len == 1:
            with SessionLocal() as session:
                path = os.path.join(sys.argv[1], str(time.time()))
                os.makedirs(path, exist_ok=True)
                path = os.path.join(path, "空课表.7z")
                params = {"path": path, "database": session}
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
