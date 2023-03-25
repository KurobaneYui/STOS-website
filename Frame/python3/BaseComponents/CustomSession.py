import datetime
from flask import session


class CustomSession:
    @staticmethod
    def setSession(studentID: str, name: str, logTime: str, department_id: int = 0, job: int = 0, department_name="预备队员") -> None:
        session["userID"] = studentID
        session["userName"] = name
        session["logTime"] = logTime
        session["department_id"] = department_id
        session["department_name"] = department_name
        session["job"] = job

        session["isLogin"] = hash(
            studentID+logTime+str(department_id)+department_name+str(job))

    @staticmethod
    def getSession() -> dict:
        return {"userID": session["userID"],
                "logTime": session["logTime"],
                "department_id": session["department_id"],
                "job": session["job"],
                "department_name": session["department_name"],
                "userName": session["userName"]}

    @staticmethod
    def checkSession() -> bool:
        if "userID" not in session or "userName" not in session or "logTime" not in session \
            or "department_id" not in session or "department_name" not in session \
                or "job" not in session or "isLogin" not in session:
            return False

        if not datetime.datetime.now()-datetime.timedelta(hours=3) <= datetime.datetime.fromisoformat(session["logTime"]) <= datetime.datetime.now():
            return False

        if hash(session["userID"]+session["logTime"]+str(session["department_id"])+session["department_name"]+str(session["job"])) != session["isLogin"]:
            return False

        return True

    @staticmethod
    def clearSession() -> None:
        session.pop('userID') if "userID" in session else None
        session.pop('userName') if "userName" in session else None
        session.pop('isLogin') if "isLogin" in session else None
        session.pop('logTime') if "logTime" in session else None
        session.pop('department_id') if "department_id" in session else None
        session.pop('department_name') if "department_name" in session else None
        session.pop('job') if "job" in session else None
