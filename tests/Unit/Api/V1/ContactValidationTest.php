<?php

namespace Tests\Unit\Api\V1;

use App\Http\Requests\Api\V1\IndexContactRequest;
use App\Http\Requests\Api\V1\StoreContactRequest;
use App\Http\Requests\Api\V1\UpdateContactRequest;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ContactValidationTest extends TestCase
{
    use RefreshDatabase;

    private function storeRules(): array
    {
        return (new StoreContactRequest)->rules();
    }

    private function updateRules(): array
    {
        return (new UpdateContactRequest)->rules();
    }

    private function indexRules(): array
    {
        return (new IndexContactRequest)->rules();
    }

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

    public function test_store_accepts_valid_data(): void
    {
        $category = Category::factory()->create();
        $data = $this->validContactData(['category_id' => $category->id]);
        $validator = Validator::make($data, $this->storeRules());
        $this->assertTrue($validator->passes());
    }

    public function test_store_rejects_invalid_gender(): void
    {
        $category = Category::factory()->create();
        $data = $this->validContactData([
            'category_id' => $category->id,
            'gender' => 9,
        ]);
        $validator = Validator::make($data, $this->storeRules());
        $this->assertTrue($validator->fails());
    }

    public function test_store_rejects_invalid_tel_format(): void
    {
        $category = Category::factory()->create();
        $data = $this->validContactData([
            'tel' => '0123-456-789',
            'category_id' => $category->id,
        ]);
        $validator = Validator::make($data, $this->storeRules());
        $this->assertTrue($validator->fails());
    }

    public function test_store_rejects_nonexistent_category_id(): void
    {
        $data = $this->validContactData(['category_id' => 9999]);
        $validator = Validator::make($data, $this->storeRules());
        $this->assertTrue($validator->fails());
    }

    public function test_store_rejects_nonexistent_tag_id(): void
    {
        $category = Category::factory()->create();
        $data = $this->validContactData([
            'category_id' => $category->id,
            'tag_ids' => [9999],
        ]);
        $validator = Validator::make($data, $this->storeRules(), (new StoreContactRequest)->messages());

        $this->assertTrue($validator->fails());
        $this->assertSame('選択されたタグが存在しません', $validator->errors()->first('tag_ids.0'));
    }

    public function test_store_accepts_valid_tag_ids(): void
    {
        $category = Category::factory()->create();
        $tag = Tag::factory()->create();
        $data = $this->validContactData(['category_id' => $category->id, 'tag_ids' => [$tag->id]]);
        $validator = Validator::make($data, $this->storeRules());
        $this->assertTrue($validator->passes());
    }

    public function test_store_rejects_missing_required_fields(): void
    {
        $validator = Validator::make([], $this->storeRules());
        $this->assertTrue($validator->fails());
    }

    public function test_update_accepts_valid_data(): void
    {
        $category = Category::factory()->create();
        $data = $this->validContactData(['category_id' => $category->id]);
        $validator = Validator::make($data, $this->updateRules());
        $this->assertTrue($validator->passes());
    }

    public function test_update_rejects_invalid_gender(): void
    {
        $validator = Validator::make(['gender' => 9], $this->updateRules());
        $this->assertTrue($validator->fails());
    }

    public function test_update_rejects_invalid_tel_format(): void
    {
        $category = Category::factory()->create();
        $data = $this->validContactData([
            'tel' => '0123-456-789',
            'category_id' => $category->id,
        ]);
        $validator = Validator::make($data, $this->updateRules());
        $this->assertTrue($validator->fails());
    }

    public function test_update_rejects_nonexistent_category_id(): void
    {
        $validator = Validator::make(['category_id' => 9999], $this->updateRules());
        $this->assertTrue($validator->fails());
    }

    public function test_update_rejects_nonexistent_tag_id(): void
    {
        $category = Category::factory()->create();
        $data = $this->validContactData([
            'category_id' => $category->id,
            'tag_ids' => [9999],
        ]);
        $validator = Validator::make($data, $this->updateRules(), (new UpdateContactRequest)->messages());

        $this->assertTrue($validator->fails());
        $this->assertSame('選択されたタグが存在しません', $validator->errors()->first('tag_ids.0'));
    }

    public function test_index_rejects_invalid_gender(): void
    {
        $validator = Validator::make(['gender' => 9], $this->indexRules());
        $this->assertTrue($validator->fails());
    }

    public function test_index_rejects_nonexistent_category_id(): void
    {
        $validator = Validator::make(['category_id' => 9999], $this->indexRules());
        $this->assertTrue($validator->fails());
    }

    public function test_index_accepts_empty_filters(): void
    {
        $validator = Validator::make([], $this->indexRules());
        $this->assertTrue($validator->passes());
    }

    public function test_index_accepts_valid_keyword(): void
    {
        $validator = Validator::make(['keyword' => '山田'], $this->indexRules());
        $this->assertTrue($validator->passes());
    }

    public function test_index_rejects_per_page_out_of_range(): void
    {
        $validator = Validator::make(['per_page' => 0], $this->indexRules());
        $this->assertTrue($validator->fails());
    }

    public function test_index_rejects_invalid_date(): void
    {
        $data = ['date' => '20243201601'];
        $validator = Validator::make($data, $this->indexRules());
        $this->assertTrue($validator->fails());
    }

    public function test_index_accepts_full_valid_filters(): void
    {
        $category = Category::factory()->create();
        $data = ['keyword' => 'id', 'gender' => 1, 'category_id' => $category->id, 'per_page' => 1, 'date' => '2026-01-01'];
        $validator = Validator::make($data, $this->indexRules());
        $this->assertTrue($validator->passes());
    }
}
