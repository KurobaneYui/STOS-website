from functools import wraps
from typing import Callable
import pprint
import json
import sys
import os
import datetime
from Frame.python3.ClientInfo import ClientInfo
from Frame.python3.CustomResponsePackage import CustomError


# local file parameters
# logMode: ['Error', 'Warning', 'Log']
# logDir: string to a directory
logMode, logDir = None, None


def Logger(func : Callable) -> Callable:
    """Decorator for program log.

    Logger decorator will log function runtime info if logMode is 'Log'.
    Runtime info includes calling datetime and results.
    This decorator will also log error info when an error raised and
    raise error again for further handle.
    Decorator can log warning info when logMode is not 'Error'.
    But please notice that warning info will be returned by function finished normally.
    So info of function called inner will show up earlier in log file then caller.

    Args:
        (Callable) func: function which need to enable log feature.

    Returns:
        Callable: A function type return, which is acturally a wrapper.
        Function returns will log some infos and procedure works of function input.

    Raises:
        Any error raised by function input will be logged and raised again.
    """
    
    global logMode, logDir

    # check 'logMode' and 'logDir'
    # check '/config/Log.conf' to set logMode and logDir if not set
    # default the logMode and logDir if not set
    # logMode: default is 'Warning'
    # logDir: default is '/log'
    if logMode is None or logDir is None:
        logDir, logMode = "./log", "Warning"
        with open("./config/Log.conf",'r') as f:
            config = json.load(f)
            logDir, logMode = config["LogDir"], config["LogMode"]

    os.makedirs(logDir, exist_ok=True)

    @wraps(func)
    def wrapper(*args, **kwargs):
        # before function execute, log running info if logMode is 'Log'
        time_postfix = datetime.datetime.now().strftime(r"%Y%m%d")
        if logMode == "Log":
            with open(os.path.join(logDir, time_postfix), "a") as f:
                f.write("\n[Log]\n")
                f.write(f"{datetime.datetime.now()}\n")
                f.write(f"Args of {func.__name__}:\n")
                f.write(f"{pprint.pformat(args)}\n")
                f.write(f"Kwargs of {func.__name__}:\n")
                f.write(f"{pprint.pformat(kwargs)}\n")

        try:
            returns = func(*args, **kwargs)
        except Exception as e:
            # log error info when catch Exception
            with open(os.path.join(logDir, time_postfix), "a") as f:
                f.write("\n[Error]\n")
                f.write(f"[{type(e)}] {datetime.datetime.now()}\n")
                if isinstance(e, CustomError):
                    f.write(f"[{e.name}:{e.line}] {e}\n")
                else:
                    f.write(f"[?:{e.__traceback__.tb_lineno}] {e}\n")
                f.write("[ClientInfo]\n")
                f.write(f"{pprint.pformat(ClientInfo())}\n")
            # raise again for function outer to handle
            raise e

        # log warning info if logMode isn't 'Error'
        warning = returns.pop('warning', None)
        
        if logMode != "Error":
            with open(os.path.join(logDir, time_postfix), "a") as f:
                f.write("\n[Warning]\n")
                f.write(f"{datetime.datetime.now()}\n")
                f.write(f"{pprint.pformat(warning)}\n")

        # log return values of function if logMode is 'Log'
        if logMode == 'Log':
            with open(os.path.join(logDir, time_postfix), "a") as f:
                f.write("\n[Log]\n")
                f.write(f"{datetime.datetime.now()}\n")
                f.write(f"Contents of {func.__name__}:\n")
                f.write(f"{pprint.pformat(returns)}\n")

        return returns
            
    return wrapper