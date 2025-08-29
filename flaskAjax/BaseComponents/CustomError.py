class CustomError(Exception):
    def __init__(self, *args, filename: str, line: int):
        super().__init__(*args)
        self.filename = filename
        self.line = line


class DatabaseConnectionError(CustomError, ConnectionError):
    """Custom database connection error."""

    pass


class DatabaseBufferError(CustomError, BufferError):
    """Custom database buffer error."""

    pass


class DatabaseRuntimeError(CustomError, RuntimeError):
    """Custom database runtime error."""

    pass


class IllegalValueError(CustomError, ValueError):
    """Custom error when get illegal parameters."""

    pass


class PermissionDenyError(CustomError, PermissionError):
    """Custom error when have no authority to access function."""

    pass


class ResourcesNotFoundError(CustomError, FileNotFoundError):
    """Custom error when function required is not exist."""

    pass


class MaintenanceError(CustomError, SystemError):
    """Custom error when website is under maintenance."""

    pass
