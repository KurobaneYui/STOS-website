import os
import json
import datetime
import sqlite3

from sqlalchemy import create_engine, event, text
from sqlalchemy.orm import sessionmaker

from .DatabaseDefinition import Base as SQL_Base
from .DatabaseDefinition import User as SQL_User  # noqa
from .DatabaseDefinition import UserProfile as SQL_UserProfile  # noqa
from .DatabaseDefinition import UserCredential as SQL_UserCredential  # noqa
from .DatabaseDefinition import PaymentInfo as SQL_PaymentInfo  # noqa
from .DatabaseDefinition import EmptyTime as SQL_EmptyTime  # noqa
from .DatabaseDefinition import CollectedInfo as SQL_CollectedInfo  # noqa
from .DatabaseDefinition import Blacklist as SQL_Blacklist  # noqa
from .DatabaseDefinition import Group as SQL_Group  # noqa
from .DatabaseDefinition import GroupMember as SQL_GroupMember  # noqa
from .DatabaseDefinition import Campus as SQL_Campus  # noqa
from .DatabaseDefinition import College as SQL_College  # noqa
from .DatabaseDefinition import Classroom as SQL_Classroom  # noqa
from .DatabaseDefinition import StudySchedule as SQL_StudySchedule  # noqa
from .DatabaseDefinition import CheckInTask as SQL_CheckInTask  # noqa
from .DatabaseDefinition import CheckInData as SQL_CheckInData  # noqa
from .DatabaseDefinition import CourseSchedule as SQL_CourseSchedule  # noqa
from .DatabaseDefinition import InspectionTask as SQL_InspectionTask  # noqa
from .DatabaseDefinition import InspectionData as SQL_InspectionData  # noqa
from .DatabaseDefinition import ContactView as SQL_ContactView, contact_view_sql  # noqa
from .DatabaseDefinition import WageView as SQL_WageView, wage_view_sql  # noqa

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
        cursor.execute("PRAGMA journal_mode=WAL;")
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

    def _create_tables_and_view():
        # 只创建非视图的表
        for table in SQL_Base.metadata.sorted_tables:
            if table.info.get("is_view"):
                continue
            table.create(bind=engine, checkfirst=True)
        # 显式执行视图 SQL（若已存在则忽略错误），避免调用 DDL.execute 导致 lint 警告
        try:
            with engine.begin() as conn:
                conn.execute(text(contact_view_sql))
                conn.execute(text(wage_view_sql))
        except Exception:
            pass

    # 如果文件存在视作已有数据，不做处理
    if os.path.exists(DB_FILE):
        _create_tables_and_view()
    else:
        _create_tables_and_view()
        with SessionLocal() as session:
            campus_name = ["清水河", "沙河"]
            college_name = [
                "信息与通信工程学院",
                "电子科学与工程学院",
                "材料与能源学院",
                "机械与电气工程学院",
                "光电科学与工程学院",
                "自动化工程学院",
                "资源与环境学院",
                "计算机科学与工程学院（网络空间安全学院）",
                "信息与软件工程学院（示范性软件学院）",
                "航空航天学院",
                "数学科学学院",
                "物理学院",
                "医学院",
                "生命科学与技术学院",
                "经济与管理学院",
                "公共管理学院",
                "外国语学院",
                "格拉斯哥学院",
                "英才实验学院（未来技术学院）",
                "集成电路科学与工程学院（示范性微电子学院）",
                "外国语学院预科班",
            ]
            group_prof = [
                ("队长组", False, False, False),
                ("现场组一组", False, True, False),
                ("现场组二组", False, True, False),
                ("现场组三组", False, True, False),
                ("查课组一组", True, False, False),
                ("查课组二组", True, False, False),
                ("沙河组", True, True, False),
                ("督导组", False, False, False),
                ("数据组", False, False, True),
            ]
            classroom_info = [
                (1111010, "清水河", "品学楼", "A", "101", 143),
                (1111020, "清水河", "品学楼", "A", "102", 143),
                (1111030, "清水河", "品学楼", "A", "103", 114),
                (1111040, "清水河", "品学楼", "A", "104", 110),
                (1111050, "清水河", "品学楼", "A", "105", 114),
                (1111060, "清水河", "品学楼", "A", "106", 114),
                (1111070, "清水河", "品学楼", "A", "107", 288),
                (1111080, "清水河", "品学楼", "A", "108", 113),
                (1111090, "清水河", "品学楼", "A", "109", 113),
                (1111100, "清水河", "品学楼", "A", "110", 114),
                (1111110, "清水河", "品学楼", "A", "111", 114),
                (1112010, "清水河", "品学楼", "A", "201", 143),
                (1112020, "清水河", "品学楼", "A", "202", 143),
                (1112030, "清水河", "品学楼", "A", "203", 165),
                (1112040, "清水河", "品学楼", "A", "204", 114),
                (1112050, "清水河", "品学楼", "A", "205", 113),
                (1112060, "清水河", "品学楼", "A", "206", 113),
                (1112070, "清水河", "品学楼", "A", "207", 113),
                (1112080, "清水河", "品学楼", "A", "208", 164),
                (1112090, "清水河", "品学楼", "A", "209", 174),
                (1112100, "清水河", "品学楼", "A", "210", 113),
                (1112110, "清水河", "品学楼", "A", "211", 113),
                (1112120, "清水河", "品学楼", "A", "212", 113),
                (1112130, "清水河", "品学楼", "A", "213", 165),
                (1113010, "清水河", "品学楼", "A", "301", 145),
                (1113020, "清水河", "品学楼", "A", "302", 145),
                (1113030, "清水河", "品学楼", "A", "303", 174),
                (1113040, "清水河", "品学楼", "A", "304", 114),
                (1113050, "清水河", "品学楼", "A", "305", 114),
                (1113060, "清水河", "品学楼", "A", "306", 114),
                (1113070, "清水河", "品学楼", "A", "307", 114),
                (1113080, "清水河", "品学楼", "A", "308", 175),
                (1113090, "清水河", "品学楼", "A", "309", 164),
                (1113100, "清水河", "品学楼", "A", "310", 111),
                (1113110, "清水河", "品学楼", "A", "311", 113),
                (1114010, "清水河", "品学楼", "A", "401", 143),
                (1114020, "清水河", "品学楼", "A", "402", 145),
                (1114030, "清水河", "品学楼", "A", "403", 165),
                (1114040, "清水河", "品学楼", "A", "404", 114),
                (1114050, "清水河", "品学楼", "A", "405", 114),
                (1114060, "清水河", "品学楼", "A", "406", 114),
                (1114070, "清水河", "品学楼", "A", "407", 114),
                (1114080, "清水河", "品学楼", "A", "408", 165),
                (1114090, "清水河", "品学楼", "A", "409", 165),
                (1121010, "清水河", "品学楼", "B", "101", 143),
                (1121020, "清水河", "品学楼", "B", "102", 143),
                (1121030, "清水河", "品学楼", "B", "103", 113),
                (1121040, "清水河", "品学楼", "B", "104", 114),
                (1121050, "清水河", "品学楼", "B", "105", 114),
                (1121060, "清水河", "品学楼", "B", "106", 114),
                (1121070, "清水河", "品学楼", "B", "107", 288),
                (1121080, "清水河", "品学楼", "B", "108", 114),
                (1121090, "清水河", "品学楼", "B", "109", 114),
                (1122010, "清水河", "品学楼", "B", "201", 143),
                (1122020, "清水河", "品学楼", "B", "202", 143),
                (1122030, "清水河", "品学楼", "B", "203", 163),
                (1122040, "清水河", "品学楼", "B", "204", 113),
                (1122050, "清水河", "品学楼", "B", "205", 114),
                (1122060, "清水河", "品学楼", "B", "206", 113),
                (1122070, "清水河", "品学楼", "B", "207", 113),
                (1122130, "清水河", "品学楼", "B", "213", 113),
                (1123010, "清水河", "品学楼", "B", "301", 145),
                (1123020, "清水河", "品学楼", "B", "302", 142),
                (1123030, "清水河", "品学楼", "B", "303", 164),
                (1123040, "清水河", "品学楼", "B", "304", 114),
                (1123050, "清水河", "品学楼", "B", "305", 114),
                (1123060, "清水河", "品学楼", "B", "306", 113),
                (1123070, "清水河", "品学楼", "B", "307", 114),
                (1123080, "清水河", "品学楼", "B", "308", 164),
                (1123090, "清水河", "品学楼", "B", "309", 163),
                (1123100, "清水河", "品学楼", "B", "310", 114),
                (1123110, "清水河", "品学楼", "B", "311", 113),
                (1123120, "清水河", "品学楼", "B", "312", 113),
                (2201030, "沙河", "二教", "-", "103", 280),
                (2201040, "沙河", "二教", "-", "104", 176),
                (2201050, "沙河", "二教", "-", "105", 176),
                (2201060, "沙河", "二教", "-", "106", 176),
                (2201070, "沙河", "二教", "-", "107", 176),
                (2201080, "沙河", "二教", "-", "108", 280),
                (2202030, "沙河", "二教", "-", "203", 280),
                (2202040, "沙河", "二教", "-", "204", 176),
                (2202050, "沙河", "二教", "-", "205", 176),
                (2202060, "沙河", "二教", "-", "206", 176),
                (2202070, "沙河", "二教", "-", "207", 176),
                (2203030, "沙河", "二教", "-", "303", 280),
            ]

            for name in campus_name:
                session.add(SQL_Campus(name=name))
            for name in college_name:
                session.add(SQL_College(name=name))
            for name, chake, chazao, datamanager in group_prof:
                session.add(
                    SQL_Group(
                        name=name, chake=chake, chazao=chazao, datamanager=datamanager
                    )
                )
            session.flush()  # 确保上面新增的数据已写入
            campus_name_to_id = {c.name: c.id for c in session.query(SQL_Campus).all()}
            college_name_to_id = {
                c.name: c.id for c in session.query(SQL_College).all()
            }
            group_name_to_id = {c.name: c.id for c in session.query(SQL_Group).all()}
            for idx, campus_name_, building, area, room, capacity in classroom_info:
                session.add(
                    SQL_Classroom(
                        id=idx,
                        campus_id=campus_name_to_id[campus_name_],
                        building=building,
                        area=area,
                        room_number=room,
                        capacity=capacity,
                    )
                )

            new_user = SQL_User(student_id="202411223344", name="Squirrel", gender="男")
            new_user.profile = SQL_UserProfile(
                campus_id=campus_name_to_id["清水河"],
                college_id=college_name_to_id["信息与通信工程学院"],
                hometown="安徽省合肥市",
                dormitory_yuan="学知苑",
                ethnicity="汉族",
                dormitory_dong=1,
                dormitory_hao=103,
                phone="18100500555",
                qq="1531030000",
            )
            new_user.profile.credential = SQL_UserCredential(
                password_hash=hashlib.sha512("test1234".encode()).digest()
            )
            new_user.profile.payment_info = SQL_PaymentInfo(
                recipient_name="Squirrel",
                recipient_id="202411223344",
                card_number=6217003810050000000,
                is_registered_poor=1,
            )
            new_user.profile.empty_time = SQL_EmptyTime(
                mon="00000",
                tue="00100",
                wed="00020",
                thu="00003",
                fri="30000",
                sat="02000",
                sun="00100",
            )
            membership1 = SQL_GroupMember(
                student_id="202411223344",
                group_id=group_name_to_id["队长组"],
                role="manager",
                wage=350.0,
                display_title="正队长",
            )
            membership2 = SQL_GroupMember(
                student_id="202411223344",
                group_id=group_name_to_id["数据组"],
                role="member",
                wage=0.0,
                display_title="组员",
                remark="兼职无工资",
            )
            session.add(new_user)
            session.add(membership1)
            session.add(membership2)
            session.commit()

            new_user = SQL_User(
                student_id="202411223355", name="BlackSquirrel", gender="女"
            )
            session.add(new_user)
            session.commit()

            black_one = SQL_Blacklist(
                student_id="202411223355",
                reason="数据造假",
                start_time=datetime.date.today(),
            )
            session.add(black_one)
            session.commit()

            import_users_from_json(session)


# 从database/data.json数据导入用户信息，且重置密码为学号
def get_or_create(model, session, defaults=None, **kwargs):
    instance = session.query(model).filter_by(**kwargs).first()
    if instance:
        return instance
    else:
        params = dict(**kwargs)
        if defaults:
            params.update(defaults)
        instance = model(**params)
        session.add(instance)
        session.flush()  # 获取自增主键
        return instance


# 从database/data.json数据导入用户信息，且重置密码为学号
def import_users_from_json(session):
    import hashlib

    # 从json文件加载
    with open("database/data.json", "r", encoding="utf-8") as f:
        user_list = json.load(f)

    for record in user_list:
        # 1. 先保证 campus & college 存在，然后得到其 id
        campus = get_or_create(SQL_Campus, session, name=record["campus"])
        college = get_or_create(SQL_College, session, name=record["school_name"])

        # 2. 构造用户及其“级联关系”对象
        user = SQL_User(
            student_id=record["student_id"],
            name=record["name"],
            gender=record["gender"],
            profile=SQL_UserProfile(
                campus_id=campus.id,
                college_id=college.id,
                dormitory_yuan=record["dormitory_yuan"],
                dormitory_dong=int(record["dormitory_dong"]),
                dormitory_hao=int(record["dormitory_hao"]),
                hometown=record["hometown"],
                ethnicity=record["ethnicity"],
                phone=record["phone"],
                qq=record["qq"],
                # 增加初始密码（sha512，明文为学号）
                credential=SQL_UserCredential(
                    password_hash=hashlib.sha512(record["student_id"].encode()).digest()
                ),
                payment_info=SQL_PaymentInfo(
                    recipient_id=record["application_student_id"],
                    recipient_name=record["application_name"],
                    card_number=record["application_bankcard"],
                    is_registered_poor=bool(record.get("subsidy_dossier", 0)),
                ),
                empty_time=SQL_EmptyTime(
                    mon=record["mon"],
                    tue=record["tue"],
                    wed=record["wed"],
                    thu=record["thu"],
                    fri=record["fri"],
                    sat=record["sat"],
                    sun=record["sun"],
                ),
            ),
        )
        session.add(user)
    session.commit()
