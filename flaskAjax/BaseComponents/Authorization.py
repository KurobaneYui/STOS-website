import sys
import flask
from flaskAjax.BaseComponents.Logger import Logger
from flaskAjax.BaseComponents.DatabaseConnector import SessionLocal
from flaskAjax.BaseComponents.CustomSession import CustomSession
from flaskAjax.BaseComponents.CustomError import PermissionDenyError
from flaskAjax.BaseComponents.DatabaseDefinition import (
    GroupMember as SQL_GroupMember,
    Group as SQL_Group,
)


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

            if not needLogin:
                logger.funcReturns = "Authority check pass."
                return

            if not checkIfLogin():
                raise PermissionDenyError(
                    "Please login first.",
                    filename=__file__,
                    line=sys._getframe().f_lineno,
                )

            if len(rightsNeeded) <= 0:
                logger.funcReturns = "Authority check pass."
                return

            for auth_required in rightsNeeded:
                if auth_required["department_id"] is None:
                    department_list = [
                        i for (i,) in (session.query(SQL_Group.id).all())
                    ]
                elif auth_required["department_id"] == "chazao":
                    department_list = [
                        i
                        for (i,) in (
                            session.query(SQL_Group.id)
                            .filter(
                                SQL_Group.chazao,
                            )
                            .all()
                        )
                    ]
                elif auth_required["department_id"] == "chake":
                    department_list = [
                        i
                        for (i,) in (
                            session.query(SQL_Group.id)
                            .filter(
                                SQL_Group.chake,
                            )
                            .all()
                        )
                    ]
                elif auth_required["department_id"] == "datamanager":
                    department_list = [
                        i
                        for (i,) in (
                            session.query(SQL_Group.id)
                            .filter(
                                SQL_Group.datamanager,
                            )
                            .all()
                        )
                    ]
                else:
                    department_list = [auth_required["department_id"]]

                actor_list = (
                    ["manager", "member"]
                    if auth_required["actor"] is None
                    else [auth_required["actor"]]
                )
                DataFetched = (
                    session.query(SQL_GroupMember)
                    .filter(
                        SQL_GroupMember.student_id
                        == CustomSession.getSession()["userID"],
                        SQL_GroupMember.group_id.in_(department_list),
                        SQL_GroupMember.role.in_(actor_list),
                    )
                    .all()
                )
                if len(DataFetched) < 0:
                    raise PermissionDenyError(
                        "Authority check error. Have no rights to execute function.",
                        filename=__file__,
                        line=sys._getframe().f_lineno,
                    )

                logger.funcReturns = "Authority check pass."
                return

            raise PermissionDenyError(
                "Authority check error. Have no rights to execute function.",
                filename=__file__,
                line=sys._getframe().f_lineno,
            )
