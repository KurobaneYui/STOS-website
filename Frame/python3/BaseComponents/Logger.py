import os
import json
import pprint
import datetime
from traceback import extract_tb
from Frame.python3.BaseComponents.CustomError import *
from Frame.python3.BaseComponents.ClientInfo import ClientInfo


class Logger:
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
        Callable: A function type return, which is actually a wrapper.
        Function returns will log some infos and procedure works of function input.

    Raises:
        Any error raised by function input will be logged and raised again.
    """

    __slots__ = ("logDir", "logMode", "time_postfix", "funcName",
                 "funcArgs", "warning", "funcReturns")

    def __init__(self, funcName: str, logMode: None | str = None, logDir: None | str = None) -> None:
        """
        logMode: ['Error', 'Warning', 'Log']
        logDir: string to a directory
        """
        self.logDir: None | str = None
        self.logMode: None | str = None
        self.funcName = funcName
        self.time_postfix = datetime.datetime.now().strftime(r"%Y%m%d-%H%M")

        if logMode is None or logDir is None:
            self.logDir, self.logMode = "./log", "Warning"
            with open("./config/Log.conf", 'r') as f:
                config = json.load(f)
                self.logDir, self.logMode = config["LogDir"], config["LogMode"]

        os.makedirs(self.logDir, exist_ok=True)

    def __enter__(self):
        return self

    def __exit__(self, exc_type, exc_val, exc_tb) -> bool:
        if exc_type is None:
            return True

        # log error info when catch Exception
        with open(os.path.join(self.logDir, self.time_postfix+'.log'), "a") as f:
            f.write("\n[Error]\n")
            f.write(f"[{exc_type}] {datetime.datetime.now()}\n")
            if isinstance(exc_val, CustomError):
                f.write(f"[{exc_val.name}:{exc_val.line}] {exc_val}\n")
            else:
                f.write(f"{pprint.pformat(tuple(extract_tb(exc_tb)[-1]))}\n")
            f.write("[ClientInfo]\n")
            f.write(f"{pprint.pformat(ClientInfo.getInfo())}\n")

        return False

    def __setattr__(self, name: str, value) -> None:
        if name not in ("funcArgs", "warning", "funcReturns"):
            super().__setattr__(name, value)
            return

        if name == "funcArgs":
            # before function execute, log running info if logMode is 'Log'
            if self.logMode == "Log":
                with open(os.path.join(self.logDir, self.time_postfix+'.log'), "a") as f:
                    f.write("\n[Log]\n")
                    f.write(f"{datetime.datetime.now()}\n")
                    f.write(f"Args of {self.funcName}:\n")
                    f.write(f"{pprint.pformat(value)}\n")
        elif name == "warning":
            # log warning info if logMode isn't 'Error'
            if self.logMode != "Error":
                with open(os.path.join(self.logDir, self.time_postfix+'.log'), "a") as f:
                    f.write("\n[Warning]\n")
                    f.write(f"{datetime.datetime.now()}\n")
                    f.write(f"{pprint.pformat(value)}\n")
        elif name == "funcReturns":
            # log return values of function if logMode is 'Log'
            if self.logMode == 'Log':
                with open(os.path.join(self.logDir, self.time_postfix+'.log'), "a") as f:
                    f.write("\n[Log]\n")
                    f.write(f"{datetime.datetime.now()}\n")
                    f.write(f"Contents of {self.funcName}:\n")
                    f.write(f"{pprint.pformat(value)}\n")
