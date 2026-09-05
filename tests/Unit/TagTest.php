<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagTest extends TestCase
{
    use RefreshDatabase;

    public function test_tag_belongs_to_many_contacts_via_pivot_table(): void
    {
        $category = Category::create(['content' => 'テストカテゴリ']);
        $tag = Tag::create(['name' => '質問']);

        $contactA = Contact::create([
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

        $contactB = Contact::create([
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

        $tag->contacts()->attach([$contactA->id, $contactB->id]);

        $this->assertCount(2, $tag->contacts);
        $this->assertDatabaseHas('contact_tag', [
            'contact_id' => $contactA->id,
            'tag_id' => $tag->id,
        ]);
        $this->assertDatabaseHas('contact_tag', [
            'contact_id' => $contactB->id,
            'tag_id' => $tag->id,
        ]);
    }
}
