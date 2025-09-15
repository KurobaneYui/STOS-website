"""
STSA岗位管理系统的sqlite3数据库定义，与数据库可视化脚本
"""

import enum
import datetime
from typing import List, Optional

from sqlalchemy import (
    DDL,
    Date,
    event,
    ForeignKey,
    Integer,
    Boolean,
    Float,
    LargeBinary,
    String,
    Text,
    TIMESTAMP,
    UniqueConstraint,
    func,
    Enum as SQLAlchemyEnum,
)
from sqlalchemy.orm import DeclarativeBase, Mapped, mapped_column, relationship
from sqlalchemy.schema import MetaData


class UserRole(enum.Enum):
    manager = "manager"
    member = "member"


# --- 3. ORM基础类定义 ---
class Base(DeclarativeBase):
    metadata = MetaData()
    type_annotation_map = {
        datetime.datetime: TIMESTAMP(timezone=True),
    }


# --- 4. 模型类定义 (表结构) ---


# 核心身份表 (长期驻留)
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

    # 1对1关系: 一个User对应一个UserProfile
    profile: Mapped["UserProfile"] = relationship(
        back_populates="user",
        cascade="all, delete-orphan",
        uselist=False,
    )
    blacklist_entry: Mapped["Blacklist"] = relationship(
        back_populates="user",
        cascade="all, delete-orphan",
        uselist=False,
    )


# 用户画像/档案表 (可删除，并级联删除所有关联数据)
class UserProfile(Base):
    __tablename__ = "user_profiles"
    student_id: Mapped[str] = mapped_column(
        ForeignKey("users.student_id", ondelete="CASCADE"), primary_key=True
    )
    campus_id: Mapped[Optional[int]] = mapped_column(
        ForeignKey("campuses.id", ondelete="SET NULL", onupdate="CASCADE"),
    )
    college_id: Mapped[Optional[int]] = mapped_column(
        ForeignKey("colleges.id", ondelete="SET NULL", onupdate="CASCADE"),
    )
    dormitory_yuan: Mapped[str] = mapped_column(String(20), nullable=False)
    dormitory_dong: Mapped[int] = mapped_column(Integer, nullable=False)
    dormitory_hao: Mapped[int] = mapped_column(Integer, nullable=False)
    hometown: Mapped[str] = mapped_column(String(100), nullable=False)
    ethnicity: Mapped[str] = mapped_column(String(50), nullable=False)
    phone: Mapped[str] = mapped_column(String(20), nullable=False)
    qq: Mapped[str] = mapped_column(String(20), nullable=False)
    updated_at: Mapped[datetime.datetime] = mapped_column(
        server_default=func.now(),
        onupdate=func.now(),
    )

    # Back-references to User, Campus, College
    user: Mapped["User"] = relationship(back_populates="profile")
    campus: Mapped["Campus"] = relationship(back_populates="user_profiles")
    college: Mapped["College"] = relationship(back_populates="user_profiles")

    credential: Mapped["UserCredential"] = relationship(
        back_populates="profile", cascade="all, delete-orphan"
    )
    payment_info: Mapped["PaymentInfo"] = relationship(
        back_populates="profile", cascade="all, delete-orphan"
    )
    empty_time: Mapped["EmptyTime"] = relationship(
        back_populates="profile", cascade="all, delete-orphan", uselist=False
    )
    collected_infos: Mapped[List["CollectedInfo"]] = relationship(
        back_populates="profile", cascade="all, delete-orphan"
    )
    group_memberships: Mapped[List["GroupMember"]] = relationship(
        back_populates="profile", cascade="all, delete-orphan"
    )
    check_in_tasks: Mapped[List["CheckInTask"]] = relationship(
        back_populates="profile", cascade="all, delete-orphan"
    )
    inspection_tasks: Mapped[List["InspectionTask"]] = relationship(
        back_populates="profile", cascade="all, delete-orphan"
    )


class UserCredential(Base):
    __tablename__ = "user_credentials"
    student_id: Mapped[str] = mapped_column(
        ForeignKey("user_profiles.student_id", ondelete="CASCADE"), primary_key=True
    )
    password_hash: Mapped[bytes] = mapped_column(LargeBinary, nullable=False)
    updated_at: Mapped[datetime.datetime] = mapped_column(
        server_default=func.now(),
        onupdate=func.now(),
    )
    profile: Mapped["UserProfile"] = relationship(back_populates="credential")


class PaymentInfo(Base):
    __tablename__ = "payment_info"
    student_id: Mapped[str] = mapped_column(
        ForeignKey("user_profiles.student_id", ondelete="CASCADE"), primary_key=True
    )
    recipient_id: Mapped[str] = mapped_column(String(20), nullable=False)
    recipient_name: Mapped[str] = mapped_column(String(100), nullable=False)
    card_number: Mapped[str] = mapped_column(String(50), nullable=False)
    is_registered_poor: Mapped[bool] = mapped_column(
        Boolean, nullable=False, default=False
    )
    updated_at: Mapped[datetime.datetime] = mapped_column(
        server_default=func.now(),
        onupdate=func.now(),
    )
    profile: Mapped["UserProfile"] = relationship(back_populates="payment_info")


class EmptyTime(Base):
    __tablename__ = "empty_time"
    student_id: Mapped[str] = mapped_column(
        ForeignKey("user_profiles.student_id", ondelete="CASCADE"), primary_key=True
    )
    mon: Mapped[str] = mapped_column(
        String(15), nullable=False, comment="Bitmask for Monday's free time slots"
    )
    tue: Mapped[str] = mapped_column(
        String(15), nullable=False, comment="Bitmask for Tuesday's free time slots"
    )
    wed: Mapped[str] = mapped_column(
        String(15), nullable=False, comment="Bitmask for Wednesday's free time slots"
    )
    thu: Mapped[str] = mapped_column(
        String(15), nullable=False, comment="Bitmask for Thursday's free time slots"
    )
    fri: Mapped[str] = mapped_column(
        String(15), nullable=False, comment="Bitmask for Friday's free time slots"
    )
    sat: Mapped[str] = mapped_column(
        String(15), nullable=False, comment="Bitmask for Saturday's free time slots"
    )
    sun: Mapped[str] = mapped_column(
        String(15), nullable=False, comment="Bitmask for Sunday's free time slots"
    )
    created_at: Mapped[datetime.datetime] = mapped_column(server_default=func.now())
    updated_at: Mapped[datetime.datetime] = mapped_column(
        server_default=func.now(),
        onupdate=func.now(),
    )
    profile: Mapped["UserProfile"] = relationship(back_populates="empty_time")


class CollectedInfo(Base):
    __tablename__ = "collected_info"
    id: Mapped[int] = mapped_column(primary_key=True)
    student_id: Mapped[str] = mapped_column(
        ForeignKey("user_profiles.student_id", ondelete="CASCADE"), nullable=False
    )
    ip_address: Mapped[Optional[str]] = mapped_column(String(45))
    address_info: Mapped[Optional[str]] = mapped_column(Text)
    language_info: Mapped[Optional[str]] = mapped_column(String(200))
    user_agent: Mapped[Optional[str]] = mapped_column(Text)
    login_result: Mapped[Optional[str]] = mapped_column(String(10))
    created_at: Mapped[datetime.datetime] = mapped_column(
        server_default=func.now(),
    )
    profile: Mapped["UserProfile"] = relationship(back_populates="collected_infos")


class Blacklist(Base):
    __tablename__ = "blacklist"
    student_id: Mapped[str] = mapped_column(
        ForeignKey("users.student_id", ondelete="CASCADE"), primary_key=True
    )
    reason: Mapped[str] = mapped_column(Text, nullable=False)
    start_time: Mapped[datetime.date] = mapped_column(Date, nullable=False)
    created_at: Mapped[datetime.datetime] = mapped_column(server_default=func.now())
    user: Mapped["User"] = relationship(back_populates="blacklist_entry")


class Group(Base):
    __tablename__ = "groups"
    id: Mapped[int] = mapped_column(primary_key=True)
    name: Mapped[str] = mapped_column(String(100), nullable=False, unique=True)
    chazao: Mapped[bool] = mapped_column(Boolean, nullable=False, default=False)
    chake: Mapped[bool] = mapped_column(Boolean, nullable=False, default=False)
    datamanager: Mapped[bool] = mapped_column(Boolean, nullable=False, default=False)
    remark: Mapped[Optional[str]] = mapped_column(Text, nullable=True)
    created_at: Mapped[datetime.datetime] = mapped_column(server_default=func.now())
    updated_at: Mapped[datetime.datetime] = mapped_column(
        server_default=func.now(),
        onupdate=func.now(),
    )
    members: Mapped[List["GroupMember"]] = relationship(
        back_populates="group", cascade="all, delete-orphan"
    )
    check_in_tasks: Mapped[List["CheckInTask"]] = relationship(back_populates="group")
    inspection_tasks: Mapped[List["InspectionTask"]] = relationship(
        back_populates="group"
    )


class GroupMember(Base):
    __tablename__ = "group_members"
    id: Mapped[int] = mapped_column(primary_key=True)
    group_id: Mapped[int] = mapped_column(
        ForeignKey("groups.id", ondelete="CASCADE", onupdate="CASCADE"), nullable=False
    )
    student_id: Mapped[str] = mapped_column(
        ForeignKey("user_profiles.student_id", ondelete="CASCADE"), nullable=False
    )
    role: Mapped[UserRole] = mapped_column(
        SQLAlchemyEnum(UserRole, name="role_type"), nullable=False
    )
    wage: Mapped[float] = mapped_column(Float, nullable=False)
    display_title: Mapped[Optional[str]] = mapped_column(String(100))
    remark: Mapped[Optional[str]] = mapped_column(Text, nullable=True)
    created_at: Mapped[datetime.datetime] = mapped_column(server_default=func.now())

    group: Mapped["Group"] = relationship(back_populates="members")
    profile: Mapped["UserProfile"] = relationship(back_populates="group_memberships")

    __table_args__ = (
        UniqueConstraint("group_id", "student_id", "role", name="uq_group_member_role"),
    )


class Campus(Base):
    __tablename__ = "campuses"
    id: Mapped[int] = mapped_column(Integer, primary_key=True)
    name: Mapped[str] = mapped_column(String(100), nullable=False, unique=True)
    user_profiles: Mapped[List["UserProfile"]] = relationship(back_populates="campus")
    classrooms: Mapped[List["Classroom"]] = relationship(back_populates="campus")


class College(Base):
    __tablename__ = "colleges"
    id: Mapped[int] = mapped_column(primary_key=True)
    name: Mapped[str] = mapped_column(String(100), nullable=False, unique=True)
    user_profiles: Mapped[List["UserProfile"]] = relationship(back_populates="college")
    study_schedules: Mapped[List["StudySchedule"]] = relationship(
        back_populates="college"
    )
    course_schedules: Mapped[List["CourseSchedule"]] = relationship(
        back_populates="college"
    )


class Classroom(Base):
    __tablename__ = "classrooms"
    id: Mapped[str] = mapped_column(String(50), primary_key=True)
    campus_id: Mapped[int] = mapped_column(
        ForeignKey("campuses.id", ondelete="CASCADE", onupdate="CASCADE"),
        nullable=False,
    )
    building: Mapped[str] = mapped_column(String(20), nullable=False)
    area: Mapped[str] = mapped_column(String(10), nullable=False)
    room_number: Mapped[str] = mapped_column(String(10), nullable=False)
    capacity: Mapped[int] = mapped_column(Integer, nullable=False)
    campus: Mapped["Campus"] = relationship(back_populates="classrooms")
    study_schedules: Mapped[List["StudySchedule"]] = relationship(
        back_populates="classroom"
    )
    course_schedules: Mapped[List["CourseSchedule"]] = relationship(
        back_populates="classroom"
    )
    __table_args__ = (
        UniqueConstraint(
            "campus_id", "building", "area", "room_number", name="uq_classroom_location"
        ),
    )


class StudySchedule(Base):
    __tablename__ = "study_schedules"
    id: Mapped[int] = mapped_column(primary_key=True)
    date: Mapped[datetime.date] = mapped_column(nullable=False)
    classroom_id: Mapped[str] = mapped_column(
        ForeignKey("classrooms.id", ondelete="CASCADE", onupdate="CASCADE"),
        nullable=False,
    )
    college_id: Mapped[int] = mapped_column(
        ForeignKey("colleges.id", ondelete="CASCADE", onupdate="CASCADE"),
        nullable=False,
    )
    expected_headcount: Mapped[int] = mapped_column(Integer, nullable=False)
    remark: Mapped[str] = mapped_column(Text, nullable=False)
    created_at: Mapped[datetime.datetime] = mapped_column(server_default=func.now())
    updated_at: Mapped[datetime.datetime] = mapped_column(
        server_default=func.now(),
        onupdate=func.now(),
    )
    classroom: Mapped["Classroom"] = relationship(back_populates="study_schedules")
    college: Mapped["College"] = relationship(back_populates="study_schedules")
    check_in_task: Mapped["CheckInTask"] = relationship(
        back_populates="schedule", cascade="all, delete-orphan", uselist=False
    )


class CheckInTask(Base):
    __tablename__ = "check_in_tasks"
    id: Mapped[int] = mapped_column(primary_key=True)
    student_id: Mapped[str] = mapped_column(
        ForeignKey("user_profiles.student_id", ondelete="SET NULL"), nullable=False
    )
    group_id: Mapped[int] = mapped_column(
        ForeignKey("groups.id", ondelete="SET NULL", onupdate="CASCADE"), nullable=False
    )
    schedule_id: Mapped[int] = mapped_column(
        ForeignKey("study_schedules.id", ondelete="CASCADE", onupdate="CASCADE"),
        nullable=False,
        unique=True,
    )
    created_at: Mapped[datetime.datetime] = mapped_column(server_default=func.now())
    schedule: Mapped["StudySchedule"] = relationship(back_populates="check_in_task")
    profile: Mapped["UserProfile"] = relationship(back_populates="check_in_tasks")
    group: Mapped["Group"] = relationship(back_populates="check_in_tasks")
    data: Mapped["CheckInData"] = relationship(
        back_populates="task", cascade="all, delete-orphan"
    )


class CheckInData(Base):
    __tablename__ = "check_in_data"
    id: Mapped[int] = mapped_column(primary_key=True)
    task_id: Mapped[int] = mapped_column(
        ForeignKey("check_in_tasks.id", ondelete="CASCADE", onupdate="CASCADE"),
        nullable=False,
        unique=True,
    )
    first_count: Mapped[int] = mapped_column(Integer, nullable=False)
    second_count: Mapped[int] = mapped_column(Integer, nullable=False)
    leave: Mapped[int] = mapped_column(Integer, nullable=False)
    late: Mapped[int] = mapped_column(Integer, nullable=False)
    absentee: Mapped[int] = mapped_column(Integer, nullable=False)
    early_leave: Mapped[int] = mapped_column(Integer, nullable=False)
    absent_list: Mapped[Optional[str]] = mapped_column(Text, nullable=True)
    leave_list: Mapped[Optional[str]] = mapped_column(Text, nullable=True)
    remarks: Mapped[Optional[str]] = mapped_column(Text)
    status: Mapped[str] = mapped_column(String(20), nullable=False, default="pending")
    created_at: Mapped[datetime.datetime] = mapped_column(server_default=func.now())
    updated_at: Mapped[datetime.datetime] = mapped_column(
        server_default=func.now(),
        onupdate=func.now(),
    )
    task: Mapped["CheckInTask"] = relationship(back_populates="data")


class CourseSchedule(Base):
    __tablename__ = "course_schedules"
    id: Mapped[int] = mapped_column(primary_key=True)
    date: Mapped[datetime.date] = mapped_column(nullable=False)
    time_slot: Mapped[str] = mapped_column(String(20), nullable=False)
    classroom_id: Mapped[str] = mapped_column(
        ForeignKey("classrooms.id", ondelete="CASCADE", onupdate="CASCADE"),
        nullable=False,
    )
    college_id: Mapped[int] = mapped_column(
        ForeignKey("colleges.id", ondelete="CASCADE", onupdate="CASCADE"),
        nullable=False,
    )
    expected_headcount: Mapped[int] = mapped_column(Integer, nullable=False)
    created_at: Mapped[datetime.datetime] = mapped_column(server_default=func.now())
    updated_at: Mapped[datetime.datetime] = mapped_column(
        server_default=func.now(),
        onupdate=func.now(),
    )
    classroom: Mapped["Classroom"] = relationship(back_populates="course_schedules")
    college: Mapped["College"] = relationship(back_populates="course_schedules")
    inspection_task: Mapped["InspectionTask"] = relationship(
        back_populates="schedule", cascade="all, delete-orphan", uselist=False
    )


class InspectionTask(Base):
    __tablename__ = "inspection_tasks"
    id: Mapped[int] = mapped_column(primary_key=True)
    student_id: Mapped[str] = mapped_column(
        ForeignKey("user_profiles.student_id", ondelete="SET NULL"), nullable=False
    )
    group_id: Mapped[int] = mapped_column(
        ForeignKey("groups.id", ondelete="SET NULL", onupdate="CASCADE"), nullable=False
    )
    schedule_id: Mapped[int] = mapped_column(
        ForeignKey("course_schedules.id", ondelete="CASCADE", onupdate="CASCADE"),
        nullable=False,
        unique=True,
    )
    created_at: Mapped[datetime.datetime] = mapped_column(server_default=func.now())
    schedule: Mapped["CourseSchedule"] = relationship(back_populates="inspection_task")
    profile: Mapped["UserProfile"] = relationship(back_populates="inspection_tasks")
    group: Mapped["Group"] = relationship(back_populates="inspection_tasks")
    data: Mapped["InspectionData"] = relationship(
        back_populates="task", cascade="all, delete-orphan"
    )


class InspectionData(Base):
    __tablename__ = "inspection_data"
    id: Mapped[int] = mapped_column(primary_key=True)
    task_id: Mapped[int] = mapped_column(
        ForeignKey("inspection_tasks.id", ondelete="CASCADE", onupdate="CASCADE"),
        nullable=False,
        unique=True,
    )
    first_count: Mapped[int] = mapped_column(Integer, nullable=False)
    second_count: Mapped[int] = mapped_column(Integer, nullable=False)
    first_absentee: Mapped[int] = mapped_column(Integer, nullable=False)
    second_absentee: Mapped[int] = mapped_column(Integer, nullable=False)
    remarks: Mapped[Optional[str]] = mapped_column(Text, nullable=True)
    status: Mapped[str] = mapped_column(String(20), nullable=False, default="pending")
    created_at: Mapped[datetime.datetime] = mapped_column(server_default=func.now())
    updated_at: Mapped[datetime.datetime] = mapped_column(
        server_default=func.now(),
        onupdate=func.now(),
    )
    task: Mapped["InspectionTask"] = relationship(back_populates="data")


# --- 5. 视图定义 ---
class ContactView(Base):
    __tablename__ = "contact_view"
    # This view is not managed by Alembic/SQLAlchemy's table creation.
    # It is created via the DDL event listener below.
    __table_args__ = {"info": {"is_view": True}}

    id: Mapped[int] = mapped_column(Integer, primary_key=True)
    name: Mapped[str] = mapped_column(String)
    gender: Mapped[str] = mapped_column(String)
    qq: Mapped[Optional[str]] = mapped_column(String)
    phone: Mapped[Optional[str]] = mapped_column(String)
    department: Mapped[str] = mapped_column(String)
    department_id: Mapped[int] = mapped_column(Integer)
    job: Mapped[str] = mapped_column(String)
    display_title: Mapped[Optional[str]] = mapped_column(String(100))


class WageView(Base):
    __tablename__ = "wage_view"
    __table_args__ = {"info": {"is_view": True}}

    row_number: Mapped[int] = mapped_column(Integer, primary_key=True)
    name: Mapped[str] = mapped_column(String)
    student_id: Mapped[str] = mapped_column(String)
    department_name: Mapped[str] = mapped_column(String)
    department_id: Mapped[int] = mapped_column(Integer)
    job: Mapped[str] = mapped_column(String)
    display_title: Mapped[Optional[str]] = mapped_column(String(100))
    wage: Mapped[float] = mapped_column(Float)
    work_remark: Mapped[Optional[str]] = mapped_column(Text)
    application_name: Mapped[Optional[str]] = mapped_column(String)
    application_student_id: Mapped[Optional[str]] = mapped_column(String)
    application_bankcard: Mapped[Optional[str]] = mapped_column(String)
    subsidy_dossier: Mapped[int] = mapped_column(Integer)
    wageinfo_remark: Mapped[Optional[str]] = mapped_column(Text)


# DDL for creating the view
create_contact_view_sql = """
CREATE VIEW IF NOT EXISTS contact_view AS
SELECT
    ROW_NUMBER() OVER (ORDER BY gm.group_id, gm.role) AS id,
    u.name,
    u.gender,
    up.qq,
    up.phone,
    g.name AS department,
    g.id AS department_id,
    gm.role AS job,
    gm.display_title AS display_title
FROM group_members AS gm
JOIN user_profiles AS up ON gm.student_id = up.student_id
JOIN users AS u ON up.student_id = u.student_id
JOIN groups AS g ON gm.group_id = g.id
"""
create_wage_view_sql = """
CREATE VIEW IF NOT EXISTS wage_view AS
SELECT
    ROW_NUMBER() OVER (ORDER BY gm.role, g.id) AS row_number,
    u.name,
    up.student_id,
    g.name AS department_name,
    g.id AS department_id,
    gm.role AS job,
    gm.display_title AS display_title,
    gm.wage,
    g.remark AS work_remark,
    pi.recipient_name AS application_name,
    pi.recipient_id AS application_student_id,
    pi.card_number AS application_bankcard,
    pi.is_registered_poor AS subsidy_dossier,
    gm.remark AS wageinfo_remark
FROM group_members AS gm
LEFT JOIN groups AS g ON gm.group_id = g.id
LEFT JOIN user_profiles AS up ON gm.student_id = up.student_id
LEFT JOIN users AS u ON up.student_id = u.student_id
LEFT JOIN payment_info AS pi ON up.student_id = pi.student_id
"""
create_contact_view_ddl = DDL(create_contact_view_sql)
create_wage_view_ddl = DDL(create_wage_view_sql)

# DDL for dropping the view
drop_contact_view_sql = "DROP VIEW IF EXISTS contact_view"
drop_wage_view_sql = "DROP VIEW IF EXISTS wage_view"
drop_contact_view_ddl = DDL(drop_contact_view_sql)
drop_wage_view_ddl = DDL(drop_wage_view_sql)


# Event listeners to create and drop the view
event.listen(Base.metadata, "after_create", create_contact_view_ddl)
event.listen(Base.metadata, "before_drop", drop_contact_view_ddl)
event.listen(Base.metadata, "after_create", create_wage_view_ddl)
event.listen(Base.metadata, "before_drop", drop_wage_view_ddl)


if __name__ == "__main__":
    import os
    import json
    from eralchemy2 import render_er

    with open("config/STSA_APP.conf", "r") as f:
        config = json.load(f)
    DB_FILE = config["DBpath"]
    os.makedirs(os.path.dirname(DB_FILE), exist_ok=True)
    render_er(Base.metadata, os.path.dirname(DB_FILE) + "/dbschema.svg")
