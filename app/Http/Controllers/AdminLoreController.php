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

        // Process Visual Variations
        $data['visual_variations'] = [
            'combat' => $request->input('variation_combat'),
            'action' => $request->input('variation_action'),
            'dramatic' => $request->input('variation_dramatic'),
            'uniform' => $request->input('variation_uniform'),
        ];

        LoreEntry::create($data);

        return redirect()->route('admin.lore.index')->with('success', 'Lore entry created successfully.');
    }

    public function update(Request $request, LoreEntry $loreEntry)
    {
         $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:city,character,faction,location',
            'description' => 'required|string',
            'visual_prompt' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
        ]);

        $data = $request->all();
        $data['slug'] = \Illuminate\Support\Str::slug($data['title']);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('lore', 'public');
            $data['image_url'] = '/storage/' . $path;
        }

        // Process Visual Variations
        $data['visual_variations'] = [
            'combat' => $request->input('variation_combat'),
            'action' => $request->input('variation_action'),
            'dramatic' => $request->input('variation_dramatic'),
            'uniform' => $request->input('variation_uniform'),
        ];

        $loreEntry->update($data);

        return redirect()->route('admin.lore.index')->with('success', 'Lore entry updated successfully.');
    }

    public function edit(LoreEntry $lore)
    {
        return view('admin.lore.form', ['entry' => $lore]);
    }

    public function destroy(LoreEntry $lore)
    {
        $lore->delete();
        return redirect()->route('admin.lore.index')->with('success', 'Lore entry deleted.');
    }
}
