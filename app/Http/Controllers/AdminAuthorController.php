<?php

namespace App\Http\Controllers;

use App\Models\Author;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminAuthorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $authors = Author::withCount('stories')->latest()->paginate(10);
        return view('admin.authors.index', compact('authors'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.authors.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'nullable|string|max:255',
            'bio' => 'nullable|string',
            'avatar' => 'nullable|string',
            'is_ai' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        
        // Handle potential duplicate slug
        if(Author::where('slug', $validated['slug'])->exists()) {
             $validated['slug'] .= '-' . rand(100,999);
        }

        Author::create($validated);

        return redirect()->route('admin.authors.index')->with('success', 'Yazar başarıyla oluşturuldu.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Not used in admin
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Author $author)
    {
         return view('admin.authors.edit', compact('author'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Author $author)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'nullable|string|max:255',
            'bio' => 'nullable|string',
            'avatar' => 'nullable|string',
            'is_ai' => 'boolean',
        ]);

        if($author->name !== $validated['name']) {
             $newSlug = Str::slug($validated['name']);
             if(Author::where('slug', $newSlug)->where('id', '!=', $author->id)->exists()) {
                 $newSlug .= '-' . rand(100,999);
             }
             $validated['slug'] = $newSlug;
        }
        
        // Handle checkbox issue (unchecked = false)
        $validated['is_ai'] = $request->has('is_ai');

        $author->update($validated);

        return redirect()->route('admin.authors.index')->with('success', 'Yazar güncellendi.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Author $author)
    {
        $author->delete();
        return redirect()->route('admin.authors.index')->with('success', 'Yazar silindi. Bağlı hikayeler yazarsız kaldı.');
    }
}
