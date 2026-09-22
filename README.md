# РАСПИСАНИЕ НСМК

## Возможности

### Расписание

- отображение расписания по группам;
- дни недели и учебные пары;
- поддержка двух учебных недель;
- добавление, редактирование и удаление занятий;
- перенос занятий с помощью Drag & Drop;
- изменение группы, дня и номера пары;
- визуальные карточки занятий;
- отображение преподавателя, дисциплины, аудитории и типа занятия.

<img width="1690" height="731" alt="изображение" src="https://github.com/user-attachments/assets/4b3ce53e-fe52-4b13-9f7a-34a7a03ed255" />

### Справочники

- группы;
- преподаватели;
- дисциплины;

<img width="1196" height="787" alt="изображение" src="https://github.com/user-attachments/assets/c7d6c6eb-e265-4ab8-bd2c-85fd29cf859b" />

- аудитории;
- связь преподавателей с несколькими дисциплинами.

### Проверка конфликтов

Система проверяет пересечения:

- у преподавателя;
- у учебной группы;
- у аудитории.

<img width="322" height="40" alt="изображение" src="https://github.com/user-attachments/assets/3f5fcc65-8ec9-4a0d-a266-9958bdcc9dc9" />

### Пользователи и роли

**Администратор**

- управление пользователями;
- управление группами;
- управление преподавателями;
- управление дисциплинами;
- управление аудиториями;
- редактирование расписания.

<img width="173" height="289" alt="изображение" src="https://github.com/user-attachments/assets/d7eda389-42e2-41ab-88cc-2c6f4571577e" />

<img width="924" height="448" alt="изображение" src="https://github.com/user-attachments/assets/f85d35ab-4946-4f18-8975-dd3f37b0fcad" />

**Преподаватель**

- просмотр расписания;
- редактирование собственных занятий;
- добавление и редактирование заметок к своим занятиям.

**Студент**

- просмотр расписания своей группы;
- просмотр общего расписания;
- доступ без возможности редактирования.

### Дополнительные возможности

- заметки к занятиям;

<img width="624" height="380" alt="изображение" src="https://github.com/user-attachments/assets/62fc2233-7217-4d19-b5e2-8e520a044603" />

- экспорт расписания в формат `.ics`;

<img width="119" height="50" alt="изображение" src="https://github.com/user-attachments/assets/6e666a9d-c894-4e1b-b630-32d24878118f" />

## Технологии

### Бэкенд

- PHP 8.5+
- Laravel 13
- SQLite

### Фронтед

- HTML
- CSS
- JavaScript
- Blade
- Vite

### Инструменты

- Composer
- Node.js
- NPM
- Git
- VS Code

## Архитектура проекта

Проект построен с разделением логики по отдельным модулям.

```text
schedule-app/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   └── Requests/
│   ├── Models/
│   └── Services/
│       ├── Schedule/
│       └── References/
├── database/
│   ├── migrations/
│   └── seeders/
├── resources/
│   ├── views/
│   ├── css/
│   └── js/
├── routes/
│   ├── web.php
│   └── api.php
├── tests/
│   ├── Feature/
│   └── Unit/
├── public/
├── config/
├── bootstrap/
├── artisan
├── composer.json
├── package.json
└── vite.config.js
```

## Установка

### Требования

Перед установкой необходимо иметь:

- PHP 8.5+
- Composer
- Node.js 24+
- NPM
- Git
- SQLite

Для PHP должны быть доступны расширения:

- `PDO`
- `pdo_sqlite`
- `sqlite3`
- `fileinfo`

### Клонирование проекта

```powershell
git clone https://github.com/ustuj/raspisankaschedule.git
cd raspisankaschedule
```

### Установка зависимостей

```powershell
composer install
npm.cmd install
```

### Настройка окружения

```powershell
copy .env.example .env
php artisan key:generate
```

### Создание служебных каталогов

```powershell
New-Item -ItemType Directory -Force storage\framework\views
New-Item -ItemType Directory -Force storage\framework\cache\data
New-Item -ItemType Directory -Force storage\framework\sessions
New-Item -ItemType Directory -Force bootstrap\cache
```

### Создание базы данных

```powershell
New-Item database\database.sqlite -ItemType File
```

### Запуск миграций

```powershell
php artisan migrate
```

При необходимости можно использовать сидеры:

```powershell
php artisan db:seed
```

### Сборка frontend

```powershell
npm.cmd run build
```

### Запуск приложения

```powershell
php artisan serve
```

После запуска приложение будет доступно по адресу:

`http://127.0.0.1:8000`

---

## Разработка фронтед

```powershell
npm.cmd run dev
```

```powershell
npm.cmd run build
```

## Тестирование

Для запуска автоматических тестов:

```powershell
php artisan test
```

## База данных

Основные сущности:

```text
groups
teachers
subjects
teacher_subjects
classrooms
lessons
users
```

Занятие содержит информацию о:

- группе;
- преподавателе;
- дисциплине;
- аудитории;
- дне недели;
- первой учебной неделе;
- второй учебной неделе;
- типе занятия;
- заметке.

## Экспорт расписания

Расписание можно экспортировать в формат:

`.ics`

Файл можно использовать для добавления расписания в календарные приложения, поддерживающие iCalendar.
