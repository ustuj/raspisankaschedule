# SheduleNSMK

Веб-приложение для создания и управления расписанием учебных занятий.

## Возможности

- просмотр расписания по группам и дням недели;
- поддержка двух учебных недель;
- добавление, редактирование и удаление занятий;
- управление группами, преподавателями, предметами и аудиториями;
- несколько предметов у одного преподавателя;
- Drag & Drop для перемещения занятий;
- обмен занятиями при перемещении в занятую ячейку;
- проверка конфликтов расписания;
- поиск;
- заметки к занятиям;
- печать расписания;
- тёмная тема;
- разграничение прав доступа.

## Роли

- **Администратор** — управление пользователями и справочниками.
- **Преподаватель** — работа с доступными предметами и занятиями.
- **Студент** — просмотр расписания.

## Технологии

- PHP
- Laravel
- SQLite
- Blade
- JavaScript
- CSS
- Vite

## Установка

### 1. Клонирование репозитория

```bash
git clone https://github.com/KarKar3333/WebShedule.git
cd SheduleNSMKv2.9
```

### 2. Установка PHP-зависимостей

```bash
composer install
```

### 3. Установка JavaScript-зависимостей

Windows PowerShell:

```powershell
npm.cmd install
```

Если PowerShell разрешает запуск `npm.ps1`, можно использовать:

```bash
npm install
```

### 4. Создание файла окружения

Windows PowerShell:

```powershell
Copy-Item .env.example .env
```

Linux/macOS:

```bash
cp .env.example .env
```

### 5. Создание необходимых директорий Laravel

Windows PowerShell:

```powershell
New-Item -ItemType Directory -Force storage\framework\cache\data
New-Item -ItemType Directory -Force storage\framework\sessions
New-Item -ItemType Directory -Force storage\framework\views
New-Item -ItemType Directory -Force storage\logs
New-Item -ItemType Directory -Force bootstrap\cache
```

Linux/macOS:

```bash
mkdir -p storage/framework/cache/data
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs
mkdir -p bootstrap/cache
```

### 6. Генерация ключа приложения

```bash
php artisan key:generate
```

### 7. Создание базы данных

Проект использует SQLite.

Windows PowerShell:

```powershell
New-Item database/database.sqlite -ItemType File
```

Linux/macOS:

```bash
touch database/database.sqlite
```

В `.env` должно быть:

### 8. Создание таблиц

```bash
php artisan migrate
```

### 9. Заполнение базы начальными данными

```bash
php artisan db:seed
```

### 10. Сборка frontend

Для первого запуска:

```powershell
npm.cmd run build
```

После выполнения должна появиться:

```text
public/build/manifest.json
```

### 11. Запуск Laravel

```bash
php artisan serve
```

После запуска открыть:

```text
http://127.0.0.1:8000
```

## Запуск frontend в режиме разработки

Для разработки можно использовать Vite:

```powershell
npm.cmd run dev
```

Эту команду нужно оставить запущенной в отдельном терминале.

В другом терминале запустить Laravel:

```bash
php artisan serve
```

## Запуск после первой установки

Терминал 1:

```powershell
npm.cmd run dev
```

Терминал 2:

```bash
php artisan serve
```

Затем открыть:

```text
http://127.0.0.1:8000
```

## Сборка frontend

Production-сборка:

```powershell
npm.cmd run build
```

## База данных

Проект использует SQLite.

Структура базы данных создаётся с помощью Laravel Migration:

```bash
php artisan migrate
```

Начальные данные создаются командой:

```bash
php artisan db:seed
```

Локальная SQLite-база не хранится в репозитории.

## Структура проекта

```text
SheduleNSMK/
├── app/
├── bootstrap/
├── config/
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/
├── public/
├── resources/
│   ├── css/
│   ├── js/
│   └── views/
├── routes/
├── storage/
├── tests/
├── artisan
├── composer.json
├── composer.lock
├── package.json
├── package-lock.json
├── phpunit.xml
├── vite.config.js
├── .env.example
├── .gitignore
└── README.md
```

## Зависимости

Папки `vendor/` и `node_modules/` не хранятся в Git.

После клонирования они устанавливаются командами:

```bash
composer install
npm install
```

## Конфигурация

Файл `.env` не хранится в репозитории.

Для настройки используется:

```text
.env.example
```

После клонирования необходимо создать собственный `.env`.

## Git

В репозитории хранятся:

- исходный код приложения;
- миграции;
- seeders;
- конфигурационные файлы;
- файлы frontend;
- тесты;
- `.env.example`;
- README.

Не хранятся:

- `.env`;
- `vendor/`;
- `node_modules/`;
- локальная SQLite-база;
- кэш;
- временные файлы.

## Обновление проекта

Получить последние изменения:

```bash
git pull
```

После обновления зависимостей:

```bash
composer install
npm install
```

Если появились новые миграции:

```bash
php artisan migrate
```

## Автор

Учебный проект для практической работы.
