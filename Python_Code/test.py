#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import TencentCloud_php, datetime, os, time

zaozixi = TencentCloud_php.zaozixi()
chake = TencentCloud_php.chake()

while(1):
    print()
    print("选择功能：")
    print("1)早自习数据导出")
    print("2)查课数据导出")
    print("3)早自习教室信息导入")
    print("4)早自习排班导入")
    print("5)查课课程信息导入")
    print("6)查课排班导入")
    print("q/Q)退出程序")
    a = input()
    if a=='1' :
        print()
        print("选择了早自习数据导出，请输入导出的文件夹目录：")
        path = input()
        if os.path.isdir(path):
            zaozixi.download(path) # 下载人数和缺勤数据到指定文件夹下
        else:
            print("文件夹不存在")
    elif a=='2' :
        print()
        print("选择了查课数据导出，请输入导出的文件夹目录：")
        path = input()
        if os.path.isdir(path):
            chake.download(path) # 下载教室数据到指定文件夹下
        else:
            print("文件夹不存在")
    elif a=='3' :
        print()
        print("选择了早自习教室信息导入，请输入导入的文件目录：")
        path = input()
        if os.path.isfile(path):
            zaozixi.work_info(path) # 导入早自习教室安排
        else:
            print("文件夹不存在")
    elif a=='4' :
        print()
        print("选择了早自习排班导入，请输入导入的文件目录：")
        path = input()
        if os.path.isfile(path):
            chake.work_info(path) # ******暂时用不了，嘿嘿~****** 导入查课教室安排
        else:
            print("文件夹不存在")
    elif a=='5' :
        print()
        print("选择了查课课程信息导入，请输入导入的文件目录：")
        path = input()
        if os.path.isfile(path):
            zaozixi.work_schedule_auto() # 自动生成排班数据，并导入
        else:
            print("文件夹不存在")
    elif a=='6' :
        print()
        print("选择了查课排班导入，请输入导入的文件目录：")
        path = input()
        if os.path.isfile(path):
            chake.work_schedule(path) # 根据excel格式的排班信息进行排班
        else:
            print("文件夹不存在")
    elif a=='q' or a=='Q' :
        print()
        print("选择了退出程序，2秒后退出")
        time.sleep(2)
        break