import sys
import flask
from Frame.python3.BaseComponents.Logger import Logger
from Frame.python3.BaseComponents.CustomSession import CustomSession
from Frame.python3.BaseComponents.CustomError import PermissionDenyError
from Frame.python3.BaseComponents.DatabaseConnector import DatabaseConnector


def checkIfLogin() -> bool:
    """Check global variable and session to confirm if user has already login.

    Returns:
        dict[str,Any]: 'data':True if has login, and 'data':False if has not login
    """
    try:
        _ = flask.g.isLogin
    except Exception:
        flask.g.isLogin = False

    if flask.g.isLogin:
        return True
    elif CustomSession.checkSession():
        flask.g.isLogin = True
        return True

    CustomSession.clearSession()
    flask.g.isLogin = False
    return False


class Authorization:
    """Auth decorator will decorate function and check authority before run a function.

    Args:
        auth (tuple[dict]): A tuple of dict with auth required. The dict must like {'department_id':2, 'actor'='10'}

    Returns:
        Callable: return a decorator
    """
    @staticmethod
    def check(rightsNeeded: tuple[dict], needLogin: bool = True) -> None:
        """
        Check interface rights

        Args:
            rightsNeeded (tuple[dict]): Be empty means every one who login can access. Each dict indicate the rights.
            needLogin (bool, optional): True means need login and False means don't need login. Defaults to True.

        Raises:
            PermissionDenyError
        """
        with Logger(funcName="Authorization.check()") as logger:
            logger.funcArgs = {
                "rightsNeeded": rightsNeeded, "needLogin": needLogin}

            # check whether user has login or not
            if needLogin and not checkIfLogin():
                raise PermissionDenyError(
                    "Please login first.", filename=__file__, line=sys._getframe().f_lineno)

            if needLogin and len(rightsNeeded) > 0:
                # Check function_auth
                connect = DatabaseConnector()
                connect.startCursor()
                for auth_required in rightsNeeded:
                    if auth_required['department_id'] is None:
                        DBAffectedRow = connect.execute(
                            sql='SELECT actor FROM Authority where student_id = %s AND actor=%s;', data=(CustomSession.getSession()["userID"], auth_required['actor']))
                        connect.fetchall()
                    else:
                        DBAffectedRow = connect.execute(
                            sql='SELECT actor FROM Authority where student_id = %s AND department_id=%s AND actor=%s;', data=(CustomSession.getSession()["userID"], auth_required['department_id'], auth_required['actor']))
                        connect.fetchall()

                    if DBAffectedRow > 0:
                        break
                    else:
                        raise PermissionDenyError(
                            "Authority check error. Have no rights to execute function.", filename=__file__, line=sys._getframe().f_lineno)

            logger.funcReturns = "Authority check pass."
