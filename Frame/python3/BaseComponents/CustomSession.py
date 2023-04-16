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
        
        session.permanent = True

    @staticmethod
    def getSession() -> dict:
        return {"userID": session.get("userID", None),
                "logTime": session.get("logTime", None),
                "department_id": session.get("department_id", None),
                "job": session.get("job", None),
                "department_name": session.get("department_name", None),
                "userName": session.get("userName", None)}

    @staticmethod
    def checkSession() -> bool:
        if "userID" not in session or "userName" not in session or "logTime" not in session \
            or "department_id" not in session or "department_name" not in session \
                or "job" not in session or "isLogin" not in session:
            return False

        if hash(session["userID"]+session["logTime"]+str(session["department_id"])+session["department_name"]+str(session["job"])) != session["isLogin"]:
            return False

        return True

    @staticmethod
    def clearSession() -> None:
        session.pop('userID', None)
        session.pop('userName', None)
        session.pop('isLogin', None)
        session.pop('logTime', None)
        session.pop('department_id', None)
        session.pop('department_name', None)
        session.pop('job', None)
