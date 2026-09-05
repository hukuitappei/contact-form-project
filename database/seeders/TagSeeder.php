<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TagSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $tags = [
            '質問',
            '要望',
            '不具合報告',
            'ご意見',
            'その他',
        ];

        foreach ($tags as $name) {
            DB::table('tags')->insert([
                'name' => $name,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
