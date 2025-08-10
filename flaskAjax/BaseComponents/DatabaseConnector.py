import os
import json
import sqlite3

from sqlalchemy import (create_engine, event)
from sqlalchemy.orm import sessionmaker

from .DatabaseDefinition import Base as SQL_Base
from .DatabaseDefinition import User as SQL_User  # noqa
from .DatabaseDefinition import UserProfile as SQL_UserProfile  # noqa
from .DatabaseDefinition import UserCredential as SQL_UserCredential  # noqa
from .DatabaseDefinition import PaymentInfo as SQL_PaymentInfo  # noqa
from .DatabaseDefinition import CollectedInfo as SQL_CollectedInfo  # noqa
from .DatabaseDefinition import Blacklist as SQL_Blacklist  # noqa
from .DatabaseDefinition import Group as SQL_Group  # noqa
from .DatabaseDefinition import GroupMember as SQL_GroupMember  # noqa
from .DatabaseDefinition import DataGroupPermission as SQL_DataGroupPermission  # noqa
from .DatabaseDefinition import Campus as SQL_Campus  # noqa
from .DatabaseDefinition import College as SQL_College  # noqa
from .DatabaseDefinition import Classroom as SQL_Classroom  # noqa
from .DatabaseDefinition import StudySchedule as SQL_StudySchedule  # noqa
from .DatabaseDefinition import CheckInTask as SQL_CheckInTask  # noqa
from .DatabaseDefinition import CheckInData as SQL_CheckInData  # noqa
from .DatabaseDefinition import InspectionTask as SQL_InspectionTask  # noqa
from .DatabaseDefinition import InspectionData as SQL_InspectionData  # noqa

# --- 1. 数据库设置 ---
# 定义数据库文件路径和连接URL
with open("config/STSA_APP.conf", "r") as f:
    config = json.load(f)

DB_FILE = config["DBpath"]
DATABASE_URL = f"sqlite:///{DB_FILE}"

# 创建数据库引擎
# echo=False 关闭SQL语句的日志输出，如需调试可设为True
engine = create_engine(DATABASE_URL, echo=False)


# --- 2. 为 SQLite 启用外键约束 (核心解释) ---
# 这段代码是关键。它为 SQLAlchemy 的 engine 设置了一个事件监听器。
#
# @event.listens_for(engine, "connect"):
#   - 它的作用是：每当 SQLAlchemy 的 engine 与数据库建立一个新的物理连接时，
#     就自动执行下面定义的函数 set_sqlite_pragma。
#
# def set_sqlite_pragma(...):
#   - 这个函数接收底层的数据库连接对象 (dbapi_connection)，
#     然后执行原生的 SQL 指令 "PRAGMA foreign_keys=ON;"。
#
# 为什么要这样做？
#   - SQLite 的外键约束默认是关闭的，并且该设置是“按连接”生效的。
#   - SQLAlchemy 为了效率，会维护一个“连接池”，可能会在不同时间创建多个连接。
#   - 使用事件监听器是唯一能保证“池中所有连接”都正确启用了外键约束的可靠方法。
#
# 这段代码是保证您数据完整性的基石，对于 SQLite 来说是必需的。
@event.listens_for(engine, "connect")
def set_sqlite_pragma(dbapi_connection, connection_record):
    if isinstance(dbapi_connection, sqlite3.Connection):
        cursor = dbapi_connection.cursor()
        cursor.execute("PRAGMA foreign_keys=ON;")
        cursor.execute("PRAGMA encoding='UTF-8';")
        cursor.close()


# --- 4. 创建 Session 工厂 (推荐) ---
SessionLocal = sessionmaker(autocommit=False, autoflush=False, bind=engine)


# --- 5. 数据库初始化函数 ---
def initialize_database():
    """
    初始化数据库。
    如果数据库文件目录不存在，则创建它。
    使用`Base.metadata.create_all()`来创建所有定义的表（如果它们尚不存在）。
    """
    import hashlib
    db_dir = os.path.dirname(DB_FILE)
    if db_dir:
        os.makedirs(db_dir, exist_ok=True)

    # create_all会安全地检查表是否存在，不存在则创建
    SQL_Base.metadata.create_all(engine)

    new_user = SQL_User(student_id="202411012149", name="Squirrel", gender="男")
    new_user.profile = SQL_UserProfile(
        campus_id=1, college_id=1, phone="18100500681", qq="1531037856"
    )
    new_user.credential = SQL_UserCredential(
        password_hash=hashlib.sha512("test1234".encode()).digest()
    )
    membership = SQL_GroupMember(
        student_id="202411012149", group_id=1, role="manager", display_title="正队长"
    )
    with SessionLocal() as session:
        session.add(new_user)
        session.add(membership)
        session.commit()
