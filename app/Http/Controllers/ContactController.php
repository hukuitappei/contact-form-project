<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Tag;

class ContactController extends Controller
{
    public function index()
    {
        $categories = Category::all();
        $tags = Tag::all();

        return view('contact.index', [
            'categories' => $categories,
            'tags' => $tags,
        ]);
    }
}
