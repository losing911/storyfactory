<?php

namespace App\Http\Controllers;

use App\Models\LoreEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class AdminLoreController extends Controller
{
    public function index()
    {
        $entries = LoreEntry::latest()->paginate(20);
        return view('admin.lore.index', compact('entries'));
    }

    public function create()
    {
        return view('admin.lore.form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:city,character,faction,location',
            'description' => 'required|string',
            'visual_prompt' => 'nullable|string', // Description for AI Image Gen
            'image' => 'nullable|image|max:2048',
        ]);

        $data = $request->all();
        $data['slug'] = Str::slug($data['title']);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('lore', 'public');
            $data['image_url'] = '/storage/' . $path;
        }

        LoreEntry::create($data);

        return redirect()->route('admin.lore.index')->with('success', 'Lore entry created successfully.');
    }

    public function edit(LoreEntry $lore)
    {
        return view('admin.lore.form', ['entry' => $lore]);
    }

    public function update(Request $request, LoreEntry $lore)
    {
         $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:city,character,faction,location',
            'description' => 'required|string',
            'visual_prompt' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
        ]);

        $data = $request->all();
        $data['slug'] = Str::slug($data['title']);

        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($lore->image_url && file_exists(public_path($lore->image_url))) {
                @unlink(public_path($lore->image_url));
            }
            
            $path = $request->file('image')->store('lore', 'public');
            $data['image_url'] = '/storage/' . $path;
        }

        $lore->update($data);

        return redirect()->route('admin.lore.index')->with('success', 'Lore entry updated successfully.');
    }

    public function destroy(LoreEntry $lore)
    {
        $lore->delete();
        return redirect()->route('admin.lore.index')->with('success', 'Lore entry deleted.');
    }
}
