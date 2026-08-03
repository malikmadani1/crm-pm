<?php

use Database\Seeders\PhotographyAgencySeeder;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('demo:seed-photography-agency', function () {
    $this->call('db:seed', [
        '--class' => PhotographyAgencySeeder::class,
        '--force' => true,
    ]);

    $this->info('تم توليد بيانات شركة التصوير والتسويق الرقمي.');
})->purpose('توليد بيانات عرض عربية لشركة تصوير وتسويق رقمي');

Artisan::command('demo:clear-photography-agency', function () {
    PhotographyAgencySeeder::clear();

    $this->info('تم حذف بيانات شركة التصوير والتسويق الرقمي.');
})->purpose('حذف بيانات العرض العربية لشركة التصوير والتسويق الرقمي');
