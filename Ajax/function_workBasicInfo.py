from Frame.python3.DatabaseConnector import DatabaseConnector
from Frame.python3.Authorization import Auth
from Frame.python3.Logger import Logger
from flask import session


@Logger
@Auth(({'department_id':None,'actor':None},))
def get_work_basic_info():
    connection = DatabaseConnector()
    connection.startCursor()
    connection.execute(
        sql="SELECT name,job,wage,Work.remark as remark FROM Work \
            LEFT JOIN Department ON Work.department_id=Department.department_id \
            WHERE student_id=%s;",
        data=(session["userID"],))
    results = connection.fetchall()
    
    return {'warning':'', 'message':'', 'data':results}