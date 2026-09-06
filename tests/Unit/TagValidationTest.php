<?php

namespace Tests\Unit;

use App\Http\Requests\StoreTagRequest;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Tests\TestCase;

class TagValidationTest extends TestCase
{
    use RefreshDatabase;

    private function storeRules(): array
    {
        return (new StoreTagRequest())->rules();
    }

    private function updateRulesIgnoring(int $tagId): array
    {
        return [
            'name' => ['required', 'string', 'max:50', Rule::unique('tags', 'name')->ignore($tagId)],
        ];
    }

    public function test_name_is_required(): void
    {
        $validator = Validator::make(['name' => ''], $this->storeRules());

        $this->assertTrue($validator->fails());
    }

    public function test_name_of_fifty_characters_is_valid(): void
    {
        $validator = Validator::make(['name' => str_repeat('あ', 50)], $this->storeRules());

        $this->assertTrue($validator->passes());
    }

    public function test_name_exceeding_fifty_characters_is_invalid(): void
    {
        $validator = Validator::make(['name' => str_repeat('あ', 51)], $this->storeRules());

        $this->assertTrue($validator->fails());
    }

    public function test_duplicate_name_is_invalid_on_store(): void
    {
        Tag::factory()->create(['name' => 'sssss']);

        $validator = Validator::make(['name' => 'sssss'], $this->storeRules());

        $this->assertTrue($validator->fails());
    }

    public function test_update_allows_keeping_its_own_current_name(): void
    {
        $tag = Tag::factory()->create(['name' => '質問']);

        $validator = Validator::make(['name' => '質問'], $this->updateRulesIgnoring($tag->id));

        $this->assertTrue($validator->passes());
    }

    public function test_update_rejects_name_already_used_by_another_tag(): void
    {
        $tag = Tag::factory()->create(['name' => '質問']);
        $other = Tag::factory()->create(['name' => '要望']);

        $validator = Validator::make(['name' => '要望'], $this->updateRulesIgnoring($tag->id));

        $this->assertTrue($validator->fails());
    }
}
