<?php

namespace Tests\Unit;

use App\Http\Requests\IndexContactRequest;
use App\Http\Requests\StoreContactRequest;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ContactValidationTest extends TestCase
{
    use RefreshDatabase;

    private function validContactData(array $overrides = []): array
    {
        return array_merge([
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => '1',
            'email' => 'taro@example.com',
            'tel' => '01234567890',
            'address' => 'Japan',
            'detail' => 'お問い合わせです',
        ], $overrides);
    }

    private function storeRules(): array
    {
        return (new StoreContactRequest)->rules();
    }

    private function indexRules(): array
    {
        return (new IndexContactRequest)->rules();
    }

    // --- StoreContactRequest ---

    public function test_store_accepts_all_required_fields_with_tags(): void
    {
        $category = Category::factory()->create(['content' => 'テストカテゴリ']);
        $tag = Tag::factory()->create(['name' => 'テストタグ']);
        $data = $this->validContactData([
            'tag_ids' => [$tag->id],
            'category_id' => $category->id,
        ]);
        $validator = Validator::make($data, $this->storeRules());
        $this->assertTrue($validator->passes());
    }

    public function test_store_accepts_valid_data_without_tags(): void
    {
        $category = Category::factory()->create(['content' => 'テストカテゴリ']);
        $tag = Tag::factory()->create(['name' => 'テストタグ']);
        $data = $this->validContactData([
            'category_id' => $category->id,
        ]);
        $validator = Validator::make($data, $this->storeRules());
        $this->assertTrue($validator->passes());
    }

    public function test_store_rejects_missing_required_fields(): void
    {
        $validator = Validator::make([], $this->storeRules());

        $this->assertTrue($validator->fails());

    }

    public function test_store_rejects_tel_shorter_than_ten_digits(): void
    {
        $category = Category::factory()->create();

        $data = $this->validContactData([
            'tel' => '012345678',
            'category_id' => $category->id,
        ]);

        $validator = Validator::make($data, $this->storeRules());

        $this->assertTrue($validator->fails());
    }

    public function test_store_rejects_tel_longer_than_eleven_digits(): void
    {
        $category = Category::factory()->create();

        $data = $this->validContactData([
            'tel' => '012345678910',
            'category_id' => $category->id,
        ]);

        $validator = Validator::make($data, $this->storeRules());

        $this->assertTrue($validator->fails());
    }

    public function test_store_rejects_tel_containing_non_numeric_characters(): void
    {
        $category = Category::factory()->create();

        $data = $this->validContactData([
            'tel' => '0123-456-789',
            'category_id' => $category->id,
        ]);

        $validator = Validator::make($data, $this->storeRules());

        $this->assertTrue($validator->fails());
    }

    // --- IndexContactRequest ---

    public function test_index_accepts_all_filters_together(): void
    {
        $category = Category::factory()->create();

        $data = ['keyword' => 'id', 'gender' => 1, 'category_id' => $category->id, 'date' => '2026-01-01'];
        $validator = Validator::make($data, $this->indexRules());

        $this->assertTrue($validator->passes());
    }

    public function test_index_accepts_empty_filters(): void
    {
        $validator = Validator::make([], $this->indexRules());
        $this->assertTrue($validator->passes());

    }

    public function test_index_rejects_invalid_gender_value(): void
    {
        $validator = Validator::make(['gender' => 9], $this->indexRules());
        $this->assertTrue($validator->fails());
    }

    public function test_index_rejects_nonexistent_category_id(): void
    {
        $validator = Validator::make(['category_id' => 9999], $this->indexRules());
        $this->assertTrue($validator->fails());
    }
}
