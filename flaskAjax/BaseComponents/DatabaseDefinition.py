import datetime
from typing import List, Optional

from sqlalchemy import (
    CheckConstraint,
    DDL,
    event,
    ForeignKey,
    Integer,
    Float,
    LargeBinary,
    String,
    Text,
    TIMESTAMP,
    UniqueConstraint,
    func,
)
from sqlalchemy.orm import DeclarativeBase, Mapped, mapped_column, relationship
from sqlalchemy.schema import MetaData


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
        back_populates="user", cascade="all, delete-orphan"
    )


# 用户画像/档案表 (可删除，并级联删除所有关联数据)
class UserProfile(Base):
    __tablename__ = "user_profiles"
    student_id: Mapped[str] = mapped_column(
        ForeignKey("users.student_id", ondelete="CASCADE"), primary_key=True
    )
    campus_id: Mapped[Optional[int]] = mapped_column(
        ForeignKey("campuses.id", ondelete="SET NULL"),
    )
    college_id: Mapped[Optional[int]] = mapped_column(
        ForeignKey("colleges.id", ondelete="SET NULL"),
    )
    dormitory_yuan: Mapped[Optional[str]] = mapped_column(String(20), nullable=False)
    dormitory_dong: Mapped[Optional[int]] = mapped_column(Integer, nullable=False)
    dormitory_hao: Mapped[Optional[int]] = mapped_column(Integer, nullable=False)
    hometown: Mapped[Optional[str]] = mapped_column(String(100), nullable=False)
    ethnicity: Mapped[Optional[str]] = mapped_column(String(50), nullable=False)
    phone: Mapped[Optional[str]] = mapped_column(String(20), nullable=False)
    qq: Mapped[Optional[str]] = mapped_column(String(20), nullable=False)
    updated_at: Mapped[datetime.datetime] = mapped_column(
        server_default=func.now(),
        onupdate=func.now(),
    )

    # Back-references to User, Campus, College
    user: Mapped["User"] = relationship(back_populates="profile")
    campus: Mapped["Campus"] = relationship(back_populates="user_profiles")
    college: Mapped["College"] = relationship(back_populates="user_profiles")

    # Relationships moved from User
    credential: Mapped["UserCredential"] = relationship(
        back_populates="profile", cascade="all, delete-orphan"
    )
    payment_info: Mapped["PaymentInfo"] = relationship(
        back_populates="profile", cascade="all, delete-orphan"
    )
    empty_time: Mapped["EmptyTime"] = relationship(
        back_populates="profile", cascade="all, delete-orphan", uselist=False
    )
    collected_info: Mapped[List["CollectedInfo"]] = relationship(
        back_populates="profile", cascade="all, delete-orphan"
    )
    blacklist_entry: Mapped["Blacklist"] = relationship(
        back_populates="profile", cascade="all, delete-orphan"
    )
    group_memberships: Mapped[List["GroupMember"]] = relationship(
        back_populates="profile", cascade="all, delete-orphan"
    )
    data_permissions_granted: Mapped[List["DataGroupPermission"]] = relationship(
        back_populates="grantee_profile",
        foreign_keys="[DataGroupPermission.student_id]",
        cascade="all, delete-orphan",
    )
    data_permissions_given: Mapped[List["DataGroupPermission"]] = relationship(
        back_populates="granter_profile",
        foreign_keys="[DataGroupPermission.granted_by]",
        cascade="all, delete-orphan",
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
    is_registered_poor: Mapped[int] = mapped_column(Integer, nullable=False, default=0)
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
    mon: Mapped[str] = mapped_column(String(15), nullable=False, default=False)
    tue: Mapped[str] = mapped_column(String(15), nullable=False, default=False)
    wed: Mapped[str] = mapped_column(String(15), nullable=False, default=False)
    thu: Mapped[str] = mapped_column(String(15), nullable=False, default=False)
    fri: Mapped[str] = mapped_column(String(15), nullable=False, default=False)
    sat: Mapped[str] = mapped_column(String(15), nullable=False, default=False)
    sun: Mapped[str] = mapped_column(String(15), nullable=False, default=False)
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
    profile: Mapped["UserProfile"] = relationship(back_populates="collected_info")


class Blacklist(Base):
    __tablename__ = "blacklist"
    student_id: Mapped[str] = mapped_column(
        ForeignKey("user_profiles.student_id", ondelete="CASCADE"), primary_key=True
    )
    reason: Mapped[str] = mapped_column(Text, nullable=False)
    created_at: Mapped[datetime.datetime] = mapped_column(server_default=func.now())
    profile: Mapped["UserProfile"] = relationship(back_populates="blacklist_entry")


class Group(Base):
    __tablename__ = "groups"
    id: Mapped[int] = mapped_column(primary_key=True)
    name: Mapped[str] = mapped_column(String(100), nullable=False, unique=True)
    created_at: Mapped[datetime.datetime] = mapped_column(server_default=func.now())
    updated_at: Mapped[datetime.datetime] = mapped_column(
        server_default=func.now(),
        onupdate=func.now(),
    )
    members: Mapped[List["GroupMember"]] = relationship(
        back_populates="group", cascade="all, delete-orphan"
    )


class GroupMember(Base):
    __tablename__ = "group_members"
    id: Mapped[int] = mapped_column(primary_key=True)
    group_id: Mapped[int] = mapped_column(
        ForeignKey("groups.id", ondelete="CASCADE"), nullable=False
    )
    student_id: Mapped[str] = mapped_column(
        ForeignKey("user_profiles.student_id", ondelete="CASCADE"), nullable=False
    )
    role: Mapped[str] = mapped_column(String(20), nullable=False)
    wage: Mapped[float] = mapped_column(Float, nullable=False)
    display_title: Mapped[Optional[str]] = mapped_column(String(100))
    remark: Mapped[Optional[str]] = mapped_column(Text, nullable=True)
    created_at: Mapped[datetime.datetime] = mapped_column(server_default=func.now())

    group: Mapped["Group"] = relationship(back_populates="members")
    profile: Mapped["UserProfile"] = relationship(back_populates="group_memberships")

    __table_args__ = (
        UniqueConstraint("group_id", "student_id", "role", name="uq_group_member_role"),
        CheckConstraint("role IN ('manager', 'member')", name="check_role_type"),
    )


class DataGroupPermission(Base):
    __tablename__ = "data_group_permissions"
    id: Mapped[int] = mapped_column(primary_key=True)
    student_id: Mapped[str] = mapped_column(
        ForeignKey("user_profiles.student_id", ondelete="CASCADE"), nullable=False
    )
    permission: Mapped[str] = mapped_column(String(100), nullable=False)
    granted_by: Mapped[str] = mapped_column(
        ForeignKey("user_profiles.student_id", ondelete="CASCADE"), nullable=False
    )
    created_at: Mapped[datetime.datetime] = mapped_column(server_default=func.now())

    grantee_profile: Mapped["UserProfile"] = relationship(
        back_populates="data_permissions_granted", foreign_keys=[student_id]
    )
    granter_profile: Mapped["UserProfile"] = relationship(
        back_populates="data_permissions_given", foreign_keys=[granted_by]
    )

    __table_args__ = (
        UniqueConstraint("student_id", "permission", name="uq_user_permission"),
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
    inspection_tasks: Mapped[List["InspectionTask"]] = relationship(
        back_populates="college"
    )


class Classroom(Base):
    __tablename__ = "classrooms"
    id: Mapped[str] = mapped_column(String(50), primary_key=True)
    campus_id: Mapped[int] = mapped_column(
        ForeignKey("campuses.id", ondelete="CASCADE"), nullable=False
    )
    building: Mapped[str] = mapped_column(String(20), nullable=False)
    area: Mapped[str] = mapped_column(String(10), nullable=False)
    room_number: Mapped[str] = mapped_column(String(10), nullable=False)
    capacity: Mapped[Optional[int]]
    campus: Mapped["Campus"] = relationship(back_populates="classrooms")
    study_schedules: Mapped[List["StudySchedule"]] = relationship(
        back_populates="classroom"
    )
    inspection_tasks: Mapped[List["InspectionTask"]] = relationship(
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
        ForeignKey("classrooms.id", ondelete="RESTRICT"), nullable=False
    )
    college_id: Mapped[int] = mapped_column(
        ForeignKey("colleges.id", ondelete="RESTRICT"), nullable=False
    )
    expected_headcount: Mapped[Optional[int]]
    created_at: Mapped[datetime.datetime] = mapped_column(server_default=func.now())
    updated_at: Mapped[datetime.datetime] = mapped_column(
        server_default=func.now(),
        onupdate=func.now(),
    )
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
        ForeignKey("user_profiles.student_id", ondelete="CASCADE"), nullable=False
    )
    created_at: Mapped[datetime.datetime] = mapped_column(server_default=func.now())
    schedule: Mapped["StudySchedule"] = relationship(back_populates="check_in_task")
    profile: Mapped["UserProfile"] = relationship(back_populates="check_in_tasks")
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
    status: Mapped[str] = mapped_column(String(20), nullable=False, default="pending")
    created_at: Mapped[datetime.datetime] = mapped_column(server_default=func.now())
    updated_at: Mapped[datetime.datetime] = mapped_column(
        server_default=func.now(),
        onupdate=func.now(),
    )
    task: Mapped["CheckInTask"] = relationship(back_populates="data")


class InspectionTask(Base):
    __tablename__ = "inspection_tasks"
    id: Mapped[int] = mapped_column(primary_key=True)
    student_id: Mapped[str] = mapped_column(
        ForeignKey("user_profiles.student_id", ondelete="CASCADE"), nullable=False
    )
    date: Mapped[datetime.date] = mapped_column(nullable=False)
    time_slot: Mapped[str] = mapped_column(String(20), nullable=False)
    classroom_id: Mapped[str] = mapped_column(
        ForeignKey("classrooms.id", ondelete="RESTRICT"), nullable=False
    )
    college_id: Mapped[int] = mapped_column(
        ForeignKey("colleges.id", ondelete="RESTRICT"), nullable=False
    )
    expected_headcount: Mapped[Optional[int]]
    created_at: Mapped[datetime.datetime] = mapped_column(server_default=func.now())
    profile: Mapped["UserProfile"] = relationship(back_populates="inspection_tasks")
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
        unique=True,
    )
    data1: Mapped[Optional[str]] = mapped_column(Text)
    data2: Mapped[Optional[str]] = mapped_column(Text)
    remarks: Mapped[Optional[str]] = mapped_column(Text)
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
    __table_args__ = {"info": dict(is_view=True)}

    id: Mapped[int] = mapped_column(Integer, primary_key=True)
    name: Mapped[str] = mapped_column(String)
    gender: Mapped[str] = mapped_column(String)
    qq: Mapped[Optional[str]] = mapped_column(String)
    phone: Mapped[Optional[str]] = mapped_column(String)
    department: Mapped[str] = mapped_column(String)
    department_id: Mapped[int] = mapped_column(Integer)
    job: Mapped[str] = mapped_column(String)


# DDL for creating the view
create_view_sql = """
CREATE VIEW IF NOT EXISTS contact_view AS
SELECT
    ROW_NUMBER() OVER (ORDER BY gm.group_id, gm.role DESC) AS id,
    u.name,
    u.gender,
    up.qq,
    up.phone,
    g.name AS department,
    g.id AS department_id,
    gm.role AS job
FROM group_members AS gm
JOIN user_profiles AS up ON gm.student_id = up.student_id
JOIN users AS u ON up.student_id = u.student_id
JOIN groups AS g ON gm.group_id = g.id
"""
create_view_ddl = DDL(create_view_sql)

# DDL for dropping the view
drop_view_ddl = DDL("DROP VIEW IF EXISTS contact_view")


# Event listeners to create and drop the view
event.listen(Base.metadata, "after_create", create_view_ddl)
event.listen(Base.metadata, "before_drop", drop_view_ddl)
