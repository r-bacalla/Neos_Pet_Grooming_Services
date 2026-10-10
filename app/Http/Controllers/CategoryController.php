<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::query()
            ->withCount('products')
            ->orderBy('name')
            ->get();
        $selectedCategory = $request->filled('category')
            ? Category::query()
                ->with(['products' => fn ($query) => $query->orderBy('name')])
                ->findOrFail($request->integer('category'))
            : null;

        return view('categories.index', compact('categories', 'selectedCategory'));
    }

    public function store(Request $request)
    {
        $request->merge(['name' => trim((string) $request->input('name'))]);
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:categories,name'],
        ]);

        Category::create($validated);

        return redirect()->route('categories.index')->with('success', 'Category created.');
    }

    public function update(Request $request, Category $category)
    {
        $request->merge(['name' => trim((string) $request->input('name'))]);
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'name')->ignore($category->id),
            ],
        ]);
        $name = $validated['name'];

        DB::transaction(function () use ($category, $name) {
            Product::where('category', $category->name)->update(['category' => $name]);
            $category->update(['name' => $name]);
        });

        return redirect()->route('categories.index')->with('success', 'Category updated.');
    }

    public function destroy(Category $category)
    {
        $category->delete();

        return redirect()->route('categories.index')->with('success', 'Category moved to Archive.');
    }
}