<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $categoryId = $request->input('category_id');

        $query = Item::with('category')->latest();

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        $items = $query->paginate(10)->withQueryString();
        $categories = Category::all();

        return view('master.items', compact('items', 'categories', 'search', 'categoryId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'sku' => ['required', 'string', 'max:100', 'unique:items,sku'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'unit' => ['required', 'string', 'max:50'],
            'initial_stock' => ['required', 'integer', 'min:0'],
        ]);

        Item::create($validated);

        return redirect()->route('master.items.index')
            ->with('success', 'Barang baru berhasil ditambahkan.');
    }

    public function update(Request $request, Item $item)
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'sku' => ['required', 'string', 'max:100', Rule::unique('items', 'sku')->ignore($item->id)],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'unit' => ['required', 'string', 'max:50'],
            'initial_stock' => ['required', 'integer', 'min:0'],
        ]);

        $item->update($validated);

        return redirect()->route('master.items.index')
            ->with('success', 'Data barang berhasil diperbarui.');
    }

    public function destroy(Item $item)
    {
        // Prevent deletion if active stock exists
        if ($item->stocks()->where('qty', '>', 0)->exists()) {
            return redirect()->route('master.items.index')
                ->with('error', 'Gagal menghapus. Barang masih memiliki stok aktif di lokasi bin.');
        }

        $item->delete();

        return redirect()->route('master.items.index')
            ->with('success', 'Barang berhasil dihapus.');
    }
}
