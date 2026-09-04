<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AttributeController extends Controller
{
    public function index(): View
    {
        return view('admin.attributes.index', [
            'attributes' => Attribute::query()->with('values')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
        ]);

        Attribute::query()->create([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
        ]);

        return back()->with('success', 'Attribute created.');
    }

    public function storeValue(Request $request, Attribute $attribute): RedirectResponse
    {
        $data = $request->validate([
            'value' => ['required', 'string', 'max:80'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        AttributeValue::query()->create([
            'attribute_id' => $attribute->id,
            'value' => $data['value'],
            'slug' => Str::slug($data['value']),
            'sort_order' => $data['sort_order'] ?? 0,
        ]);

        return back()->with('success', 'Attribute value added.');
    }

    public function destroy(Attribute $attribute): RedirectResponse
    {
        $attribute->delete();

        return back()->with('success', 'Attribute deleted.');
    }

    public function destroyValue(AttributeValue $value): RedirectResponse
    {
        $value->delete();

        return back()->with('success', 'Value deleted.');
    }
}
