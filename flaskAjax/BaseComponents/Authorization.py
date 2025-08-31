import sys
import flask
from flaskAjax.BaseComponents.Logger import Logger
from flaskAjax.BaseComponents.DatabaseConnector import SessionLocal
from flaskAjax.BaseComponents.CustomSession import CustomSession
from flaskAjax.BaseComponents.CustomError import PermissionDenyError
from flaskAjax.BaseComponents.DatabaseDefinition import GroupMember as SQL_GroupMember


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
        auth (tuple[dict]):
            A tuple of dict with auth required.
            The dict must like {'department_id':2, 'actor':'manager'}

    Returns:
        Callable: return a decorator
    """

    @staticmethod
    def check(rightsNeeded: tuple[dict, ...], needLogin: bool = True) -> None:
        """
        Check interface rights

        Args:
            rightsNeeded (tuple[dict]):
                Be empty means every one who login can access.
                Each dict indicate the rights.
            needLogin (bool, optional):
                True means need login and False means don't need login.
                Defaults to True.

        Raises:
            PermissionDenyError
        """
        with (
            Logger(funcName="Authorization.check()") as logger,
            SessionLocal() as session,
        ):
            logger.funcArgs = {"rightsNeeded": rightsNeeded, "needLogin": needLogin}

            # check whether user has login or not
            if needLogin and not checkIfLogin():
                raise PermissionDenyError(
                    "Please login first.",
                    filename=__file__,
                    line=sys._getframe().f_lineno,
                )

            if needLogin and len(rightsNeeded) > 0:
                # Check function_auth
                for auth_required in rightsNeeded:
                    if auth_required["department_id"] is None:
                        DataFetched = (
                            session.query(SQL_GroupMember)
                            .filter_by(
                                student_id=CustomSession.getSession()["userID"],
                                role=auth_required["actor"],
                            )
                            .all()
                        )
                    else:
                        DataFetched = (
                            session.query(SQL_GroupMember)
                            .filter_by(
                                group_id=auth_required["department_id"],
                                student_id=CustomSession.getSession()["userID"],
                                role=auth_required["actor"],
                            )
                            .all()
                        )
                    if len(DataFetched) > 0:
                        break
                else:
                    raise PermissionDenyError(
                        "Authority check error. Have no rights to execute function.",
                        filename=__file__,
                        line=sys._getframe().f_lineno,
                    )

            logger.funcReturns = "Authority check pass."
