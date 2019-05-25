#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import TencentCloud_php, datetime

import pandas as pd
from openpyxl import Workbook

search_mission = TencentCloud_php.STOS_DB_conn("-p","config.json",(True,0))
update_mission = TencentCloud_php.STOS_DB_conn("-p","config.json",(True,1))

wb = Workbook(write_only=True)
ws = wb.create_sheet()
ws.append(['姓名','学号','编号'])

exe = pd.read_excel(r'G:\OtherThings\大学\学风督导队\队长\2019上\数据\第十三周\第13周查课安排.xlsx')
count = 1
for col in range(1,6):
    for row in range(0,3):
        a = exe.iloc[row,col]
        i_s = a.split('、')[:-1]
        for i in i_s:
            d = i.split('(')[0].split(' ')[0]
            sql = "SELECT `学号` FROM `成员信息` WHERE `姓名` LIKE '%{}%';".format(d)
            ws.append([d,search_mission.execute_query(sql)[0][0],count].copy())

            sql = "UPDATE `查课排班` SET `组员姓名`='{}',`查课组员`='{}' WHERE `日期` BETWEEN '2019-05-20' AND '2019-05-26' AND `编号` = {};".format(d,search_mission.execute_query(sql)[0][0],count)
            update_mission.execute_query(sql)

            count += 1
wb.save("d.xlsx")