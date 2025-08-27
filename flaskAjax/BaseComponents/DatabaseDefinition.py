import datetime

from sqlalchemy import (
    ForeignKey, String, Integer, Text, LargeBinary, TIMESTAMP, CheckConstraint,
    UniqueConstraint, func
)
from sqlalchemy.orm import (DeclarativeBase, Mapped, mapped_column, relationship)
from typing import List, Optional


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
    campus_id: Mapped[
        Optional[int],
    ] = mapped_column(ForeignKey("campuses.id", ondelete="SET NULL"),)
    college_id: Mapped[
        Optional[int],
    ] = mapped_column(ForeignKey("colleges.id", ondelete="SET NULL"),)
    hometown: Mapped[Optional[str]] = mapped_column(String(100))
    phone: Mapped[Optional[str]] = mapped_column(String(20))
    qq: Mapped[Optional[str]] = mapped_column(String(20))
    updated_at: Mapped[datetime.datetime] = mapped_column(
        server_default=func.now(),
        onupdate=func.now(),
    )

    # Relationships
    user: Mapped["User"] = relationship(back_populates="profile")
    campus: Mapped["Campus"] = relationship(back_populates="user_profiles")
    college: Mapped["College"] = relationship(back_populates="user_profiles")


class UserCredential(Base):
    __tablename__ = "user_credentials"
    student_id: Mapped[str] = mapped_column(
        ForeignKey("users.student_id", ondelete="CASCADE"), primary_key=True
    )
    password_hash: Mapped[bytes] = mapped_column(LargeBinary, nullable=False)
    updated_at: Mapped[datetime.datetime] = mapped_column(
        server_default=func.now(),
        onupdate=func.now(),
    )

    user: Mapped["User"] = relationship(back_populates="credential")


class PaymentInfo(Base):
    __tablename__ = "payment_info"
    student_id: Mapped[str] = mapped_column(
        ForeignKey("users.student_id", ondelete="CASCADE"), primary_key=True
    )
    recipient_name: Mapped[str] = mapped_column(String(100), nullable=False)
    card_number: Mapped[str] = mapped_column(String(50), nullable=False)
    is_registered_poor: Mapped[int] = mapped_column(Integer, nullable=False, default=0)
    updated_at: Mapped[datetime.datetime] = mapped_column(
        server_default=func.now(),
        onupdate=func.now(),
    )

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
    login_result: Mapped[
        Optional[str],
    ] = mapped_column(String(10),)  # 登录结果信息: "success", "failure"
    created_at: Mapped[datetime.datetime] = mapped_column(server_default=func.now(),)

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
    updated_at: Mapped[datetime.datetime] = mapped_column(
        server_default=func.now(),
        onupdate=func.now(),
    )

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
    id: Mapped[int] = mapped_column(Integer, primary_key=True)
    name: Mapped[str] = mapped_column(String(100), nullable=False, unique=True)

    user_profiles: Mapped[List["UserProfile"]] = relationship(back_populates="campus")
    classrooms: Mapped[List["Classroom"]] = relationship(back_populates="campus")


class College(Base):
    __tablename__ = "colleges"
    id: Mapped[int] = mapped_column(primary_key=True)
    name: Mapped[str] = mapped_column(String(100), nullable=False, unique=True)

    user_profiles: Mapped[List["UserProfile"]] = relationship(back_populates="college")
    study_schedules: Mapped[
        List["StudySchedule"],
    ] = relationship(back_populates="college",)
    inspection_tasks: Mapped[
        List["InspectionTask"],
    ] = relationship(back_populates="college",)


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
    study_schedules: Mapped[
        List["StudySchedule"],
    ] = relationship(back_populates="classroom",)
    inspection_tasks: Mapped[
        List["InspectionTask"],
    ] = relationship(back_populates="classroom",)

    __table_args__ = (
        UniqueConstraint(
            'campus_id', 'building', 'area', 'room_number', name='uq_classroom_location'
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
    updated_at: Mapped[datetime.datetime] = mapped_column(
        server_default=func.now(),
        onupdate=func.now(),
    )

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
    updated_at: Mapped[datetime.datetime] = mapped_column(
        server_default=func.now(),
        onupdate=func.now(),
    )

    task: Mapped["InspectionTask"] = relationship(back_populates="data")
