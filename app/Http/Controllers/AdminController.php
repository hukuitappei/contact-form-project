<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexContactRequest;
use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(IndexContactRequest $request)
    {
        $validated = $request->validated();

        $query = Contact::with(['category', 'tags']);
        if ($validated['keyword'] ?? null) {
            $keyword = $validated['keyword'];
            $query->where(function ($q) use ($keyword) {
                $q->where('first_name', 'like', "%{$keyword}%")
                    ->orWhere('last_name', 'like', "%{$keyword}%")
                    ->orWhere('email', 'like', "%{$keyword}%");
            });
        }

        if (($validated['gender'] ?? null) && $validated['gender'] != 0) {
            $query->where('gender', $validated['gender']);
        }

        if ($validated['category_id'] ?? null) {
            $query->where('category_id', $validated['category_id']);
        }

        if ($validated['date'] ?? null) {
            $query->whereDate('created_at', $validated['date']);
        }

        $contacts = $query->paginate(7);
        $categories = Category::all();
        $tags = Tag::all();

        return view('admin.index', [
            'contacts' => $contacts,
            'categories' => $categories,
            'tags' => $tags,
        ]);
    }

    public function show(Contact $contact)
    {
        $contact->load(['category', 'tags']);

        return view('admin.show', compact('contact'));
    }

    public function destroy(Contact $contact)
    {
        $contact->delete();

        return redirect('/admin');
    }
}
