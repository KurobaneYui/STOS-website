import os
import datetime
import sqlite3

from sqlalchemy import (
    create_engine, event, ForeignKey, String, Integer, Text, TIMESTAMP, CheckConstraint,
    UniqueConstraint, func
)
from sqlalchemy.orm import (
    DeclarativeBase, Mapped, mapped_column, relationship, sessionmaker
)
from sqlalchemy.exc import IntegrityError
from typing import List, Optional

# --- 1. 数据库设置 ---
# 定义数据库文件路径和连接URL
DB_FILE = os.path.join("database", "teaching_club.db")
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


# --- 3. ORM基础类定义 ---
# 所有模型类都将继承这个Base类
class Base(DeclarativeBase):
    # 为所有的 TIMESTAMP 类型设置 timezone=True
    type_annotation_map = {
        datetime.datetime: TIMESTAMP(timezone=True),
    }


# --- 4. 模型类定义 (表结构) ---


# 核心身份与信息表
class User(Base):
    __tablename__ = "users"
    student_id: Mapped[str] = mapped_column(String(20), primary_key=True)
    name: Mapped[str] = mapped_column(String(50), nullable=False)
    gender: Mapped[str] = mapped_column(String(10), nullable=False)
    created_at: Mapped[datetime.datetime] = mapped_column(server_default=func.now())
    updated_at: Mapped[datetime.datetime] = mapped_column(
        server_default=func.now(),
        onupdate=func.now(),
    )

    # Relationships
    profile: Mapped["UserProfile"] = relationship(
        back_populates="user", cascade="all, delete-orphan"
    )
    credential: Mapped["UserCredential"] = relationship(
        back_populates="user", cascade="all, delete-orphan"
    )
    payment_info: Mapped["PaymentInfo"] = relationship(
        back_populates="user", cascade="all, delete-orphan"
    )
    collected_info: Mapped[
        List["CollectedInfo"]
    ] = relationship(back_populates="user", cascade="all, delete-orphan")
    blacklist_entry: Mapped["Blacklist"] = relationship(
        back_populates="user", cascade="all, delete-orphan"
    )
    group_memberships: Mapped[
        List["GroupMember"]
    ] = relationship(back_populates="user", cascade="all, delete-orphan")
    data_permissions_granted: Mapped[List["DataGroupPermission"]] = relationship(
        back_populates="grantee",
        foreign_keys="[DataGroupPermission.student_id]",
        cascade="all, delete-orphan"
    )
    data_permissions_given: Mapped[List["DataGroupPermission"]] = relationship(
        back_populates="granter",
        foreign_keys="[DataGroupPermission.granted_by]",
        cascade="all, delete-orphan"
    )
    check_in_tasks: Mapped[
        List["CheckInTask"]
    ] = relationship(back_populates="user", cascade="all, delete-orphan")
    inspection_tasks: Mapped[
        List["InspectionTask"]
    ] = relationship(back_populates="user", cascade="all, delete-orphan")


class UserProfile(Base):
    __tablename__ = "user_profiles"
    student_id: Mapped[str] = mapped_column(
        ForeignKey("users.student_id", ondelete="CASCADE"), primary_key=True
    )
    campus_id: Mapped[Optional[str]
                      ] = mapped_column(ForeignKey("campuses.id", ondelete="SET NULL"))
    college_id: Mapped[Optional[int]
                       ] = mapped_column(ForeignKey("colleges.id", ondelete="SET NULL"))
    phone: Mapped[Optional[str]] = mapped_column(String(20))
    qq: Mapped[Optional[str]] = mapped_column(String(20))
    updated_at: Mapped[datetime.datetime
                       ] = mapped_column(server_default=func.now(), onupdate=func.now())

    # Relationships
    user: Mapped["User"] = relationship(back_populates="profile")
    campus: Mapped["Campus"] = relationship(back_populates="user_profiles")
    college: Mapped["College"] = relationship(back_populates="user_profiles")


class UserCredential(Base):
    __tablename__ = "user_credentials"
    student_id: Mapped[str] = mapped_column(
        ForeignKey("users.student_id", ondelete="CASCADE"), primary_key=True
    )
    password_hash: Mapped[str] = mapped_column(Text, nullable=False)
    updated_at: Mapped[datetime.datetime
                       ] = mapped_column(server_default=func.now(), onupdate=func.now())

    user: Mapped["User"] = relationship(back_populates="credential")


class PaymentInfo(Base):
    __tablename__ = "payment_info"
    student_id: Mapped[str] = mapped_column(
        ForeignKey("users.student_id", ondelete="CASCADE"), primary_key=True
    )
    recipient_name: Mapped[str] = mapped_column(String(100), nullable=False)
    card_number: Mapped[str] = mapped_column(String(50), nullable=False)
    is_registered_poor: Mapped[int] = mapped_column(Integer, nullable=False, default=0)
    updated_at: Mapped[datetime.datetime
                       ] = mapped_column(server_default=func.now(), onupdate=func.now())

    user: Mapped["User"] = relationship(back_populates="payment_info")


class CollectedInfo(Base):
    __tablename__ = "collected_info"
    id: Mapped[int] = mapped_column(primary_key=True)
    student_id: Mapped[str] = mapped_column(
        ForeignKey("users.student_id", ondelete="CASCADE"), nullable=False
    )
    ip_address: Mapped[Optional[str]] = mapped_column(String(45))  # 支持IPv6
    address_info: Mapped[Optional[str]] = mapped_column(Text)  # JSON格式存储地理位置信息
    language_info: Mapped[Optional[str]] = mapped_column(String(200))  # 语言偏好
    user_agent: Mapped[Optional[str]] = mapped_column(Text)  # 用户代理字符串
    login_result: Mapped[Optional[str]
                         ] = mapped_column(String(10))  # 登录结果信息: "success", "failure"
    created_at: Mapped[datetime.datetime] = mapped_column(server_default=func.now())

    # Relationship
    user: Mapped["User"] = relationship(back_populates="collected_info")


class Blacklist(Base):
    __tablename__ = "blacklist"
    student_id: Mapped[str] = mapped_column(
        ForeignKey("users.student_id", ondelete="CASCADE"), primary_key=True
    )
    reason: Mapped[str] = mapped_column(Text, nullable=False)
    created_at: Mapped[datetime.datetime] = mapped_column(server_default=func.now())

    user: Mapped["User"] = relationship(back_populates="blacklist_entry")


# 组织与权限结构表
class Group(Base):
    __tablename__ = "groups"
    id: Mapped[int] = mapped_column(primary_key=True)
    name: Mapped[str] = mapped_column(String(100), nullable=False, unique=True)
    created_at: Mapped[datetime.datetime] = mapped_column(server_default=func.now())
    updated_at: Mapped[datetime.datetime
                       ] = mapped_column(server_default=func.now(), onupdate=func.now())

    members: Mapped[
        List["GroupMember"]
    ] = relationship(back_populates="group", cascade="all, delete-orphan")


class GroupMember(Base):
    __tablename__ = "group_members"
    id: Mapped[int] = mapped_column(primary_key=True)
    group_id: Mapped[int] = mapped_column(
        ForeignKey("groups.id", ondelete="CASCADE"), nullable=False
    )
    student_id: Mapped[str] = mapped_column(
        ForeignKey("users.student_id", ondelete="CASCADE"), nullable=False
    )
    role: Mapped[str] = mapped_column(
        String(20), nullable=False
    )  # "manager" or "member"
    display_title: Mapped[Optional[str]] = mapped_column(String(100))
    created_at: Mapped[datetime.datetime] = mapped_column(server_default=func.now())

    group: Mapped["Group"] = relationship(back_populates="members")
    user: Mapped["User"] = relationship(back_populates="group_memberships")

    __table_args__ = (
        UniqueConstraint('group_id', 'student_id', 'role', name='uq_group_member_role'),
        CheckConstraint("role IN ('manager', 'member')", name='check_role_type'),
    )


class DataGroupPermission(Base):
    __tablename__ = "data_group_permissions"
    id: Mapped[int] = mapped_column(primary_key=True)
    student_id: Mapped[str] = mapped_column(
        ForeignKey("users.student_id", ondelete="CASCADE"), nullable=False
    )
    permission: Mapped[str] = mapped_column(String(100), nullable=False)
    granted_by: Mapped[str] = mapped_column(
        ForeignKey("users.student_id", ondelete="CASCADE"), nullable=False
    )
    created_at: Mapped[datetime.datetime] = mapped_column(server_default=func.now())

    grantee: Mapped["User"] = relationship(
        back_populates="data_permissions_granted", foreign_keys=[student_id]
    )
    granter: Mapped["User"] = relationship(
        back_populates="data_permissions_given", foreign_keys=[granted_by]
    )

    __table_args__ = (
        UniqueConstraint('student_id', 'permission', name='uq_user_permission'),
    )


# 教学单位与任务数据表
class Campus(Base):
    __tablename__ = "campuses"
    id: Mapped[str] = mapped_column(String(50), primary_key=True)
    name: Mapped[str] = mapped_column(String(100), nullable=False, unique=True)

    user_profiles: Mapped[List["UserProfile"]] = relationship(back_populates="campus")
    classrooms: Mapped[List["Classroom"]] = relationship(back_populates="campus")


class College(Base):
    __tablename__ = "colleges"
    id: Mapped[int] = mapped_column(primary_key=True)
    name: Mapped[str] = mapped_column(String(100), nullable=False, unique=True)

    user_profiles: Mapped[List["UserProfile"]] = relationship(back_populates="college")
    study_schedules: Mapped[List["StudySchedule"]
                            ] = relationship(back_populates="college")
    inspection_tasks: Mapped[List["InspectionTask"]
                             ] = relationship(back_populates="college")


class Classroom(Base):
    __tablename__ = "classrooms"
    id: Mapped[str] = mapped_column(String(50), primary_key=True)
    campus_id: Mapped[str] = mapped_column(
        ForeignKey("campuses.id", ondelete="CASCADE"), nullable=False
    )
    building: Mapped[str] = mapped_column(String(100), nullable=False)
    room_number: Mapped[str] = mapped_column(String(50), nullable=False)
    capacity: Mapped[Optional[int]]

    campus: Mapped["Campus"] = relationship(back_populates="classrooms")
    study_schedules: Mapped[List["StudySchedule"]
                            ] = relationship(back_populates="classroom")
    inspection_tasks: Mapped[List["InspectionTask"]
                             ] = relationship(back_populates="classroom")

    __table_args__ = (
        UniqueConstraint(
            'campus_id', 'building', 'room_number', name='uq_classroom_location'
        ),
    )


class StudySchedule(Base):
    __tablename__ = "study_schedules"
    id: Mapped[int] = mapped_column(primary_key=True)
    date: Mapped[datetime.date] = mapped_column(nullable=False)
    classroom_id: Mapped[str] = mapped_column(
        ForeignKey("classrooms.id", ondelete="RESTRICT"), nullable=False
    )
    college_id: Mapped[int] = mapped_column(
        ForeignKey("colleges.id", ondelete="RESTRICT"), nullable=False
    )
    expected_headcount: Mapped[Optional[int]]
    created_at: Mapped[datetime.datetime] = mapped_column(server_default=func.now())
    updated_at: Mapped[datetime.datetime
                       ] = mapped_column(server_default=func.now(), onupdate=func.now())

    classroom: Mapped["Classroom"] = relationship(back_populates="study_schedules")
    college: Mapped["College"] = relationship(back_populates="study_schedules")
    check_in_task: Mapped["CheckInTask"] = relationship(
        back_populates="schedule", cascade="all, delete-orphan"
    )


class CheckInTask(Base):
    __tablename__ = "check_in_tasks"
    id: Mapped[int] = mapped_column(primary_key=True)
    schedule_id: Mapped[int] = mapped_column(
        ForeignKey("study_schedules.id", ondelete="CASCADE"), nullable=False
    )
    student_id: Mapped[str] = mapped_column(
        ForeignKey("users.student_id", ondelete="CASCADE"), nullable=False
    )
    created_at: Mapped[datetime.datetime] = mapped_column(server_default=func.now())

    schedule: Mapped["StudySchedule"] = relationship(back_populates="check_in_task")
    user: Mapped["User"] = relationship(back_populates="check_in_tasks")
    data: Mapped["CheckInData"] = relationship(
        back_populates="task", cascade="all, delete-orphan"
    )


class CheckInData(Base):
    __tablename__ = "check_in_data"
    id: Mapped[int] = mapped_column(primary_key=True)
    task_id: Mapped[int] = mapped_column(
        ForeignKey("check_in_tasks.id", ondelete="CASCADE"), nullable=False, unique=True
    )
    data1: Mapped[Optional[str]] = mapped_column(Text)
    data2: Mapped[Optional[str]] = mapped_column(Text)
    remarks: Mapped[Optional[str]] = mapped_column(Text)
    status: Mapped[str] = mapped_column(
        String(20), nullable=False, default='pending'
    )  # "pending", "confirmed"
    created_at: Mapped[datetime.datetime] = mapped_column(server_default=func.now())
    updated_at: Mapped[datetime.datetime
                       ] = mapped_column(server_default=func.now(), onupdate=func.now())

    task: Mapped["CheckInTask"] = relationship(back_populates="data")


class InspectionTask(Base):
    __tablename__ = 'inspection_tasks'
    id: Mapped[int] = mapped_column(primary_key=True)
    student_id: Mapped[str] = mapped_column(
        ForeignKey('users.student_id', ondelete="CASCADE"), nullable=False
    )
    date: Mapped[datetime.date] = mapped_column(nullable=False)
    time_slot: Mapped[str] = mapped_column(String(20), nullable=False)  # "1-2", "3-4"
    classroom_id: Mapped[str] = mapped_column(
        ForeignKey('classrooms.id', ondelete="RESTRICT"), nullable=False
    )
    college_id: Mapped[int] = mapped_column(
        ForeignKey('colleges.id', ondelete="RESTRICT"), nullable=False
    )
    expected_headcount: Mapped[Optional[int]]
    created_at: Mapped[datetime.datetime] = mapped_column(server_default=func.now())

    user: Mapped["User"] = relationship(back_populates="inspection_tasks")
    classroom: Mapped["Classroom"] = relationship(back_populates="inspection_tasks")
    college: Mapped["College"] = relationship(back_populates="inspection_tasks")
    data: Mapped["InspectionData"] = relationship(
        back_populates="task", cascade="all, delete-orphan"
    )


class InspectionData(Base):
    __tablename__ = "inspection_data"
    id: Mapped[int] = mapped_column(primary_key=True)
    task_id: Mapped[int] = mapped_column(
        ForeignKey("inspection_tasks.id", ondelete="CASCADE"),
        nullable=False,
        unique=True
    )
    data1: Mapped[Optional[str]] = mapped_column(Text)
    data2: Mapped[Optional[str]] = mapped_column(Text)
    remarks: Mapped[Optional[str]] = mapped_column(Text)
    status: Mapped[str] = mapped_column(
        String(20), nullable=False, default='pending'
    )  # "pending", "confirmed"
    created_at: Mapped[datetime.datetime] = mapped_column(server_default=func.now())
    updated_at: Mapped[datetime.datetime
                       ] = mapped_column(server_default=func.now(), onupdate=func.now())

    task: Mapped["InspectionTask"] = relationship(back_populates="data")


# --- 4. 创建 Session 工厂 (推荐) ---
SessionLocal = sessionmaker(autocommit=False, autoflush=False, bind=engine)


# --- 5. 数据库初始化函数 ---
def initialize_database():
    """
    初始化数据库。
    如果数据库文件目录不存在，则创建它。
    使用`Base.metadata.create_all()`来创建所有定义的表（如果它们尚不存在）。
    """
    print(f"Initializing database at: {DB_FILE}")
    db_dir = os.path.dirname(DB_FILE)
    if db_dir:
        os.makedirs(db_dir, exist_ok=True)

    # create_all会安全地检查表是否存在，不存在则创建
    Base.metadata.create_all(engine)
    print("Database and tables initialized successfully.")


def seed_initial_data():
    """
    向数据库中填充一些初始的、基础的数据。
    使用 Session 来执行插入操作，并通过处理 IntegrityError 来避免重复插入。
    """
    # 使用 with 语句确保 Session 被正确关闭
    with SessionLocal() as session:
        try:
            print("\nSeeding initial data...")
            initial_data = [
                Campus(id='HQ', name='总部校区'),
                Campus(id='BRANCH', name='分支校区'),
                College(name='计算机学院'),
                College(name='外国语学院'),
                College(name='理学院'),
                Group(name='队长组'),
                Group(name='数据组'),
                Group(name='查早组'),
            ]

            # 为了避免重复插入，我们可以先查询
            for item in initial_data:
                # 根据类型和唯一键来查询
                if isinstance(item, Campus):
                    exists = session.query(Campus).filter_by(id=item.id).first()
                elif isinstance(item, College):
                    exists = session.query(College).filter_by(name=item.name).first()
                elif isinstance(item, Group):
                    exists = session.query(Group).filter_by(name=item.name).first()
                else:
                    exists = None

                if not exists:
                    session.add(item)

            session.commit()
            print("Initial data (campuses, colleges, groups) seeded successfully.")
        except IntegrityError:
            session.rollback()
            print("Initial data already exists, rolling back.")
        except Exception as e:
            session.rollback()
            print(f"An error occurred during data seeding: {e}")


if __name__ == '__main__':
    # 步骤1: 初始化数据库和表结构
    initialize_database()

    # 步骤2: 填充基础数据
    seed_initial_data()

    # --- 使用示例 ---
    print("\n--- ORM Usage Example ---")
    with SessionLocal() as session:
        # --- 使用示例：如何添加一个用户并分配到组里 ---
        # 检查队长组是否存在
        captain_group = session.query(Group).filter_by(name="队长组").first()
        if not captain_group:
            print("Captain group not found. Please run seeding first.")
        else:
            # 检查用户是否存在
            existing_user = session.query(User).filter_by(student_id="20240001").first()
            if not existing_user:
                print("Creating a new user 'Alice' (20240001)...")
                new_user = User(student_id="20240001", name="Alice", gender="女")

                # 创建用户的扩展信息
                new_user.profile = UserProfile(phone="13800138000", qq="12345678")

                # 创建用户的密码
                new_user.credential = UserCredential(
                    password_hash="a_very_secure_hash_here"
                )

                # 将用户添加到组
                membership = GroupMember(
                    user=new_user,
                    group=captain_group,
                    role="manager",
                    display_title="正队长"
                )

                session.add(new_user)
                session.add(membership)

                session.commit()
                print("User 'Alice' and her membership have been created.")

                # 验证关系是否正常工作
                print(f"User Name: {new_user.name}")
                print(f"User Phone: {new_user.profile.phone}")
                print(f"User is in group: {new_user.group_memberships[0].group.name}")
                print(f"User's role: {new_user.group_memberships[0].role}")

            else:
                print("User 'Alice' (20240001) already exists.")

        # --- 使用示例：获取多条查询记录
        # 获取所有用户
        all_users = session.query(User).all()
        print(f"Total users: {len(all_users)}")

        # 获取所有女性用户
        female_users = session.query(User).filter_by(gender="女").all()

        # 使用filter进行更复杂的查询
        recent_users = session.query(User).filter(
            User.created_at >= datetime.datetime(2024, 1, 1)
        ).all()

        # --- 使用示例：分组查询 ---
        from sqlalchemy import func
        # 按性别分组统计用户数量
        gender_stats = session.query(
            User.gender,
            func.count(User.student_id).label('count')
        ).group_by(User.gender).all()

        for gender, count in gender_stats:
            print(f"{gender}: {count} users")

        # 按学院分组统计用户数量
        college_stats = session.query(
            College.name,
            func.count(UserProfile.student_id).label('user_count')
        ).join(UserProfile).group_by(College.id, College.name).all()

        # --- 使用示例：统计数量 ---
        # 统计总用户数
        total_users = session.query(User).count()
        print(f"Total users: {total_users}")

        # 统计特定条件的用户数
        female_count = session.query(User).filter_by(gender="女").count()
        print(f"Female users: {female_count}")

        # 统计某个组的成员数量
        captain_group_count = session.query(GroupMember).join(Group).filter(
            Group.name == "队长组"
        ).count()

        # --- 使用示例：复杂关联查询
        # 查询所有队长组成员的详细信息
        captain_members = session.query(User, GroupMember, Group).join(
            GroupMember, User.student_id == GroupMember.student_id
        ).join(Group, GroupMember.group_id == Group.id).filter(Group.name == "队长组").all()

        for user, membership, group in captain_members:
            print(
                f"User: {user.name}, "
                f"Role: {membership.role}, "
                f"Title: {membership.display_title}"
            )

        # --- 使用示例：更新和删除操作（修改单条数据） ---
        # 修改特定用户的信息
        user = session.query(User).filter_by(student_id="20240001").first()
        if user:
            user.name = "Alice Updated"
            # 修改关联的profile信息
            if user.profile:
                user.profile.phone = "13900139000"

            session.commit()
            print("User updated successfully")
        else:
            print("User not found")

        # --- 使用示例：批量更新和删除操作（批量修改多条记录） ---
        # 批量更新所有女性用户的某个字段（假设有字段需要更新）
        updated_count = session.query(User).filter_by(gender="女").update({
            User.updated_at:
            func.now()
        })

        session.commit()
        print(f"Updated {updated_count} female users")

        # 批量更新用户profile信息
        session.query(UserProfile).filter(UserProfile.phone.like("138%")
                                          ).update({UserProfile.updated_at: func.now()})
        session.commit()

        # --- 使用示例：条件删除操作
        # 删除特定用户
        user_to_delete = session.query(User).filter_by(student_id="20240001").first()
        if user_to_delete:
            session.delete(user_to_delete)
            session.commit()
            print("User deleted")

        # 批量删除符合条件的记录
        deleted_count = session.query(CollectedInfo).filter(
            CollectedInfo.created_at < datetime.datetime(2024, 1, 1)
        ).delete()

        session.commit()
        print(f"Deleted {deleted_count} old collected info records")

        # --- 使用示例：复杂事务处理 ---
        try:
            # 开始事务
            session.begin()

            # 创建新用户
            new_user = User(student_id="20240002", name="Bob", gender="男")
            session.add(new_user)

            # 同时创建profile
            new_profile = UserProfile(student_id="20240002", phone="13700137000")
            session.add(new_profile)

            # 添加到组中
            group = session.query(Group).filter_by(name="数据组").first()
            if group:
                membership = GroupMember(
                    student_id="20240002", group_id=group.id, role="member"
                )
                session.add(membership)

            # 提交所有更改
            session.commit()
            print("Complex transaction completed successfully")

        except Exception as e:
            session.rollback()
            print(f"Transaction failed: {e}")

        # --- 使用示例：通过ORM关系进行操作 ---
        # 通过关系访问和修改数据
        user = session.query(User).filter_by(student_id="20240001").first()
        if user:
            # 通过关系访问收集的信息
            for info in user.collected_info:
                print(f"IP: {info.ip_address}, Created: {info.created_at}")

            # 添加新的收集信息
            new_info = CollectedInfo(
                student_id=user.student_id,
                ip_address="192.168.1.100",
                user_agent="Mozilla/5.0...",
                login_result="success"
            )
            session.add(new_info)
            session.commit()
