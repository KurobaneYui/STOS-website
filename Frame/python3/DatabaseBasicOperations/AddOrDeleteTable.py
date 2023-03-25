from Frame.python3.BaseComponents.DatabaseConnector import DatabaseConnector
from Frame.python3.BaseComponents.Logger import Logger
from Frame.python3.BaseComponents.CustomResponse import CustomResponse


class ColumnConfig:
    def __init__(self, columnName: str, columnType: str, defaultValue: str | int | float | None, asPrimaryKey: bool = False, canNULL: bool = False, comment: str | None = None):
        self.columnName = columnName
        self.columnType = columnType
        self.defaultValue = defaultValue
        self.asPrimaryKey = asPrimaryKey
        self.canNULL = canNULL
        self.comment = comment

    def __str__(self) -> str:
        if self.defaultValue is str:
            defaultValue = self.defaultValue if self.defaultValue == "CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP" else f"'{self.defaultValue}'"
        elif isinstance(self.defaultValue, (int, float)):
            defaultValue = self.defaultValue

        returnString = f"`{self.columnName}` {self.columnType} {'' if self.canNULL else 'NOT NULL'} "
        returnString += f"{'' if self.defaultValue is None else 'DEFAULT'} {defaultValue} "
        returnString += f"{'' if self.comment is None else 'COMMENT'} {self.comment}"
        return returnString


class AddOrDeleteTable:
    @staticmethod
    def addTable(tableName: str, columnsTuple: tuple[ColumnConfig]):
        primaryKeyNames = []
        addColumnString = ""

        for column in columnsTuple:
            if column.asPrimaryKey:
                addColumnString += f"{column},"
                primaryKeyNames.append(f"`{column.columnName}`")

        primaryKeyNames = ','.join(primaryKeyNames)

        sql = f"CREATE TABLE IF NOT EXISTS `{tableName}`"
        sql += f"({addColumnString} PRIMARY KEY ({primaryKeyNames}) ) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;"

        DatabaseConnector().execute(sql=sql)

    @staticmethod
    def deleteTable(tableName: str):
        DatabaseConnector().execute(sql="DROP TABLE %s", data=(tableName,))


"""
CREATE TABLE IF NOT EXISTS `STSA`.`MemberExtend` (
    `student_id` VARCHAR(50) NOT NULL,
    `ethnicity` VARCHAR(50) NOT NULL COMMENT 'Please provide full name of nation',
    `hometown` VARCHAR(50) NOT NULL,
    `phone` VARCHAR(50) NOT NULL COMMENT 'Recommand phone number',
    `qq` VARCHAR(50) NOT NULL,
    `school_id` INT(10) UNSIGNED NOT NULL,
    `dormitory_yuan` VARCHAR(50) NOT NULL COMMENT '清水河填如：学知苑、博瀚苑\n沙河填如：校内、校外',
    `dormitory_dong` VARCHAR(50) NOT NULL,
    `dormitory_hao` VARCHAR(50) NOT NULL,
    `remark` TEXT NOT NULL,
    `submission_time` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`student_id`),
    INDEX `MemberExtend_School_idx` (`school_id` ASC) VISIBLE,
    CONSTRAINT `MemberExtend_MemberBasic`
        FOREIGN KEY (`student_id`)
        REFERENCES `STSA`.`MemberBasic` (`student_id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT `MemberExtend_School`
        FOREIGN KEY (`school_id`)
        REFERENCES `STSA`.`School` (`school_id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_0900_ai_ci;


-- -----------------------------------------------------
-- View `STSA`.`Delegate`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `STSA`.`Delegate`;
USE `STSA`;
CREATE  OR REPLACE VIEW `Delegate`(`type`,`schedule_id`, `schedule_student_id`, `schedule_student_name`, `schedule_department_id`, `schedule_department_name`,
										`actual_student_id`, `actual_student_name`, `actual_department_id`, `actual_department_name`) AS
		SELECT '查早', `SelfstudyCheckSchedule`.`selfstudy_id`, `SelfstudyCheckSchedule`.`schedule_student_id`, Schedule_Info.`name`, Schedule_Work.`department_id`,
							Schedule_Department.`name`, `SelfstudyCheckSchedule`.`actual_student_id`, Actual_Info.`name`, Actual_Work.`department_id`, Actual_Department.`name`
		FROM `SelfstudyCheckSchedule`
			LEFT JOIN `MemberBasic` AS Schedule_Info ON Schedule_Info.`student_id` = `SelfstudyCheckSchedule`.`schedule_student_id`
            LEFT JOIN `Work` AS Schedule_Work ON Schedule_Work.`student_id` = `SelfstudyCheckSchedule`.`schedule_student_id`
            LEFT JOIN `Department` AS Schedule_Department ON Schedule_Department.`department_id` = Schedule_Work.`department_id`
            LEFT JOIN `MemberBasic` AS Actual_Info ON Actual_Info.`student_id` = `SelfstudyCheckSchedule`.`actual_student_id`
            LEFT JOIN `Work` AS Actual_Work ON Actual_Work.`student_id` = `SelfstudyCheckSchedule`.`schedule_student_id`
            LEFT JOIN `Department` AS Actual_Department ON Actual_Department.`department_id` = Actual_Work.`department_id`
        WHERE
			`SelfstudyCheckSchedule`.`schedule_student_id` != `SelfstudyCheckSchedule`.`actual_student_id`
            and Schedule_Department.`name` like "现场组%"
            and Actual_Department.`name` like "现场组%"
    UNION
		SELECT '查课', `CourseCheckSchedule`.`course_id`, `CourseCheckSchedule`.`schedule_student_id`, Schedule_Info.`name`, Schedule_Work.`department_id`,
							Schedule_Department.`name`, `CourseCheckSchedule`.`actual_student_id`, Actual_Info.`name`, Actual_Work.`department_id`, Actual_Department.`name`
        FROM `CourseCheckSchedule`
			LEFT JOIN `MemberBasic` AS Schedule_Info ON Schedule_Info.`student_id` = `CourseCheckSchedule`.`schedule_student_id`
            LEFT JOIN `Work` AS Schedule_Work ON Schedule_Work.`student_id` = `CourseCheckSchedule`.`schedule_student_id`
            LEFT JOIN `Department` AS Schedule_Department ON Schedule_Department.`department_id` = Schedule_Work.`department_id`
            LEFT JOIN `MemberBasic` AS Actual_Info ON Actual_Info.`student_id` = `CourseCheckSchedule`.`actual_student_id`
            LEFT JOIN `Work` AS Actual_Work ON Actual_Work.`student_id` = `CourseCheckSchedule`.`schedule_student_id`
            LEFT JOIN `Department` AS Actual_Department ON Actual_Department.`department_id` = Actual_Work.`department_id`
		WHERE
			`CourseCheckSchedule`.`schedule_student_id` != `CourseCheckSchedule`.`actual_student_id`
			and Schedule_Department.`name` like "现场组%"
            and Actual_Department.`name` like "现场组%";
"""
