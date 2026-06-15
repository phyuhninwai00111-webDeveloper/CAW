<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = ['Human Resources Management', 'Cooperate', 'Engineering(Financial Solutions)', 'Professional Services', 'Engineering(Security and Unique Solutions)', 'Marketing and Sales', 'Finance', 'Administration' , 'Logistics' , 'IT System and Security' , 'Payment Solutions' , 'Engineering(Government Solutions)' , 'Software Development' , 'Financial Solution'];
        foreach ($departments as $d) {
            DB::table('departments')->updateOrInsert(['department_name' => $d]);
        }
    }
}
