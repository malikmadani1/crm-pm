# CRM & PM System

مرجع المشروع الرسمي والحالة التقنية الحالية.

هذا الملف هو المصدر الأساسي لفهم المشروع، تشغيله، معرفة حالته الحالية، ومراجعة بيانات الدخول والهيكل التقني. سأعتبره من الآن فصاعدًا ملفًا حيًا يتم تحديثه مع كل تعديل مهم على النظام.

## 1. ملخص سريع

- اسم المشروع: `CRM & PM System`
- نوع المشروع: Monolithic Laravel Web App
- إصدار Laravel المستخدم فعليًا: `12.56.0`
- PHP المطلوب: `8.2+`
- الواجهة: `Blade + Tailwind CSS + Alpine.js`
- قاعدة البيانات المستهدفة: `MySQL`
- النمط المعماري: `MVC`
- حالة المشروع الحالية: المنظومة الأساسية مبنية ومربوطة، مع CRM + PM + Team + Reports + Notifications + Audit Log + تعريب عربي + RTL + Theme Toggle
- الحالة المحلية الحالية على هذا الجهاز: يوجد عائق تشغيل فعلي سببه أن `MySQL` غير شغّال على `127.0.0.1:3306`

## 2. الحالة الحالية للمشروع

### ما تم إنجازه

- بناء لوحة تحكم متكاملة داخل Laravel بدون فصل API خارجي.
- بناء وحدات CRM:
  - العملاء `Customers`
  - العملاء المحتملون `Leads`
  - الصفقات `Deals`
  - مراحل الصفقات `Deal Pipeline`
  - التفاعلات `Customer Interactions`
  - المتابعات `Follow-ups`
- بناء وحدات إدارة المشاريع:
  - المشاريع `Projects`
  - المهام `Tasks`
  - لوحة `Kanban`
  - تعليقات المهام
  - تتبع الوقت `Time Entries`
  - سجلات المهام `Task Logs`
- بناء إدارة الفريق:
  - المستخدمون `Users`
  - الأدوار `Roles`
  - الصلاحيات `Permissions`
- بناء التقارير:
  - `Sales`
  - `CRM`
  - `Projects`
  - `Tasks`
  - `Team`
- بناء الإشعارات الداخلية وسجل العمليات `Audit Log`.
- بناء واجهة عربية مع `RTL` وخط `Cairo`.
- إضافة زر تبديل اللغة `العربية / English`.
- تحسين زر المظهر `Theme` وإصلاح تشوه الـ light mode.
- إضافة سياسات صلاحيات `Policies` وطلبات تحقق `Form Requests`.
- بناء `Seeders` و`Factories` وبيانات Demo جاهزة.

### الحالة التشغيلية الآن

- الكود نفسه موجود ومترابط.
- الواجهة مبنية.
- الـ routes موجودة.
- الـ seeders موجودة.
- بيانات الدخول demo معروفة.
- العائق الحالي محلي فقط: خدمة `MySQL` ليست شغالة على جهازك الآن.

## 3. المشكلة الحالية المفتوحة

المشكلة الحالية ليست من الكود، بل من البيئة المحلية:

```txt
SQLSTATE[HY000] [2002]
No connection could be made because the target machine actively refused it
Host: 127.0.0.1
Port: 3306
```

### معنى ذلك

- Laravel يحاول الاتصال بقاعدة `MySQL`.
- ملف `.env` مضبوط على:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=crm
DB_USERNAME=root
DB_PASSWORD=
```

- لكن لا يوجد خادم MySQL شغّال على هذا المنفذ حاليًا.

### ماذا تم عمله للتخفيف من الانهيار

- تم تعديل `AppServiceProvider` حتى لا تنكسر صفحة الخطأ نفسها إذا كانت قاعدة البيانات متوقفة.
- تم تغليف تحميل `roles` وعدّاد الإشعارات بحيث يفشلان بهدوء بدل تفجير واجهة الخطأ.

## 4. التقنيات والحزم المستخدمة

### Backend

- `laravel/framework ^12.0`
- `laravel/tinker`
- `laravel/breeze`

### Frontend

- `Tailwind CSS`
- `Alpine.js`
- `Chart.js`
- `SortableJS`
- `Vite`

### أدوات التطوير

- `Laravel Pint`
- `PHPUnit`
- `Faker`
- `laravel/pail`

## 5. الهيكل المعماري

المشروع مبني كـ Laravel monolith، وكل شيء يعمل داخل التطبيق نفسه:

- Routes في `routes/web.php`
- Controllers داخل `app/Http/Controllers`
- Models داخل `app/Models`
- Validation Requests داخل `app/Http/Requests`
- Policies داخل `app/Policies`
- Services داخل `app/Services`
- Views داخل `resources/views`
- Localization داخل `lang`
- Migrations / Seeders / Factories داخل `database`

### الميدلوير المخصصة

- `SetLocale`
- `EnsureUserIsActive`

### الخدمات المخصصة

- `DashboardService`
- `ReportService`
- `LeadConversionService`
- `TaskWorkflowService`
- `AuditLogService`

## 6. المجلدات والملفات المهمة

```txt
app/
  Http/
    Controllers/
      CRM/
      PM/
      Team/
      Auth/
    Requests/
    Middleware/
  Models/
  Policies/
  Services/
  Notifications/

bootstrap/
  app.php

config/
  crm_pm.php
  database.php

database/
  migrations/
  factories/
  seeders/

lang/
  ar.json
  ar/

resources/
  css/
  js/
  views/

routes/
  web.php
  auth.php
```

## 7. الوحدات المبنية فعليًا

### Authentication

- Login
- Logout
- Forgot Password
- Reset Password
- Profile
- Change Password

### CRM

- Customers CRUD
- Leads CRUD
- Lead conversion
- Deals CRUD
- Deals pipeline
- Customer interactions
- Follow-ups

### PM

- Projects CRUD
- Tasks CRUD
- Task comments
- Kanban drag & drop
- Time tracking
- Project progress

### Team

- Users CRUD
- Roles CRUD
- Permissions-based authorization

### Reports

- Sales reports
- CRM reports
- Project reports
- Task reports
- Team reports
- Export endpoint موجود
- التصدير الحالي العملي: `CSV`

### System

- Notifications center
- Audit log
- Dashboard
- Arabic localization
- Theme switching
- Error pages `403 / 404 / 500`

## 8. قاعدة البيانات

### الجداول الأساسية الموجودة

- `users`
- `roles`
- `permissions`
- `role_user`
- `permission_role`
- `permission_user`
- `customers`
- `customer_interactions`
- `leads`
- `deals`
- `deal_stages`
- `follow_ups`
- `projects`
- `project_user`
- `tasks`
- `task_user`
- `task_comments`
- `task_logs`
- `time_entries`
- `attachments`
- `tags`
- `task_tag`
- `audit_logs`
- `notifications`
- `settings`
- بالإضافة إلى جداول Laravel الأساسية: `cache`, `jobs`

### العلاقة العامة بين الوحدات

- العميل يمكن أن يرتبط بصفقات ومشاريع وتفاعلات ومتابعات.
- الـ lead يمكن تحويله إلى customer.
- الصفقة ترتبط بعميل وقد ترتبط بـ lead.
- المشروع يرتبط بعميل وبمدير وأعضاء.
- المهمة ترتبط بمشروع، ويمكن أن يكون لها:
  - assignees
  - tags
  - comments
  - logs
  - time entries
  - subtasks عبر `parent_id`

## 9. الصلاحيات والأمان

- Authorization عبر `Policies`
- صلاحيات مهيكلة من خلال `roles` و`permissions`
- `Gate::before` يمنح `Admin` صلاحية شاملة
- حماية CSRF افتراضيًا عبر Laravel
- تحقق إدخال عبر `Form Requests`
- حماية كلمات المرور عبر hashing
- Middleware للمستخدمين غير النشطين
- منع الوصول غير المصرح به للمناطق المحمية

## 10. التعريب والواجهة

### اللغة

- اللغة العربية مفعلة
- RTL مفعّل
- خط `Cairo` مفعّل للواجهة العربية
- زر تبديل اللغة موجود

### المظهر

- يوجد `Theme Toggle`
- تم تحسين سلوك الـ light/dark mode
- التصميم الحالي مبني على Dashboard حديث داخل Blade
- تم اعتماد نظام `Toast Notifications` موحّد للنجاح والفشل والتنبيهات والمعلومات، مع `Progress Bar` واختفاء تلقائي بدل الرسائل المطبوعة داخل الصفحة

## 11. الملفات التقنية المحورية

- `routes/web.php`
- `bootstrap/app.php`
- `config/crm_pm.php`
- `app/Providers/AppServiceProvider.php`
- `app/Models/User.php`
- `app/Services/DashboardService.php`
- `app/Services/ReportService.php`
- `resources/views/layouts/app.blade.php`
- `resources/views/layouts/guest.blade.php`
- `resources/css/app.css`
- `database/seeders/DatabaseSeeder.php`

## 12. بيانات تسجيل الدخول التجريبية

هذه الحسابات تعمل بعد تشغيل قاعدة البيانات وتنفيذ الـ seeding:

### Admin

- البريد: `admin@crm-pm.test`
- كلمة المرور: `password`

### Managers

- `manager0@crm-pm.test` / `password`
- `manager1@crm-pm.test` / `password`

### Employees

- `employee0@crm-pm.test` / `password`
- `employee1@crm-pm.test` / `password`
- `employee2@crm-pm.test` / `password`
- `employee3@crm-pm.test` / `password`
- `employee4@crm-pm.test` / `password`
- `employee5@crm-pm.test` / `password`

## 13. الروابط المهمة

بعد تشغيل المشروع:

- الصفحة الرئيسية: `http://127.0.0.1:8000`
- تسجيل الدخول: `http://127.0.0.1:8000/login`
- لوحة التحكم: `http://127.0.0.1:8000/dashboard`

## 14. خطوات التشغيل المحلية الصحيحة

### 1. تأكد من تشغيل MySQL

يجب أن تكون قاعدة البيانات متاحة على:

```txt
127.0.0.1:3306
```

### 2. أنشئ قاعدة البيانات

اسم قاعدة البيانات الحالية في `.env`:

```txt
crm
```

### 3. نفّذ أوامر Laravel

```bash
composer install
npm install
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

### 4. ابنِ الواجهة

```bash
npm run build
```

## 15. ملاحظات مهمة جدًا

- المشروع يستخدم نفس نسخة Laravel الموجودة فعلًا ولم يتم تغيير الإصدار.
- بيئة `.env.example` تقترح `mysql` أيضًا.
- الملف `.env` الحالي مضبوط على `APP_URL=http://127.0.0.1:8000`.
- إذا ظهر خطأ `419 Page Expired` فعادة يكون بسبب اختلاف الرابط أو الكاش أو الجلسة، وقد تم إصلاح ذلك سابقًا بتوحيد `APP_URL`.
- إذا ظهر `500` متعلق بقاعدة البيانات، فالسبب غالبًا أن MySQL متوقف أو أن قاعدة `crm` غير موجودة.
- التصدير PDF/Excel الكامل لم يُفعّل بحزم إضافية حتى الآن.
- التصدير الحالي الموجود عمليًا هو `CSV`.

## 16. التحقق الذي تم سابقًا

تم التحقق سابقًا من النقاط التالية عندما كانت البيئة سليمة:

- `php artisan route:list`
- `php artisan migrate:fresh --seed`
- `npm run build`
- `php artisan view:cache`
- تسجيل الدخول بحسابات demo
- تحميل `dashboard`

## 17. ما الذي يحتاج متابعة لاحقًا

- تشغيل MySQL محليًا على الجهاز الحالي
- التأكد من وجود قاعدة `crm`
- تنفيذ `migrate --seed` عند الحاجة
- مراجعة شكل بعض الصفحات الفردية تحت light mode إن رغبت بتحسين بصري أدق
- إضافة حزم PDF / Excel إن أردت تصديرًا احترافيًا إضافيًا

## 18. سياسة تحديث هذا الملف

من الآن فصاعدًا:

- هذا الملف هو مرجع المشروع الأساسي
- أي تعديل مهم في النظام يجب أن ينعكس هنا
- أي تغيير في بيانات الدخول أو التشغيل أو البيئة أو الموديولات سأضيفه لهذا الملف
- إذا طلبت مني "حدّث الملف" أو "راجع الحالة" فسأراجع هذا الملف وأعدّله مباشرة
