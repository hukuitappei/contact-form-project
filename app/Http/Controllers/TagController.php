<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTagRequest;
use App\Http\Requests\UpdateTagRequest;
use App\Models\Tag;

class TagController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function store(StoreTagRequest $request)
    {
        $validated = $request->validated();

        Tag::create($validated);

        return redirect('/admin');
    }

    public function edit(Tag $tag)
    {
        return view('admin.tags.edit', compact('tag'));
    }

    public function update(UpdateTagRequest $request, Tag $tag)
    {
        $validated = $request->validated();

        $tag->update($validated);

        return redirect('/admin');
    }

    public function destroy(Tag $tag)
    {
        $tag->delete();

        return redirect('/admin');
    }
}
