import datetime
from flask import session


class CustomSession:
    @staticmethod
    def setSession(studentID: str, name: str, logTime: str, work: int = 0) -> None:
        session["userID"] = studentID
        session["userName"] = name
        session["logTime"] = logTime
        session["work"] = work

        session["isLogin"] = hash(studentID+logTime+str(work))

    @staticmethod
    def getSession() -> dict:
        return {"userID": session["userID"], "logTime": session["logTime"], "work": session["work"], "userName": session["userName"]}

    @staticmethod
    def checkSession() -> bool:
        if "userID" not in session or "userName" not in session or "logTime" not in session or "work" not in session or "isLogin" not in session:
            return False

        if not datetime.datetime.now()-datetime.timedelta(hours=3) <= datetime.datetime.fromisoformat(session["logTime"]) <= datetime.datetime.now():
            return False

        if hash(session["userID"]+session["logTime"]+str(session["work"])) != session["isLogin"]:
            return False

        return True

    @staticmethod
    def clearSession() -> None:
        session.pop('userID') if "userID" in session else None
        session.pop('userName') if "userName" in session else None
        session.pop('isLogin') if "isLogin" in session else None
        session.pop('logTime') if "logTime" in session else None
        session.pop('work') if "work" in session else None
