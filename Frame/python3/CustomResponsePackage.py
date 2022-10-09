from functools import wraps
import json
from re import L
from typing import Callable
from flask import make_response

from pprint import pprint


class CustomError(Exception):
    def __init__(self, *args, filename, line):
        super().__init__(*args)
        self.name = filename
        self.line = line


class DatabaseConnectionError(CustomError, ConnectionError):
    """Custom database connection error."""
    def __init__(self, *args, filename, line):
        super().__init__(*args, filename=filename, line=line)


class DatabaseBufferError(CustomError, BufferError):
    """Custom database buffer error."""
    def __init__(self, *args, filename, line):
        super().__init__(*args, filename=filename, line=line)


class DatabaseRuntimeError(CustomError, RuntimeError):
    """Custom database runtime error."""
    def __init__(self, *args, filename, line):
        super().__init__(*args, filename=filename, line=line)


class IllegalValueError(CustomError, ValueError):
    """Custom error when get illegal parameters."""
    def __init__(self, *args, filename, line):
        super().__init__(*args, filename=filename, line=line)


class PermissionDenyError(CustomError, PermissionError):
    """Custom error when have no authority to access function."""
    def __init__(self, *args, filename, line):
        super().__init__(*args, filename=filename, line=line)
        

class ResourcesNotFoundError(CustomError, FileNotFoundError):
    """Custom error when function required is not exist."""
    def __init__(self, *args, filename, line):
        super().__init__(*args, filename=filename, line=line)


class MaintenanceError(CustomError, SystemError):
    """Custom error when website is under maintenance."""
    def __init__(self, *args, filename, line):
        super().__init__(*args, filename=filename, line=line)


def CustomResponsePackage(func : Callable) -> Callable:
    """Customize response packager.

    This decorator will catch any error in runtime and return coordinating response.

    Args:
        (Callable) func: function which need make response.

    Returns:
        Callable: return a wrapper.
    """
    @wraps(func)
    def wrapper(*args, **kwargs):
        try:
            # raise Error499("服务器维护中，请稍后再试", filename="Squirrel", line=0)
            returns = func(*args, **kwargs)
            returns['code'] = 200
        except (DatabaseConnectionError, DatabaseBufferError, DatabaseRuntimeError) as e:
            returns = {"code": 498, "message": str(e), "data": ""}
        except IllegalValueError as e:
            returns = {"code": 400, "message": str(e), "data": ""}
        except PermissionDenyError as e:
            returns = {"code": 401, "message": str(e), "data": ""}
        except ResourcesNotFoundError as e:
            returns = {"code": 404, "message": str(e), "data": ""}
        except MaintenanceError as e:
            returns = {"code": 499, "message": str(e), "data": ""}
        except Exception as e:
            returns = {"code": 417, "message": str(e), "data": ""}

        # ensure response is json and escape UTF-8
        returns = make_response(json.dumps(returns, ensure_ascii=False))
        returns.headers["Content-Type"] = "application/json;charset=UTF-8"
        return returns
            
    return wrapper