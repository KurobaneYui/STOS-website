from Frame.python3.DatabaseConnector import DatabaseConnector
from Frame.python3.Authorization import Auth
from Frame.python3.Logger import Logger


@Logger
@Auth(({'department_id':None,'actor':'11'},))
def get_contact():
    connection = DatabaseConnector()
    connection.startCursor()
    connection.execute(sql="set @i:=0;")
    connection.execute(sql="SELECT (@i:=@i+1) as `id`,`Contact`.* FROM `Contact`;")
    results = connection.fetchall()
    
    return {'warning':'', 'message':'', 'data':results}