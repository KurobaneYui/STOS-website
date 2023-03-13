import json
from flask import Response, make_response
from Frame.python3.BaseComponents.CustomError import *


class CustomResponse:
    """Custom response packager.

    This class will catch any error in runtime and return coordinating response.
    """

    def __init__(self) -> None:
        self.code: int = 200
        self.message: str = ""
        self.data: tuple | list | str = []

    def __enter__(self):
        return self

    def __exit__(self, exc_type, exc_val, exc_tb) -> bool:
        if exc_type is not None:
            self.message = str(exc_val)
            self.data = []

        if isinstance(exc_val, (DatabaseConnectionError, DatabaseBufferError, DatabaseRuntimeError)):
            self.code = 498
            return True
        elif isinstance(exc_val, IllegalValueError):
            self.code = 400
            return True
        elif isinstance(exc_val, PermissionDenyError):
            self.code = 401
            return True
        elif isinstance(exc_val, ResourcesNotFoundError):
            self.code = 404
            return True
        elif isinstance(exc_val, MaintenanceError):
            self.code = 499
            return True
        elif isinstance(exc_val, Exception):
            self.code = 417
            return True

        return False

    def setMessageAndData(self, message: str, data: tuple | list | str, code: int = 200) -> None:
        self.code = code
        self.message = message
        self.data = data

    def getResponse(self) -> Response:
        returns = {"code": self.code,
                   "message": self.message, "data": self.data}
        returns = make_response(json.dumps(returns, ensure_ascii=False))
        returns.headers["Content-Type"] = "application/json;charset=UTF-8"
        return returns
