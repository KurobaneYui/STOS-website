class CustomError(Exception):
    def __init__(self, *args, filename: str, line: int):
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
