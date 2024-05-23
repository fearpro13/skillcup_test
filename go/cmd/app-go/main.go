package main

import (
	"database/sql"
	"errors"
	"fearpro13/test_task_2024_05_21"
	"fmt"
	"github.com/gin-gonic/gin"
	_ "github.com/lib/pq"
	"log"
	"net/http"
	"os"
	"os/signal"
	"strconv"
	"syscall"
)

const (
	ProcOk    = 0
	ProcError = 1
)

func main() {
	res, err := execMain(os.Args)
	if err != nil {
		log.Printf("%+v\n", res)
	}

	os.Exit(res)
}

func execMain(args []string) (int, error) {
	if len(args) < 1 {
		return ProcError, errors.New("not enough arguments")
	}

	dbUrl := os.Getenv("APP_DATABASE_URL")
	if dbUrl == "" {
		if len(args) < 2 {
			return ProcError, errors.New("database url must be provided as 2nd argument")
		}

		dbUrl = args[1]
	}

	db, err := sql.Open("postgres", dbUrl)
	if err != nil {
		return ProcError, err
	}

	defer func() {
		_ = db.Close()
	}()

	err = db.Ping()
	if err != nil {
		return ProcError, err
	}

	fmt.Println("Database connection established")

	repo := test_task_2024_05_21.Repository{
		DB: db,
	}

	r := gin.Default()

	r.Group("/", gin.BasicAuth(gin.Accounts{
		"user1": "user1_pass",
	}))

	r.GET("/tasks/user/:user_id", func(c *gin.Context) {
		userIdStr := c.Param("user_id")
		if userIdStr == "" {
			c.JSON(400, gin.H{
				"message": "user_id required",
			})

			return
		}

		userId, err := strconv.Atoi(userIdStr)
		if err != nil {
			c.JSON(400, gin.H{
				"message": "user_id invalid",
			})
			return
		}

		tasks := repo.GetUserTasks(int64(userId))
		if tasks == nil {
			c.JSON(500, gin.H{
				"message": "no tasks found",
			})

			return
		}

		c.JSON(http.StatusOK, *tasks)
	})

	r.PATCH("/tasks/:task_id/user/:user_id", func(c *gin.Context) {
		taskIdStr := c.Param("task_id")
		userIdStr := c.Param("user_id")

		if userIdStr == "" || taskIdStr == "" {
			c.JSON(400, gin.H{
				"message": "user_id and task_id are required",
			})

			return
		}

		userId, err := strconv.Atoi(userIdStr)
		if err != nil {
			c.JSON(400, gin.H{
				"message": "user_id invalid",
			})

			return
		}

		taskId, err := strconv.Atoi(taskIdStr)
		if err != nil {
			c.JSON(400, gin.H{
				"message": "task_id invalid",
			})

			return
		}

		err = repo.CompleteUserTask(int64(taskId), int64(userId))
		if err != nil {
			log.Println(err.Error())
			c.JSON(422, gin.H{
				"message": "could not complete task",
			})

			return
		}

		c.JSON(200, gin.H{
			"message": "ok",
		})
	})

	addr, e := os.LookupEnv("APP_LISTEN_ADDR")
	if !e {
		addr = "0.0.0.0:80"
	}

	server := &http.Server{
		Addr:    addr,
		Handler: r.Handler(),
	}

	go func() {
		signals := make(chan os.Signal, 1)

		signal.Notify(signals, syscall.SIGINT, syscall.SIGTERM)

		<-signals

		_ = server.Shutdown(nil)
	}()

	err = server.ListenAndServe()

	if err != nil {
		return ProcError, err
	}

	return ProcOk, nil
}
