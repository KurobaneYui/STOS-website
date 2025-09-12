# AJAX API Endpoints Documentation

This document outlines the AJAX endpoints used for managing the morning self-study schedule.

## 1\. Get Submitted Schedule Dates

- **Endpoint:** `/Ajax/DataManager/get_submitted_selfstudy_schedule_date`

- **Description:** Retrieves a list of recent dates for which a self-study schedule has been created or submitted. This is used to populate the "Import" modal.

- **HTTP Method:** `GET`

- **Request Payload (Input):** None

- **Response Payload (Output):**

  - **On Success (`code: 200`):**

        ```json
        {
          "code": 200,
          "data": [
            {
              "date": "2025-09-10",
              "submitted_at": "2025-09-09 15:30:00"
            },
            {
              "date": "2025-09-09",
              "submitted_at": null
            },
            // ... more dates
          ]
        }
        ```

        **Fields:**
    - `date` (string): The date of the self-study session (format `YYYY-MM-DD`).
    - `submitted_at` (string | null): The timestamp when the schedule for this date was submitted. If `null`, a schedule exists but has not been finalized.

## 2\. Get Schedule Details for a Specific Date

- **Endpoint:** `/Ajax/DataManager/get_schedule_on_date`

- **Description:** Fetches the detailed classroom and student schedule for a given date, including assigned and unassigned members.

- **HTTP Method:** `POST`

- **Request Payload (Input):**

    ```json
    {
      "date": "2025-09-10"
    }
    ```

    **Fields:**

  - `date` (string): The date for which to retrieve the schedule (format `YYYY-MM-DD`).

- **Response Payload (Output):**

  - **On Success (`code: 200`):**

        ```json
        {
          "code": 200,
          "data": {
            "date": "2025-09-10",
            "schedule": {
              "101": {
                "schedule_id": 101,
                "campus": "清水河",
                "classroom_id": 25,
                "classroom_name": "品学楼A101",
                "remark": "高数",
                "student_id": "2023010101",
                "name": "张三",
                "group_name": "第一组"
              },
              "102": {
                "schedule_id": 102,
                "campus": "沙河",
                "classroom_name": "主楼东102",
                "remark": null,
                "student_id": "2023020202",
                "name": "李四",
                "group_name": "第五组"
              }
            },
            "unassigned": [
              {
                "student_id": "2023030303",
                "name": "王五",
                "group_name": "第二组"
              }
            ]
          }
        }
        ```

        **Fields:**
    - `date` (string): The date of the schedule.
    - `schedule` (Object): An object where each key is a unique `schedule_id` and the value is an object containing the full details of that assignment.
    - `unassigned` (Array): A list of student members who are eligible but not assigned to any classroom for that date.

## 3\. Submit Self-Study Schedule

- **Endpoint:** `/Ajax/DataManager/submit_selfstudy_schedule`

- **Description:** Submits the final, potentially modified, schedule for a specific date to the server.

- **HTTP Method:** `POST`

- **Request Payload (Input):**

    ```json
    {
      "date": "2025-09-10",
      "data": {
        "qingshuihe": [
          {
            "selfstudy_id": 101,
            "student_id": "2023010101"
          }
        ],
        "shahe": [
          {
            "selfstudy_id": 102,
            "student_id": "2023020202"
          }
        ]
      }
    }
    ```

    **Fields:**

  - `date` (string): The date the schedule applies to.
  - `data` (Object): An object containing arrays for each campus.
  - `selfstudy_id` (integer): The unique identifier for the schedule slot (classroom assignment).
  - `student_id` (string): The student ID of the member assigned to that slot.

- **Response Payload (Output):**

  - **On Success (`code: 200`):**

        ```json
        {
          "code": 200,
          "message": "提交成功"
        }
        ```

## 4\. Reset Schedule for a Campus

- **Endpoint:** `/Ajax/DataManager/reset_schedule_on_date`

- **Description:** Resets the student assignments for a specific campus on a given date to their original or last saved state.

- **HTTP Method:** `POST`

- **Request Payload (Input):**

    ```json
    {
      "date": "2025-09-10",
      "campus": "清水河"
    }
    ```

    **Fields:**

  - `date` (string): The target date.
  - `campus` (string): The name of the campus to reset (`清水河` or `沙河`).

- **Response Payload (Output):**

  - **On Success (`code: 200`):**

        ```json
        {
          "code": 200,
          "data": [
            // Array of schedule item objects for the specified campus,
            // matching the structure of items in the get_schedule_on_date response.
            {
              "schedule_id": 101,
              "campus": "清水河",
              // ... other fields
            }
          ]
        }
        ```

## 5\. Randomize Schedule for a Campus

- **Endpoint:** `/Ajax/DataManager/random_schedule_on_date`

- **Description:** Randomly shuffles the student assignments for a specific campus on a given date.

- **HTTP Method:** `POST`

- **Request Payload (Input):**

    ```json
    {
      "date": "2025-09-10",
      "campus": "清水河"
    }
    ```

    **Fields:**

  - `date` (string): The target date.
  - `campus` (string): The name of the campus to randomize (`清水河` or `沙河`).

- **Response Payload (Output):**

  - **On Success (`code: 200`):**

        ```json
        {
          "code": 200,
          "data": [
            // Array of randomly shuffled schedule item objects for the specified campus,
            // matching the structure of items in the get_schedule_on_date response.
            {
              "schedule_id": 101,
              "campus": "清水河",
              // ... other fields
            }
          ]
        }
        ```
