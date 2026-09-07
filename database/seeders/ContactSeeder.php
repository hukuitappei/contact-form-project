<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ContactSeeder extends Seeder
{
    public function run(): void
    {
        $faker = fake('ja_JP');
        $categoryIds = Category::pluck('id');
        $tagIds = Tag::pluck('id');

        for ($i = 0; $i < 20; $i++) {
            $tel = $faker->numerify(
                $faker->boolean() ? '0##########' : '0#########'
            );

            $contact = Contact::create([
                'category_id' => $categoryIds->random(),
                'first_name' => $faker->firstName(),
                'last_name' => $faker->lastName(),
                'gender' => $faker->numberBetween(1, 3),
                'email' => $faker->safeEmail(),
                'tel' => $tel,
                'address' => $faker->address(),
                'building' => $faker->optional(0.5)->secondaryAddress(),
                'detail' => Str::limit($faker->realText(100), 120, ''),
            ]);

            $contact->tags()->attach(
                $tagIds->random(rand(1, 3))
            );
        }
    }
}
