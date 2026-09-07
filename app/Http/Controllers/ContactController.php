<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExportContactRequest;
use App\Http\Requests\StoreContactRequest;
use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;

class ContactController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->only('export');
    }

    public function index()
    {
        $categories = Category::all();
        $tags = Tag::all();

        return view('contact.index', [
            'categories' => $categories,
            'tags' => $tags,
        ]);
    }

    public function confirm(StoreContactRequest $request)
    {
        $validated = $request->validated();
        $category = Category::find($validated['category_id']);
        $tags = Tag::wherein('id', $validated['tag_ids'] ?? [])->get();

        return view('contact.confirm', [
            'validated' => $validated,
            'category' => $category,
            'tags' => $tags,
        ]);
    }

    public function store(StoreContactRequest $request)
    {
        $validated = $request->validated();
        $contact = Contact::create($validated);
        $contact->tags()->attach(($validated['tag_ids'] ?? []));

        return redirect('/thanks');
    }

    public function thanks()
    {
        return view('contact.thanks');
    }

    public function export(ExportContactRequest $request)
    {
        $validated = $request->validated();

        $query = Contact::with('category');
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
        $contacts = $query->latest()->get();
        $genderLabels = [1 => '男性', 2 => '女性', 3 => 'その他'];

        return response()->streamDownload(function () use ($contacts, $genderLabels) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['ID', '氏名', '性別', 'メール', '電話', '住所', '建物', 'カテゴリ', '内容', '作成日時']);

            foreach ($contacts as $contact) {
                fputcsv($handle, [
                    $contact->id,
                    $contact->last_name.' '.$contact->first_name,
                    $genderLabels[$contact->gender] ?? '',
                    $contact->email,
                    $contact->tel,
                    $contact->address,
                    $contact->building,
                    $contact->category->content ?? '',
                    $contact->detail,
                    $contact->created_at,
                ]);
            }

            fclose($handle);
        }, 'contacts.csv', ['Content-Type' => 'text/csv']);
    }
}
