# Technical Reference

المرجع التقني الرسمي للمشروع.

هذا الملف مخصص للتوثيق الهندسي فقط: التقنيات، الإطارات، المكتبات، البنية العامة، طبقات التطبيق، إعدادات التشغيل، وبيانات الدخول التجريبية. لا يركز على الشرح التجاري أو الوظيفي، بل على الصورة التقنية الفعلية للمشروع كما هو الآن داخل المستودع.

## 1. Project Identity

- Project Name: `CRM & PM System`
- Application Style: `Monolithic Laravel Web Application`
- Architecture: `MVC + Blade Server-Rendered UI`
- Runtime Mode: `Web App`
- Language Support: `Arabic + English`
- UI Direction Support: `LTR + RTL`

## 2. Runtime & Core Platform

- PHP: `^8.2`
- Laravel Framework Constraint: `^12.0`
- Laravel Runtime verified locally: `12.56.0`
- Composer: `2.x`
- Frontend Bundler: `Vite 7`
- Node Package Manager: `npm`
- Styling Build Pipeline: `Tailwind CSS + PostCSS + Vite`

## 3. Backend Stack

### Core Backend Framework

- `laravel/framework ^12.0`
- `laravel/tinker ^2.10.1`

### Backend Dev Packages

- `laravel/breeze ^2.4`
- `laravel/pail ^1.2.2`
- `laravel/pint ^1.24`
- `laravel/sail ^1.41`
- `fakerphp/faker ^1.23`
- `mockery/mockery ^1.6`
- `nunomaduro/collision ^8.6`
- `phpunit/phpunit ^11.5.50`

### Backend Patterns Used

- MVC
- Eloquent ORM
- Form Requests Validation
- Middleware
- Policies + Gates
- Service Layer
- Seeders + Factories
- Notifications
- Queue-ready database driver configuration
- Blade-based server rendering

## 4. Frontend Stack

### UI Technologies

- `Blade`
- `Tailwind CSS ^3.1.0`
- `@tailwindcss/forms ^0.5.2`
- `Alpine.js ^3.4.2`
- `Axios ^1.11.0`
- `Chart.js ^4.5.1`
- `SortableJS ^1.15.7`
- `flag-icons ^7.5.0`

### Build / Tooling

- `Vite ^7.0.7`
- `laravel-vite-plugin ^2.0.0`
- `postcss ^8.4.31`
- `autoprefixer ^10.4.2`
- `concurrently ^9.0.1`

## 5. UI / Design System Notes

- Main UI engine: `Blade layouts + reusable Blade components`
- Theme Support: `dark / light`
- Notifications UI: `Toast-based global notifications`
- Typography:
  - `Instrument Sans`
  - `Cairo`
- Language switcher: real flag icons via `flag-icons`
- Header controls:
  - Notifications icon
  - Theme toggle icon
  - Language switcher
  - User control

## 6. Application Structure

### Key Root Files

- `routes/web.php`
- `routes/auth.php`
- `bootstrap/app.php`
- `config/database.php`
- `config/crm_pm.php`
- `.env`
- `.env.example`
- `composer.json`
- `package.json`

### Key Application Folders

- `app/Http/Controllers`
- `app/Http/Requests`
- `app/Http/Middleware`
- `app/Models`
- `app/Policies`
- `app/Services`
- `app/Notifications`
- `resources/views`
- `resources/css`
- `resources/js`
- `database/migrations`
- `database/factories`
- `database/seeders`
- `lang`

## 7. Controllers

### General Controllers

- `DashboardController`
- `ProfileController`
- `NotificationController`
- `AuditLogController`
- `ReportController`
- `LocaleController`

### CRM Controllers

- `CRM/CustomerController`
- `CRM/CustomerInteractionController`
- `CRM/LeadController`
- `CRM/DealController`
- `CRM/FollowUpController`

### PM Controllers

- `PM/ProjectController`
- `PM/TaskController`
- `PM/TaskCommentController`
- `PM/KanbanController`
- `PM/TimeEntryController`

### Team Controllers

- `Team/UserController`
- `Team/RoleController`

### Auth Controllers

- Breeze / Laravel auth controllers under `app/Http/Controllers/Auth`

## 8. Request Validation Layer

### Form Requests Present

- `LoginRequest`
- `ProfileUpdateRequest`
- `StoreCustomerRequest`
- `UpdateCustomerRequest`
- `StoreCustomerInteractionRequest`
- `StoreLeadRequest`
- `UpdateLeadRequest`
- `ConvertLeadRequest`
- `StoreDealRequest`
- `UpdateDealRequest`
- `UpdateDealStageRequest`
- `StoreFollowUpRequest`
- `StoreProjectRequest`
- `UpdateProjectRequest`
- `StoreTaskRequest`
- `UpdateTaskRequest`
- `MoveTaskRequest`
- `StoreTaskCommentRequest`
- `StoreTimeEntryRequest`
- `UpdateTimeEntryRequest`
- `StoreUserRequest`
- `UpdateUserRequest`
- `StoreRoleRequest`
- `UpdateRoleRequest`

### Shared Request Concerns

- `app/Http/Requests/Concerns/NormalizesTaskInput.php`

## 9. Middleware

### Custom Middleware

- `SetLocale`
- `EnsureUserIsActive`

### Middleware Bootstrapping

Defined in `bootstrap/app.php`:

- guest redirect to `login`
- `SetLocale` appended to web middleware stack
- aliases:
  - `active`
  - `locale`

## 10. Authorization Layer

### Policies Present

- `UserPolicy`
- `RolePolicy`
- `CustomerPolicy`
- `LeadPolicy`
- `DealPolicy`
- `ProjectPolicy`
- `TaskPolicy`
- `AuditLogPolicy`

### Shared Policy Concern

- `app/Policies/Concerns/ChecksPermissions.php`

### Gate Behavior

- `Gate::before(...)` allows full access for `Admin`

## 11. Services Layer

- `DashboardService`
- `ReportService`
- `AuditLogService`
- `LeadConversionService`
- `TaskWorkflowService`

### Service Responsibilities

- `DashboardService`: KPIs, dashboard aggregates, widgets
- `ReportService`: report queries and export-ready data
- `AuditLogService`: audit record writing
- `LeadConversionService`: lead-to-customer conversion
- `TaskWorkflowService`: task assignments, logs, project progress sync

## 12. Notification Layer

### Notification Classes

- `TaskAssignedNotification`
- `TaskCommentAddedNotification`
- `TaskDeadlineReminderNotification`
- `ProjectStatusChangedNotification`
- `LeadConvertedNotification`

### Notification Delivery Context

- in-app notifications via database notifications table
- notification center in navbar
- dedicated notifications page

## 13. Models

- `User`
- `Role`
- `Permission`
- `Customer`
- `CustomerInteraction`
- `Lead`
- `Deal`
- `DealStage`
- `FollowUp`
- `Project`
- `Task`
- `TaskComment`
- `TaskLog`
- `TimeEntry`
- `Attachment`
- `Tag`
- `AuditLog`
- `Setting`

## 14. Database Driver & Environment

### Intended Database

- `MySQL`

### Current Example Environment

From `.env.example`:

- `DB_CONNECTION=mysql`
- `DB_HOST=127.0.0.1`
- `DB_PORT=3306`
- `DB_DATABASE=crm_pm`
- `DB_USERNAME=root`
- `DB_PASSWORD=`

### Current Local Runtime Notes

- application URL used locally: `http://127.0.0.1:8000`
- locale default in example: `ar`
- queue driver in example: `database`
- session driver in example: `database`
- cache store in example: `database`
- mail driver in example: `log`

## 15. Database Tables

القسم التالي تقني فقط: أسماء الجداول والغرض الهندسي منها.

### Laravel Base Tables

- `users`
  - core identity table
- `cache`
  - Laravel cache backend
- `jobs`
  - queued job storage
- `notifications`
  - Laravel database notifications

### Authorization Tables

- `roles`
  - role definitions
- `permissions`
  - permission definitions
- `role_user`
  - user-role pivot
- `permission_role`
  - role-permission pivot
- `permission_user`
  - direct user-permission pivot

### CRM Tables

- `customers`
  - customer master records
- `customer_interactions`
  - calls / emails / meetings / notes history
- `leads`
  - lead records before conversion
- `deal_stages`
  - CRM pipeline stage definitions
- `deals`
  - sales / pipeline deal records
- `follow_ups`
  - scheduled follow-ups for customers or leads

### Project Management Tables

- `projects`
  - project master records
- `project_user`
  - project members pivot
- `tasks`
  - task records with hierarchy support via `parent_id`
- `task_user`
  - task assignees pivot
- `task_comments`
  - discussion/comments on tasks
- `task_logs`
  - task activity log entries
- `time_entries`
  - time tracking records
- `attachments`
  - polymorphic file attachments
- `tags`
  - reusable task tags
- `task_tag`
  - task-tags pivot

### System / Meta Tables

- `audit_logs`
  - system-level audit trail
- `settings`
  - application settings / key-value configuration

## 16. Migrations Present

- `0001_01_01_000000_create_users_table.php`
- `0001_01_01_000001_create_cache_table.php`
- `0001_01_01_000002_create_jobs_table.php`
- `2026_04_16_175004_create_roles_table.php`
- `2026_04_16_175005_create_customers_table.php`
- `2026_04_16_175005_create_permissions_table.php`
- `2026_04_16_175006_create_customer_interactions_table.php`
- `2026_04_16_175007_create_leads_table.php`
- `2026_04_16_175008_create_deals_table.php`
- `2026_04_16_175008_create_deal_stages_table.php`
- `2026_04_16_175009_create_follow_ups_table.php`
- `2026_04_16_175009_create_projects_table.php`
- `2026_04_16_175010_create_tasks_table.php`
- `2026_04_16_175011_create_task_comments_table.php`
- `2026_04_16_175011_create_task_logs_table.php`
- `2026_04_16_175012_create_attachments_table.php`
- `2026_04_16_175012_create_time_entries_table.php`
- `2026_04_16_175013_create_settings_table.php`
- `2026_04_16_175013_create_tags_table.php`
- `2026_04_16_175014_create_audit_logs_table.php`
- `2026_04_16_175015_create_notifications_table.php`
- `2026_04_16_175111_create_permission_role_table.php`
- `2026_04_16_175111_create_permission_user_table.php`
- `2026_04_16_175112_create_project_user_table.php`
- `2026_04_16_175112_create_role_user_table.php`
- `2026_04_16_175113_create_task_tag_table.php`
- `2026_04_16_175113_create_task_user_table.php`

## 17. Factories Present

- `UserFactory`
- `RoleFactory`
- `PermissionFactory`
- `CustomerFactory`
- `CustomerInteractionFactory`
- `LeadFactory`
- `DealFactory`
- `DealStageFactory`
- `FollowUpFactory`
- `ProjectFactory`
- `TaskFactory`
- `TaskCommentFactory`
- `TaskLogFactory`
- `TimeEntryFactory`
- `AttachmentFactory`
- `TagFactory`
- `AuditLogFactory`
- `SettingFactory`

## 18. Seeder Layer

### Main Seeder

- `DatabaseSeeder`

### Seeder Coverage

- roles
- permissions
- admin / managers / employees
- customers
- leads
- deals
- projects
- tasks
- task comments
- task logs
- time entries
- tags
- settings
- audit logs
- notifications

## 19. Localization Layer

### Locale Files Present

- `lang/ar.json`
- `lang/ar/auth.php`
- `lang/ar/pagination.php`
- `lang/ar/passwords.php`
- `lang/ar/validation.php`

### Locale Behavior

- default locale support configured to use Arabic UI
- `SetLocale` middleware determines language state
- RTL styling active when locale is `ar`

## 20. Frontend Assets

### Main Files

- `resources/css/app.css`
- `resources/js/app.js`
- `resources/views/layouts/app.blade.php`
- `resources/views/layouts/guest.blade.php`

### Main Frontend Features

- global dark/light theme
- global toast notifications
- kanban drag and drop
- charts
- language switcher
- notification dropdown
- responsive sidebar

## 21. Authentication & Demo Accounts

### Demo Login Credentials

- Admin:
  - Email: `admin@crm-pm.test`
  - Password: `password`

- Managers:
  - `manager0@crm-pm.test` / `password`
  - `manager1@crm-pm.test` / `password`

- Employees:
  - `employee0@crm-pm.test` / `password`
  - `employee1@crm-pm.test` / `password`
  - `employee2@crm-pm.test` / `password`
  - `employee3@crm-pm.test` / `password`
  - `employee4@crm-pm.test` / `password`
  - `employee5@crm-pm.test` / `password`

## 22. Useful Commands

### Install

```bash
composer install
npm install
```

### Environment

```bash
php artisan key:generate
```

### Database

```bash
php artisan migrate --seed
```

### Run

```bash
php artisan serve
npm run dev
```

### Production Assets

```bash
npm run build
```

### Maintenance / Cache

```bash
php artisan optimize:clear
php artisan view:cache
```

### Tests

```bash
php artisan test
```

## 23. Technical URLs

- Home: `http://127.0.0.1:8000`
- Login: `http://127.0.0.1:8000/login`
- Dashboard: `http://127.0.0.1:8000/dashboard`

## 24. Current Technical Notes

- The application currently relies on `MySQL`.
- If MySQL is not running on `127.0.0.1:3306`, any database-backed page will fail.
- Global UI notifications are implemented as toast notifications.
- Language switcher now depends on `flag-icons`.
- The project uses server-rendered Blade, not a separate SPA frontend.

## 25. File Purpose

هذا الملف مخصص كمرجع تقني مختصر ومباشر. عند أي تعديل تقني مهم لاحقًا، يجب تحديثه ليبقى مواكبًا للحالة الفعلية للمشروع.

