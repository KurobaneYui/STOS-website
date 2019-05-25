#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import TencentCloud_php, datetime

#************************早自习相关函数******************************************************

zaozixi = TencentCloud_php.zaozixi()

zaozixi.download("C:/desktop/") # 下载人数和缺勤数据到指定文件夹下

zaozixi.work_info(r"C:\desktop\早自习教室.xlsx") # 导入早自习教室安排

 # 一下两种方式二选一，建议第一个                                                                                                                                         **
zaozixi.work_schedule_auto() # 自动生成排班数据，并导入
zaozixi.work_schedule_manuel(r"c:\desktop\早自习排班.xlsx") # 根据excel格式的排班信息进行排班

#************************早自习相关函数******************************************************


#************************查课相关函数********************************************************

chake = TencentCloud_php.chake()

chake.download("C:/desktop/") # 下载教室数据到指定文件夹下

chake.work_info(r"C:\desktop\早自习教室.xlsx") # ******暂时用不了，嘿嘿~****** 导入查课教室安排
chake.work_schedule(r"c:\desktop\早自习排班.xlsx") # 根据excel格式的排班信息进行排班

#************************查课相关函数********************************************************