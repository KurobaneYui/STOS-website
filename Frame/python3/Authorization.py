import sys
import Frame.python3.DatabaseConnector as DatabaseConnector
from Frame.python3.CustomResponsePackage import PermissionDenyError
from Frame.python3.Logger import Logger
from functools import wraps
from typing import Any, Callable
import datetime
import flask
from flask import session


@Logger
def checkIfLogin() -> dict[str,Any]:
    try:
        if flask.g.isLogin == True:
            return {"warning":"", "message":"", "data":True}
        else:
            raise ValueError("flask.g.isLogin is False.")
    except Exception as e:
        if "userID" in session and "isLogin" in session and "logTime" in session:
            if datetime.datetime.now()-datetime.timedelta(hours=3) <= datetime.datetime.fromisoformat(session["logTime"]) <= datetime.datetime.now():
                if hash(session["userID"]+session["logTime"]) == session["isLogin"]:
                    flask.g.isLogin = True
                    return {"warning":"", "message":"", "data":True}
                
        session.pop('userID') if "userID" in session else None
        session.pop('isLogin') if "isLogin" in session else None
        session.pop('logTime') if "logTime" in session else None
        flask.g.isLogin = False
        return {"warning":"", "message":"", "data":False}


def Auth(auth : tuple) -> Callable:
    def decorator(func : Callable) -> Callable:
        @wraps(func)
        def wrapper(*args, **kwargs):
            # check whether user has login or not
            if not checkIfLogin()["data"]:
                raise PermissionDenyError("Please login first.", filename=__file__, line=sys._getframe().f_lineno)
            # Check function_auth
            connect = DatabaseConnector.DatabaseConnector()
            connect.startCursor()
            connect.execute(sql='SELECT department_id,actor FROM Authority where student_id = %s;',data=(session["userID"],))
            results = connect.fetchall()
            
            can_pass = False
            for auth_required in auth:
                for auth_have in results:
                    if auth_required['department_id'] is not None and auth_required['department_id'] != auth_have['department_id']: continue
                    if auth_required["actor"][0] == auth_have["actor"][0]:
                        can_pass = True
                        break
                    if auth_required["actor"][1] == auth_have["actor"][1]: 
                        can_pass = True
                        break
                if can_pass: break
            
            if not can_pass:
                raise PermissionDenyError("Authority check error. Have no rights to execute function.", filename=__file__, line=sys._getframe().f_lineno)
            
            return func(*args, **kwargs)
        return wrapper
    return decorator