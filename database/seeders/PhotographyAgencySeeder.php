<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\CustomerInteraction;
use App\Models\Deal;
use App\Models\DealStage;
use App\Models\FollowUp;
use App\Models\Lead;
use App\Models\Permission;
use App\Models\Project;
use App\Models\Role;
use App\Models\Setting;
use App\Models\Tag;
use App\Models\Task;
use App\Models\TaskComment;
use App\Models\TaskLog;
use App\Models\TimeEntry;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PhotographyAgencySeeder extends Seeder
{
    private const USER_EMAILS = [
        'nour@pixelwave.local',
        'khaled@pixelwave.local',
        'maya@pixelwave.local',
        'samer@pixelwave.local',
        'lina@pixelwave.local',
        'omar@pixelwave.local',
    ];

    private const TAG_SLUGS = [
        'photo-shoot',
        'video-production',
        'reels',
        'social-media',
        'paid-ads',
        'branding',
        'editing',
        'urgent-client',
    ];

    private const PROJECT_CODES = [
        'PWD-2026-001',
        'PWD-2026-002',
        'PWD-2026-003',
        'PWD-2026-004',
        'PWD-2026-005',
        'PWD-2026-006',
    ];

    private const CUSTOMER_EMAILS = [
        'marketing@lavandacafe.test',
        'events@orionhotels.test',
        'brand@greenbasket.test',
        'hello@novafitness.test',
        'leasing@atlasrealestate.test',
        'info@belleclinics.test',
        'founder@urbanbite.test',
        'contact@luminafashion.test',
    ];

    private const LEAD_EMAILS = [
        'growth@cedarhomes.test',
        'marketing@bluebayresort.test',
        'owner@sweetslane.test',
        'hello@stridegear.test',
        'events@damascenehall.test',
        'team@bytecraft.test',
        'sales@petraevents.test',
        'brand@glowcosmetics.test',
        'info@northstarclinic.test',
        'hello@freshcart.test',
    ];

    public function run(): void
    {
        DB::transaction(function (): void {
            self::clear();

            $this->seedFoundation();
            $users = $this->seedUsers();
            $tags = $this->seedTags();
            $stages = DealStage::query()->orderBy('position')->get()->keyBy('slug');
            $customers = $this->seedCustomers($users);
            $leads = $this->seedLeads($users);

            $this->seedDeals($users, $customers, $leads, $stages);
            $this->seedProjectsAndTasks($users, $customers, $tags);
            $this->seedSettings();
        });
    }

    public static function clear(): void
    {
        $projectIds = Project::withTrashed()->whereIn('code', self::PROJECT_CODES)->pluck('id');
        $taskIds = Task::withTrashed()->whereIn('project_id', $projectIds)->pluck('id');
        $customerIds = Customer::withTrashed()->whereIn('email', self::CUSTOMER_EMAILS)->pluck('id');
        $leadIds = Lead::withTrashed()->whereIn('email', self::LEAD_EMAILS)->pluck('id');
        $userIds = User::query()->whereIn('email', self::USER_EMAILS)->pluck('id');
        $tagIds = Tag::query()->whereIn('slug', self::TAG_SLUGS)->pluck('id');

        if ($taskIds->isNotEmpty()) {
            DB::table('task_tag')->whereIn('task_id', $taskIds)->delete();
            DB::table('task_user')->whereIn('task_id', $taskIds)->delete();
            TaskComment::query()->whereIn('task_id', $taskIds)->delete();
            TaskLog::query()->whereIn('task_id', $taskIds)->delete();
            TimeEntry::query()->whereIn('task_id', $taskIds)->delete();
            Task::withTrashed()->whereIn('id', $taskIds)->forceDelete();
        }

        if ($projectIds->isNotEmpty()) {
            DB::table('project_user')->whereIn('project_id', $projectIds)->delete();
            TimeEntry::query()->whereIn('project_id', $projectIds)->delete();
            Project::withTrashed()->whereIn('id', $projectIds)->forceDelete();
        }

        Deal::withTrashed()
            ->whereIn('customer_id', $customerIds)
            ->orWhereIn('lead_id', $leadIds)
            ->orWhere('title', 'like', 'PixelWave:%')
            ->orWhere('title', 'like', 'بيكسل ويف:%')
            ->forceDelete();

        FollowUp::query()
            ->whereIn('customer_id', $customerIds)
            ->orWhereIn('lead_id', $leadIds)
            ->orWhereIn('assigned_to', $userIds)
            ->delete();

        CustomerInteraction::query()->whereIn('customer_id', $customerIds)->delete();

        if ($leadIds->isNotEmpty()) {
            Lead::withTrashed()->whereIn('id', $leadIds)->forceDelete();
        }

        if ($customerIds->isNotEmpty()) {
            Customer::withTrashed()->whereIn('id', $customerIds)->forceDelete();
        }

        if ($tagIds->isNotEmpty()) {
            DB::table('task_tag')->whereIn('tag_id', $tagIds)->delete();
            Tag::query()->whereIn('id', $tagIds)->delete();
        }

        if ($userIds->isNotEmpty()) {
            DB::table('role_user')->whereIn('user_id', $userIds)->delete();
            DB::table('permission_user')->whereIn('user_id', $userIds)->delete();
            User::query()->whereIn('id', $userIds)->delete();
        }

        Setting::query()->whereIn('key', [
            'demo_company_profile',
            'demo_default_service_mix',
            'demo_seed_source',
        ])->delete();
    }

    private function seedFoundation(): void
    {
        foreach (config('crm_pm.permissions') as $module => $actions) {
            foreach ($actions as $action) {
                Permission::query()->updateOrCreate(
                    ['slug' => "{$module}.{$action}"],
                    [
                        'name' => $this->permissionName($module, $action),
                        'module' => $module,
                        'description' => 'صلاحية ضمن نظام إدارة العملاء والمشاريع.',
                    ]
                );
            }
        }

        $permissions = Permission::query()->get();
        $roles = [
            'admin' => ['name' => 'مدير النظام', 'description' => 'صلاحيات كاملة على النظام.'],
            'manager' => ['name' => 'مدير فريق', 'description' => 'يدير العملاء والمشاريع والتقارير.'],
            'employee' => ['name' => 'موظف تنفيذ', 'description' => 'ينفذ المهام اليومية ويتابع التسليمات.'],
        ];

        foreach ($roles as $slug => $role) {
            Role::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $role['name'],
                    'guard_name' => 'web',
                    'description' => $role['description'],
                    'is_system' => true,
                ]
            );
        }

        Role::query()->where('slug', 'admin')->first()?->permissions()->sync($permissions->pluck('id'));

        foreach ([
            ['name' => 'عميل جديد', 'slug' => 'new_lead', 'color' => 'slate', 'position' => 1, 'is_won' => false, 'is_lost' => false],
            ['name' => 'تم التواصل', 'slug' => 'contacted', 'color' => 'sky', 'position' => 2, 'is_won' => false, 'is_lost' => false],
            ['name' => 'مؤهل', 'slug' => 'qualified', 'color' => 'indigo', 'position' => 3, 'is_won' => false, 'is_lost' => false],
            ['name' => 'تم إرسال العرض', 'slug' => 'proposal_sent', 'color' => 'amber', 'position' => 4, 'is_won' => false, 'is_lost' => false],
            ['name' => 'تفاوض', 'slug' => 'negotiation', 'color' => 'orange', 'position' => 5, 'is_won' => false, 'is_lost' => false],
            ['name' => 'مربوحة', 'slug' => 'won', 'color' => 'emerald', 'position' => 6, 'is_won' => true, 'is_lost' => false],
            ['name' => 'خاسرة', 'slug' => 'lost', 'color' => 'rose', 'position' => 7, 'is_won' => false, 'is_lost' => true],
        ] as $stage) {
            DealStage::query()->updateOrCreate(['slug' => $stage['slug']], $stage);
        }
    }

    private function seedUsers(): Collection
    {
        $roleIdsBySlug = Role::query()->whereIn('slug', ['admin', 'manager', 'employee'])->pluck('id', 'slug');

        return collect([
            ['نور حداد', 'nour@pixelwave.local', 'PWD-ADM-01', 'مديرة الوكالة', 'admin'],
            ['خالد منصور', 'khaled@pixelwave.local', 'PWD-MGR-01', 'مدير الإنتاج', 'manager'],
            ['مايا صالح', 'maya@pixelwave.local', 'PWD-MGR-02', 'مديرة التسويق الرقمي', 'manager'],
            ['سامر نصار', 'samer@pixelwave.local', 'PWD-VID-01', 'مصور ومونتير فيديو', 'employee'],
            ['لينا بركات', 'lina@pixelwave.local', 'PWD-DES-01', 'مصممة غرافيك', 'employee'],
            ['عمر درويش', 'omar@pixelwave.local', 'PWD-ADS-01', 'مختص إعلانات ممولة', 'employee'],
        ])->map(function (array $row) use ($roleIdsBySlug): User {
            [$name, $email, $employeeCode, $jobTitle, $roleSlug] = $row;

            $user = User::query()->create([
                'name' => $name,
                'email' => $email,
                'employee_code' => $employeeCode,
                'job_title' => $jobTitle,
                'phone' => '+963 9'.fake()->numerify('## ### ###'),
                'timezone' => 'Asia/Damascus',
                'locale' => 'ar',
                'is_active' => true,
                'last_seen_at' => now()->subMinutes(fake()->numberBetween(5, 180)),
                'email_verified_at' => now(),
                'password' => 'password',
            ]);

            $user->roles()->sync([$roleIdsBySlug[$roleSlug]]);

            return $user;
        })->keyBy('email');
    }

    private function seedTags(): Collection
    {
        return collect([
            ['جلسة تصوير', 'photo-shoot', 'sky'],
            ['إنتاج فيديو', 'video-production', 'rose'],
            ['ريلز', 'reels', 'amber'],
            ['سوشال ميديا', 'social-media', 'indigo'],
            ['إعلانات ممولة', 'paid-ads', 'emerald'],
            ['هوية بصرية', 'branding', 'violet'],
            ['مونتاج وتعديل', 'editing', 'slate'],
            ['عميل مستعجل', 'urgent-client', 'orange'],
        ])->map(fn (array $tag): Tag => Tag::query()->create([
            'name' => $tag[0],
            'slug' => $tag[1],
            'color' => $tag[2],
        ]))->keyBy('slug');
    }

    private function seedCustomers(Collection $users): Collection
    {
        return collect([
            ['مقهى لافندا', 'marketing@lavandacafe.test', 'رنا قاسم', 'مسؤولة التسويق', 'active', 'إنستغرام', 'تصوير أطباق موسمي وباقة ريلز أسبوعية.'],
            ['فنادق أوريون', 'events@orionhotels.test', 'فادي رحال', 'مدير الفعاليات', 'active', 'ترشيح', 'محتوى ضيافة شهري وتغطية مناسبات الفندق.'],
            ['سوق السلة الخضراء', 'brand@greenbasket.test', 'ديمة سعد', 'مديرة العلامة', 'active', 'الموقع الإلكتروني', 'تصوير منتجات وتجديد الكتالوج وحملات ميتا.'],
            ['نادي نوفا الرياضي', 'hello@novafitness.test', 'طارق نادر', 'المدير العام', 'active', 'فيسبوك', 'حملة إطلاق للحصص الجديدة والمدربين.'],
            ['أطلس العقارية', 'leasing@atlasrealestate.test', 'يزن حموي', 'مدير المبيعات', 'potential', 'لينكدإن', 'فيديوهات جولات عقارية وقمع لتوليد العملاء المحتملين.'],
            ['عيادات بيل', 'info@belleclinics.test', 'منى فارس', 'منسقة العيادة', 'active', 'ترشيح', 'محتوى قبل وبعد وتقويم سوشال وإعلانات مواعيد.'],
            ['أوربان بايت', 'founder@urbanbite.test', 'كريم آغا', 'المؤسس', 'potential', 'إنستغرام', 'تحديث الهوية البصرية ومحتوى أسبوع الافتتاح.'],
            ['لومينا فاشن', 'contact@luminafashion.test', 'سارة شامي', 'المديرة الإبداعية', 'active', 'معرض تجاري', 'تصوير لوك بوك وحملة مع صناع محتوى.'],
        ])->map(function (array $row, int $index) use ($users): Customer {
            [$company, $email, $name, $title, $status, $source, $notes] = $row;
            $owner = $index % 2 === 0 ? $users['maya@pixelwave.local'] : $users['khaled@pixelwave.local'];

            $customer = Customer::query()->create([
                'owner_id' => $owner->id,
                'name' => $name,
                'phone' => '+963 9'.fake()->numerify('## ### ###'),
                'email' => $email,
                'company_name' => $company,
                'job_title' => $title,
                'address' => fake()->streetAddress(),
                'city' => fake()->randomElement(['دمشق', 'حلب', 'حمص', 'اللاذقية']),
                'country' => 'سوريا',
                'source' => $source,
                'status' => $status,
                'last_contacted_at' => now()->subDays(fake()->numberBetween(1, 18)),
                'notes' => $notes,
            ]);

            $this->seedCustomerActivity($customer, $owner);

            return $customer;
        })->keyBy('company_name');
    }

    private function seedCustomerActivity(Customer $customer, User $owner): void
    {
        foreach ([
            ['meeting', 'جلسة اكتشاف', 'تمت مراجعة أهداف المحتوى والمنتجات ومواقع التصوير والجمهور المستهدف.'],
            ['email', 'متابعة العرض', 'تم إرسال نطاق الخدمة والجدول الزمني التقديري ومسودة الميزانية.'],
            ['call', 'متابعة الإنتاج', 'تم تأكيد قائمة اللقطات وتوفر الفريق ومواعيد النشر.'],
        ] as $index => $interaction) {
            CustomerInteraction::query()->create([
                'customer_id' => $customer->id,
                'user_id' => $owner->id,
                'type' => $interaction[0],
                'subject' => $interaction[1],
                'details' => $interaction[2],
                'interaction_at' => now()->subDays(15 - ($index * 5)),
                'metadata' => ['channel' => $interaction[0], 'seed' => 'photography_agency_ar'],
            ]);
        }

        FollowUp::query()->create([
            'customer_id' => $customer->id,
            'lead_id' => null,
            'assigned_to' => $owner->id,
            'title' => 'إرسال تقويم المحتوى القادم',
            'notes' => 'يتضمن مواعيد التصوير ونصوص المنشورات ومواعيد تسليم تصاميم الإعلانات.',
            'status' => 'pending',
            'priority' => $customer->status === 'active' ? 'high' : 'medium',
            'due_at' => now()->addDays(fake()->numberBetween(2, 9))->setTime(11, 0),
        ]);
    }

    private function seedLeads(Collection $users): Collection
    {
        return collect([
            ['سيدار هومز', 'growth@cedarhomes.test', 'هبة عمار', 'مسؤولة المبيعات', 'qualified', 'open', 8500, 65, 'لينكدإن', 'بحاجة إلى فيديوهات عقارية وتصاميم صفحة هبوط لمجمع جديد.'],
            ['منتجع بلو باي', 'marketing@bluebayresort.test', 'مازن كوري', 'مدير التسويق', 'proposal_sent', 'open', 14000, 70, 'ترشيح', 'يفكر بحملة صيفية مع تصوير درون وإعلانات ممولة.'],
            ['سويتس لين', 'owner@sweetslane.test', 'نادين تومة', 'المالكة', 'contacted', 'open', 3200, 35, 'إنستغرام', 'طلبت تصوير حلويات وريلز قصيرة لوصفات سريعة.'],
            ['سترايد غير', 'hello@stridegear.test', 'جواد مالك', 'مدير التجارة الإلكترونية', 'negotiation', 'open', 9200, 80, 'الموقع الإلكتروني', 'يتفاوض على تصوير شهري وحملات تحسين التحويل.'],
            ['قاعة الدمشقي', 'events@damascenehall.test', 'رامي حاتم', 'مدير القاعة', 'new_lead', 'open', 4800, 20, 'معرض تجاري', 'يريد باقات تغطية مناسبات.'],
            ['بايت كرافت', 'team@bytecraft.test', 'لين صفدي', 'شريكة مؤسسة', 'qualified', 'open', 6500, 55, 'لينكدإن', 'فيديو إطلاق لخدمة تقنية وحملة إثبات اجتماعي.'],
            ['بترا إيفنتس', 'sales@petraevents.test', 'حسين درزي', 'مدير المبيعات', 'lost', 'lost', 2600, 0, 'ترشيح', 'تم تجميد الطلب بعد مراجعة الميزانية داخلياً.'],
            ['غلو كوزمتكس', 'brand@glowcosmetics.test', 'آلاء ريس', 'مسؤولة العلامة', 'proposal_sent', 'open', 11800, 75, 'إنستغرام', 'صور منتجات رئيسية وحقائب صناع محتوى وخطة تيك توك.'],
            ['عيادة نورث ستار', 'info@northstarclinic.test', 'سوسن نوري', 'مديرة العيادة', 'contacted', 'open', 5400, 40, 'فيسبوك', 'مهتمة بمحتوى يبني الثقة وإعلانات مواعيد.'],
            ['فريش كارت', 'hello@freshcart.test', 'باسل قطان', 'مسؤول العمليات', 'new_lead', 'open', 7300, 25, 'الموقع الإلكتروني', 'بحاجة إلى محتوى كتالوج منتجات لتطبيق توصيل.'],
        ])->map(function (array $row, int $index) use ($users): Lead {
            [$company, $email, $name, $title, $stage, $status, $value, $probability, $source, $notes] = $row;
            $owner = $index % 2 === 0 ? $users['maya@pixelwave.local'] : $users['nour@pixelwave.local'];

            $lead = Lead::unguarded(fn (): Lead => Lead::query()->create([
                'owner_id' => $owner->id,
                'name' => $name,
                'phone' => '+963 9'.fake()->numerify('## ### ###'),
                'email' => $email,
                'company_name' => $company,
                'job_title' => $title,
                'address' => fake()->streetAddress(),
                'city' => fake()->randomElement(['دمشق', 'حلب', 'اللاذقية']),
                'country' => 'سوريا',
                'source' => $source,
                'stage' => $stage,
                'status' => $status,
                'estimated_value' => $value,
                'probability' => $probability,
                'expected_close_date' => now()->addDays(10 + ($index * 3))->toDateString(),
                'notes' => $notes,
            ]));

            FollowUp::query()->create([
                'customer_id' => null,
                'lead_id' => $lead->id,
                'assigned_to' => $owner->id,
                'title' => $stage === 'proposal_sent' ? 'مراجعة قرار العرض' : 'تأهيل موجز الحملة',
                'notes' => 'تأكيد ملاءمة الباقة وصاحب القرار ونطاق الميزانية وموعد الإطلاق المتوقع.',
                'status' => $status === 'lost' ? 'cancelled' : 'pending',
                'priority' => $probability >= 65 ? 'high' : 'medium',
                'due_at' => now()->addDays(fake()->numberBetween(1, 12))->setTime(13, 30),
            ]);

            return $lead;
        })->keyBy('company_name');
    }

    private function seedDeals(Collection $users, Collection $customers, Collection $leads, Collection $stages): void
    {
        collect([
            ['بيكسل ويف: عقد محتوى شهري لمقهى لافندا', 'مقهى لافندا', null, 'won', 6200, 100, 'won'],
            ['بيكسل ويف: تغطية فعاليات ربع سنوية لفنادق أوريون', 'فنادق أوريون', null, 'negotiation', 18500, 80, 'open'],
            ['بيكسل ويف: كتالوج وإعلانات سوق السلة الخضراء', 'سوق السلة الخضراء', null, 'proposal_sent', 9700, 70, 'open'],
            ['بيكسل ويف: حملة إطلاق نادي نوفا الرياضي', 'نادي نوفا الرياضي', null, 'qualified', 7600, 60, 'open'],
            ['بيكسل ويف: فيديوهات جولات عقارية لأطلس', 'أطلس العقارية', null, 'contacted', 11200, 35, 'open'],
            ['بيكسل ويف: خطة سوشال مدفوعة لعيادات بيل', 'عيادات بيل', null, 'won', 8300, 100, 'won'],
            ['بيكسل ويف: حملة الصيف لمنتجع بلو باي', null, 'منتجع بلو باي', 'proposal_sent', 14000, 70, 'open'],
            ['بيكسل ويف: إطلاق صناع محتوى لغلو كوزمتكس', null, 'غلو كوزمتكس', 'negotiation', 11800, 85, 'open'],
            ['بيكسل ويف: تغطية مصغرة لبترا إيفنتس', null, 'بترا إيفنتس', 'lost', 2600, 0, 'lost'],
        ])->each(function (array $row) use ($users, $customers, $leads, $stages): void {
            [$title, $customerName, $leadName, $stageSlug, $value, $probability, $status] = $row;
            $stage = $stages[$stageSlug];

            Deal::query()->create([
                'lead_id' => $leadName ? $leads[$leadName]->id : null,
                'customer_id' => $customerName ? $customers[$customerName]->id : null,
                'owner_id' => $users['maya@pixelwave.local']->id,
                'stage_id' => $stage->id,
                'title' => $title,
                'value' => $value,
                'probability' => $probability,
                'expected_close_date' => now()->addDays($status === 'open' ? 18 : -7)->toDateString(),
                'status' => $status,
                'closed_at' => in_array($status, ['won', 'lost'], true) ? now()->subDays(fake()->numberBetween(3, 20)) : null,
                'notes' => 'فرصة بيع لخدمات التصوير والتسويق الرقمي ضمن بيانات شركة بيكسل ويف التجريبية.',
            ]);
        });
    }

    private function seedProjectsAndTasks(Collection $users, Collection $customers, Collection $tags): void
    {
        collect([
            ['PWD-2026-001', 'تصوير قائمة رمضان لمقهى لافندا', 'مقهى لافندا', 'khaled@pixelwave.local', 'in_progress', 'high', 68, 6200],
            ['PWD-2026-002', 'تغطية فعاليات وريـلز لفنادق أوريون', 'فنادق أوريون', 'khaled@pixelwave.local', 'in_progress', 'high', 42, 18500],
            ['PWD-2026-003', 'تجديد كتالوج منتجات السلة الخضراء', 'سوق السلة الخضراء', 'maya@pixelwave.local', 'completed', 'medium', 100, 9700],
            ['PWD-2026-004', 'إعلانات إطلاق وفيديوهات مدربين لنادي نوفا', 'نادي نوفا الرياضي', 'maya@pixelwave.local', 'in_progress', 'high', 55, 7600],
            ['PWD-2026-005', 'برنامج محتوى الثقة لعيادات بيل', 'عيادات بيل', 'khaled@pixelwave.local', 'paused', 'medium', 30, 8300],
            ['PWD-2026-006', 'لوك بوك صيفي للومينا فاشن', 'لومينا فاشن', 'maya@pixelwave.local', 'on_hold', 'medium', 22, 10400],
        ])->each(function (array $row, int $index) use ($users, $customers, $tags): void {
            [$code, $name, $customerName, $managerEmail, $status, $priority, $progress, $budget] = $row;
            $manager = $users[$managerEmail];

            $project = Project::query()->create([
                'customer_id' => $customers[$customerName]->id,
                'manager_id' => $manager->id,
                'name' => $name,
                'code' => $code,
                'description' => 'مشروع تسليم للعميل يشمل التصوير والفيديو وتخطيط المحتوى وتنفيذ التسويق الرقمي.',
                'start_date' => now()->subDays(28 - ($index * 3))->toDateString(),
                'due_date' => now()->addDays(14 + ($index * 5))->toDateString(),
                'budget' => $budget,
                'status' => $status,
                'priority' => $priority,
                'progress' => $progress,
                'last_activity_at' => now()->subHours(fake()->numberBetween(2, 48)),
            ]);

            $memberIds = collect([
                $manager->id,
                $users['samer@pixelwave.local']->id,
                $users['lina@pixelwave.local']->id,
                $users['omar@pixelwave.local']->id,
            ]);

            $project->members()->sync($memberIds->mapWithKeys(
                fn (int $id): array => [$id => ['role' => $id === $manager->id ? 'manager' : 'member']]
            ));

            $this->seedTasksForProject($project, $manager, $users, $tags);
        });
    }

    private function seedTasksForProject(Project $project, User $manager, Collection $users, Collection $tags): void
    {
        $taskRows = [
            ['إعداد موجز إبداعي وقائمة لقطات', 'todo', 'high', 8, ['photo-shoot', 'branding']],
            ['تأكيد المواقع والفريق وجدول الإنتاج', 'in_progress', 'high', 6, ['photo-shoot', 'urgent-client']],
            ['تصوير الصور الرئيسية ومشاهد الفيديو العمودية', 'in_progress', 'high', 18, ['photo-shoot', 'video-production']],
            ['تعديل الصور المختارة وتصحيح الألوان', 'review', 'medium', 14, ['editing', 'branding']],
            ['إنتاج ريلز مع النصوص وصور الغلاف', 'todo', 'medium', 12, ['reels', 'social-media']],
            ['تجهيز جماهير إعلانات ميتا وهيكل الحملة', 'todo', 'medium', 7, ['paid-ads', 'social-media']],
            ['جدولة المنشورات وتسليم تقويم المحتوى الشهري', 'todo', 'medium', 5, ['social-media']],
        ];

        foreach ($taskRows as $position => $row) {
            [$title, $status, $priority, $estimatedHours, $tagSlugs] = $row;
            $completion = match ($status) {
                'done' => 100,
                'review' => 85,
                'in_progress' => 45,
                default => 0,
            };

            if ($project->status === 'completed') {
                $status = 'done';
                $completion = 100;
            }

            $task = Task::query()->create([
                'project_id' => $project->id,
                'created_by' => $manager->id,
                'title' => $title,
                'description' => 'مهمة تسليم ضمن مشروع العميل رقم '.$project->code.'.',
                'status' => $status,
                'priority' => $priority,
                'start_date' => Carbon::parse($project->start_date)->addDays($position)->toDateString(),
                'due_date' => Carbon::parse($project->start_date)->addDays($position + 6)->toDateString(),
                'estimated_hours' => $estimatedHours,
                'actual_hours' => $status === 'todo' ? 0 : round($estimatedHours * ($completion / 100), 2),
                'completion_percentage' => $completion,
                'position' => $position + 1,
                'completed_at' => $status === 'done' ? now()->subDays(fake()->numberBetween(1, 8)) : null,
            ]);

            $assignees = collect([
                $users['samer@pixelwave.local']->id,
                $users['lina@pixelwave.local']->id,
                $users['omar@pixelwave.local']->id,
            ])->shuffle()->take($position % 2 === 0 ? 2 : 1)->values();

            $task->assignees()->sync($assignees->mapWithKeys(
                fn (int $id, int $index): array => [$id => ['is_primary' => $index === 0, 'assigned_at' => now()->subDays(3)]]
            ));

            $task->tags()->sync(collect($tagSlugs)->map(fn (string $slug): int => $tags[$slug]->id));

            TaskComment::query()->create([
                'task_id' => $task->id,
                'user_id' => $manager->id,
                'body' => 'يرجى تسمية الملفات باسم العميل ورقم المشروع وتاريخ التسليم لتسهيل المراجعة والموافقة.',
            ]);

            TaskLog::query()->create([
                'task_id' => $task->id,
                'user_id' => $manager->id,
                'action' => 'created',
                'description' => 'تم إنشاء المهمة بواسطة بيانات العرض العربية.',
                'new_values' => ['status' => $status, 'priority' => $priority],
                'created_at' => now()->subDays(4),
            ]);

            if ($status !== 'todo') {
                TimeEntry::query()->create([
                    'project_id' => $project->id,
                    'task_id' => $task->id,
                    'user_id' => $assignees->first(),
                    'started_at' => now()->subDays(2)->setTime(10, 0),
                    'ended_at' => now()->subDays(2)->setTime(12, 30),
                    'minutes' => 150,
                    'billable' => true,
                    'description' => 'عمل إنتاج وتسليم ضمن المشروع '.$project->code.'.',
                ]);
            }
        }
    }

    private function seedSettings(): void
    {
        collect([
            ['group' => 'company', 'key' => 'demo_company_profile', 'value' => 'بيكسل ويف ستوديو - تصوير فوتوغرافي، إنتاج فيديو، إدارة سوشال ميديا، وإعلانات ممولة.', 'type' => 'string'],
            ['group' => 'company', 'key' => 'demo_default_service_mix', 'value' => 'تصوير، ريلز، إدارة شهرية للسوشال ميديا، إعلانات ميتا، ومحتوى هوية بصرية.', 'type' => 'string'],
            ['group' => 'system', 'key' => 'demo_seed_source', 'value' => self::class, 'type' => 'string'],
        ])->each(fn (array $setting): Setting => Setting::query()->create($setting));
    }

    private function permissionName(string $module, string $action): string
    {
        $modules = [
            'dashboard' => 'لوحة التحكم',
            'users' => 'المستخدمين',
            'roles' => 'الأدوار',
            'customers' => 'العملاء',
            'leads' => 'العملاء المحتملين',
            'deals' => 'الصفقات',
            'projects' => 'المشاريع',
            'tasks' => 'المهام',
            'reports' => 'التقارير',
            'notifications' => 'الإشعارات',
            'audit_logs' => 'سجل التدقيق',
        ];

        $actions = [
            'view' => 'عرض',
            'create' => 'إنشاء',
            'update' => 'تعديل',
            'delete' => 'حذف',
            'convert' => 'تحويل',
            'pipeline' => 'إدارة المسار',
            'assign' => 'إسناد',
            'comment' => 'تعليق',
            'move' => 'نقل',
            'export' => 'تصدير',
        ];

        return ($actions[$action] ?? $action).' '.($modules[$module] ?? $module);
    }
}
