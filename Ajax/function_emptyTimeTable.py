from Frame.python3.DatabaseConnector import DatabaseConnector
from Frame.python3.Authorization import Auth
from Frame.python3.Logger import Logger
from flask import session
import numpy


@Logger
@Auth(({'department_id':None,'actor':None},))
def get_empty_time_info():
    database = DatabaseConnector()
    database.startCursor()
    
    DBAffectedRows = database.execute(
        sql="SELECT student_id,mon,tue,wed,thu,fri,sat,sun,remark FROM EmptyTime WHERE student_id=%s;",
        data=(session["userID"],)
    )
    results = database.fetchall()[0]
    
    week_name = ("mon", "tue", "wed", "thu", "fri", "sat", "sun")
    time_period = ("1-2", "3-4", "5-6", "7-8", "9-11")
    empty_table = {"odd":numpy.zeros((len(time_period),len(week_name)),dtype='int8').tolist(),
                   "even":numpy.zeros((len(time_period),len(week_name)),dtype='int8').tolist(),
                   "remark":results["remark"]}
    for i,name in enumerate(week_name):
        for j in range(len(results[name])):
            empty_table["even"][j][i] = 0 if results[name][j] in ['0','1'] else 1
            empty_table["odd"][j][i] = 0 if results[name][j] in ['0','2'] else 1
    
    return {'warning':'', 'message':'', 'data':empty_table}