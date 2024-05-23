package test_task_2024_05_21

import (
	"database/sql"
	"log"
)

type UserTask struct {
	Id          int64  `json:"id"`
	Title       string `json:"title"`
	Description string `json:"description"`
	Completed   bool   `json:"completed"`
}

type Repository struct {
	*sql.DB
}

func (r *Repository) GetUserTasks(userId int64) *[]UserTask {
	stmt, err := r.Prepare("select t.id, t.title, t.description, ut.completed from tasks t left join tasks_users ut on ut.task_id = t.id where ut.user_id = $1")

	if err != nil {
		return nil
	}

	rows, err := stmt.Query(userId)
	if err != nil {
		return nil
	}

	userTasks := []UserTask{}
	for rows.Next() {
		var ut UserTask

		err = rows.Scan(&ut.Id, &ut.Title, &ut.Description, &ut.Completed)
		if err != nil {
			log.Println(err.Error())
			return nil
		}

		userTasks = append(userTasks, ut)
	}

	return &userTasks
}

func (r *Repository) CompleteUserTask(taskId int64, userId int64) error {
	stmt, err := r.Prepare("update tasks_users set completed=true where task_id = $1 and user_id = $2")

	if err != nil {
		return nil
	}

	_, err = stmt.Exec(taskId, userId)

	return err
}
