<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Short;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShortController extends Controller
{
    public function index(): View
    {
        return view('admin.shorts.index', [
            'shorts' => Short::query()->with('product')->orderBy('sort_order')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.shorts.form', [
            'short' => new Short(),
            'products' => Product::query()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Short::query()->create($this->validated($request));

        return redirect()->route('admin.shorts.index')->with('success', 'Short created.');
    }

    public function edit(Short $short): View
    {
        return view('admin.shorts.form', [
            'short' => $short,
            'products' => Product::query()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Short $short): RedirectResponse
    {
        $short->update($this->validated($request));

        return redirect()->route('admin.shorts.index')->with('success', 'Short updated.');
    }

    public function destroy(Short $short): RedirectResponse
    {
        $short->delete();

        return redirect()->route('admin.shorts.index')->with('success', 'Short deleted.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'video_url' => ['required', 'string', 'max:500'],
            'thumbnail' => ['nullable', 'string', 'max:500'],
            'product_id' => ['nullable', 'exists:products,id'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['sort_order'] = $data['sort_order'] ?? 0;

        return $data;
    }
}

