# Запуск

docker compose up --build

## Адрес
По умолчанию приложение запускается на 80 порту, работает по http

## Авторизация
  ```
Scheme: basic auth
Login: user1
Password: user1_pass
```

## API
Приложение использует формат json как для запросов, так и для ответов сервера

- Создание задачи (POST /tasks)
- Получение списка всех задач (GET /tasks)
- Получение информации о конкретной задаче (GET /tasks/:id)
- Обновление информации о задаче (PUT /tasks/:id)
- Получение списка задач пользователя (GET /tasks/user/:user_id)
- Выполнение задачи (PATCH /tasks/:task_id/user/:user_id)

Структура запроса для создания задачи:
```
{
  "id": 1,
  "title": "Urgent",
  "description": "Make app",
  "users": [1,2,3]
}
```

Структура запроса для обновления задачи:
```
{
  "title": "Misc",
  "description": "Make app after discussion",
  "users": [1]
}
```

Структура ответа для общего списка задач:
```
[
  {
    "id": 1,
    "title": "Misc",
    "description": "Make app after discussion",
    "users": [1]
  }
]
```

Структура ответа списка задач пользователя:
```
[
  {
    "id": 1,
    "title": "Misc",
    "description": "Make app after discussion",
    "completed": false
  }
]
```
