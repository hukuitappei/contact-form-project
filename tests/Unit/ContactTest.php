<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_belongs_to_category(): void
    {
        $category = Category::create(['content' => 'テストカテゴリ']);

        $contact = Contact::create([
            'category_id' => $category->id,
            'first_name' => '太郎',
            'last_name' => 'テスト',
            'gender' => 1,
            'email' => 'taro@example.com',
            'tel' => '09012345678',
            'address' => '東京都',
            'building' => null,
            'detail' => 'テスト内容',
        ]);

        $this->assertInstanceOf(Category::class, $contact->category);
        $this->assertSame($category->id, $contact->category->id);
    }

    public function test_contact_can_sync_multiple_tags(): void
    {
        $category = Category::create(['content' => 'テストカテゴリ']);

        $contact = Contact::create([
            'category_id' => $category->id,
            'first_name' => '太郎',
            'last_name' => 'テスト',
            'gender' => 1,
            'email' => 'taro@example.com',
            'tel' => '09012345678',
            'address' => '東京都',
            'building' => null,
            'detail' => 'テスト内容',
        ]);

        $tagA = Tag::create(['name' => '質問']);
        $tagB = Tag::create(['name' => '要望']);
        $tagC = Tag::create(['name' => 'ご意見']);

        $contact->tags()->sync([$tagA->id, $tagB->id]);
        $this->assertCount(2, $contact->tags()->get());

        $contact->tags()->sync([$tagB->id, $tagC->id]);
        $contact->refresh();

        $this->assertCount(2, $contact->tags);
        $this->assertEqualsCanonicalizing(
            [$tagB->id, $tagC->id],
            $contact->tags->pluck('id')->all()
        );
    }
}
