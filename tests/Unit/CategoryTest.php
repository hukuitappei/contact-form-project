<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Contact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_has_many_contacts(): void
    {
        $category = Category::create(['content' => 'テストカテゴリ']);

        Contact::create([
            'category_id' => $category->id,
            'first_name' => '太郎',
            'last_name' => 'テスト',
            'gender' => 1,
            'email' => 'taro@example.com',
            'tel' => '09012345678',
            'address' => '東京都',
            'building' => null,
            'detail' => 'テスト内容1',
        ]);

        Contact::create([
            'category_id' => $category->id,
            'first_name' => '花子',
            'last_name' => 'テスト',
            'gender' => 2,
            'email' => 'hanako@example.com',
            'tel' => '09087654321',
            'address' => '大阪府',
            'building' => null,
            'detail' => 'テスト内容2',
        ]);

        $this->assertCount(2, $category->contacts);
        $this->assertTrue($category->contacts->every(
            fn (Contact $contact) => $contact->category_id === $category->id
        ));
    }
}
