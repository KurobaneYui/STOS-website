import sys
import json
from enum import Enum
from typing import Optional
import pymysql
from pymysql.cursors import DictCursor
from Frame.python3.CustomResponsePackage import DatabaseConnectionError, DatabaseBufferError, DatabaseRuntimeError


class DatabaseConnectionStatus(Enum):
    """DatabaseConnector inner status enumerate
    """
    NoConnection = 0 # There is no connection to the server
    ConnectionEstablished = 1 # There is only connection to the server but no cursor
    CursorEstablished = 2 # There is a cursor established to server
    QueryCached = 3 # There is a query executed and caching some results


class DatabaseConnector:
    """STSA database connector

    Connector to establish connection to STSA database.
    Provide transaction supported.

    Attributes:
        (variable) Session: a connection session when connection established.
        (variable) Cursor: a cursor when start a connection cursor.
        (variable) Status: instance status indicator.

        (method) startCursor: start a cursor when connection is established.
        (method) closeCursor: close a cursor when the cursor exists.
        (method) execute: execute a sql query.
        (method) fetchall: fetch all of query results.
        (method) rollback: rollback sql query in last transaction if not be committed.
        (method) commit: commit last transaction.
    """
    def __init__(self, configFile : str = "./config/DataBase_STSA.conf") -> None:
        """Inits DatabaseConnector with configFile.

        Args:
            (str, optional) configFile: path of config file which provide logMode and logDir configure in json format. Defaults to '/config/DataBase_STSA.conf'.
        """
        try:
            # read config file and try to connect the database
            with open(configFile,'r') as f:
                config = json.load(f)
            Host = config['host']
            Port = config['port']
            User = config['user']
            Password = config['password']
            Database = config['database']
            self.Session = pymysql.connect(
                host=Host,
                port=Port,
                user=User,
                password=Password,
                database=Database,
                charset="utf8")
            self.Cursor = None
            self.Status = DatabaseConnectionStatus.ConnectionEstablished
            # if connection failed
            if self.Session.Error==True:
                self.Status = DatabaseConnectionStatus.NoConnection
                raise DatabaseConnectionError("Error in connection to database", filename=__file__, line=sys._getframe().f_lineno)
        except Exception as e:
            # if any error occured
            self.Session = None
            self.Cursor = None
            self.Status = DatabaseConnectionStatus.NoConnection
            raise DatabaseConnectionError(f"Error in set connection environment. Error info is: {e}", filename=__file__, line=sys._getframe().f_lineno)

    def __del__(self) -> None:
        # deprecate cached results if exists
        if self.Status is DatabaseConnectionStatus.QueryCached:
            self.Cursor.fetchall()
            self.Status = DatabaseConnectionStatus.CursorEstablished

        if self.Status is DatabaseConnectionStatus.CursorEstablished:
            self.Cursor.close()
            self.Status = DatabaseConnectionStatus.ConnectionEstablished
            
        if self.Status is DatabaseConnectionStatus.ConnectionEstablished:
            self.Session.close()
            self.Status = DatabaseConnectionStatus.NoConnection

    def startCursor(self) -> None:
        """Start a database connection cursor.
        
        When there is no cursor exists and connection is already established, create a new cursor.
        """
        if self.Status is DatabaseConnectionStatus.NoConnection:
            raise DatabaseConnectionError("Connection not established.", filename=__file__, line=sys._getframe().f_lineno)
        
        if self.Status in [DatabaseConnectionStatus.CursorEstablished, DatabaseConnectionStatus.QueryCached]:
            raise DatabaseConnectionError("Cursor has been established.", filename=__file__, line=sys._getframe().f_lineno)

        try:
            self.Cursor = self.Session.cursor(cursor=DictCursor)
            self.Status = DatabaseConnectionStatus.CursorEstablished
        except:
            raise DatabaseConnectionError("Cannot start cursor on this session.", filename=__file__, line=sys._getframe().f_lineno)

    def closeCursor(self) -> None:
        """Close a database connection cursor.
        
        When there is a cursor, close it. This action will clean the cache of query result
        """
        if self.Status is DatabaseConnectionStatus.QueryCached:
            self.Cursor.fetchall()
            self.Status = DatabaseConnectionStatus.CursorEstablished
        
        if self.Status is DatabaseConnectionStatus.CursorEstablished:
            self.Cursor.close()
            self.Cursor = None
            self.Status = DatabaseConnectionStatus.ConnectionEstablished

    def execute(self, sql : str, data : Optional[tuple or list or dict] = None, autoCommit : bool = True) -> int:
        """Execute a query with multiply data.

        When a cursor exists, execute sql query in that cursor and cache the results.

        Args:
            (str) sql: sql sentence.
            (Optional[tuple | list | dict], optional) data: multiply data pass through into sql sentence. Defaults to None.
            (bool, optional) autoCommit: indicate whether to commit after execute query. Defaults to True.

        Raises:
            DatabaseConnectionError: raise this error when any unexpected situation occurred on connection.
            DatabaseBufferError: raise this error when try to cache a query result while cache isn't read.
            DatabaseRuntimeError: raise this error when execute query unsuccessfully.

        Returns:
            int: _description_
        """
        if self.Status not in [DatabaseConnectionStatus.CursorEstablished, DatabaseConnectionStatus.QueryCached]:
            raise DatabaseConnectionError("Please start a cursor first", filename=__file__, line=sys._getframe().f_lineno)
        
        if self.Status is DatabaseConnectionStatus.QueryCached:
            raise DatabaseBufferError("Already cached query results. Please fetch them all and try again.", filename=__file__, line=sys._getframe().f_lineno)

        if data is None:
            affectedRow = self.Cursor.execute(sql)
        elif isinstance(data, (tuple,list)) and len(data) > 0 and isinstance(data[0], (tuple,list,dict)):
            affectedRow = self.Cursor.executemany(sql, data)
        else:
            affectedRow = self.Cursor.execute(sql, data)

        if self.Session.Error == True:
            raise DatabaseRuntimeError(f"Cannot execute query.", filename=__file__, line=sys._getframe().f_lineno)

        if sql.startswith(('SELECT ','select ')):
            self.Status = DatabaseConnectionStatus.QueryCached
        else:
            self.Status = DatabaseConnectionStatus.CursorEstablished
            if autoCommit == True:
                self.commit()

        return affectedRow if affectedRow is not None else 0

    def fetchall(self) -> tuple[dict]:
        """Fetch all cached result.

        Returns:
            tuple[dict]: sql query results in dict type
        """
        if self.Status is not DatabaseConnectionStatus.QueryCached:
            return tuple()
        self.Status = DatabaseConnectionStatus.CursorEstablished
        return self.Cursor.fetchall()

    def rollback(self) -> None:
        """Rollback last transaction."""
        if self.Status is not DatabaseConnectionStatus.NoConnection:
            self.Session.rollback()

    def commit(self) -> None:
        """Commit last transaction."""
        if self.Status not in [DatabaseConnectionStatus.NoConnection, DatabaseConnectionStatus.QueryCached]:
            self.Session.commit()