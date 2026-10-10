<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\User;

it('filters products by category and manages category names', function () {
    $manager = User::factory()->create(['role' => 'manager']);
    $category = Category::create(['name' => 'Care']);
    $matching = Product::create([
        'name' => 'Coat Shampoo',
        'sku' => 'CATEGORY-SHAMPOO',
        'category' => $category->name,
        'cost' => 10,
        'price' => 20,
        'stock' => 0,
    ]);
    Product::create([
        'name' => 'Travel Brush',
        'sku' => 'CATEGORY-BRUSH',
        'category' => 'Accessories',
        'cost' => 10,
        'price' => 20,
        'stock' => 0,
    ]);

    $this->actingAs($manager)
        ->get(route('categories.index'))
        ->assertOk()
        ->assertSee('href="' . route('categories.index', ['category' => $category->id]) . '#category-products"', false)
        ->assertDontSee('id="category-name-' . $category->id . '"', false)
        ->assertDontSee('value="DELETE"', false)
        ->assertSee('Care');

    $this->actingAs($manager)
        ->get(route('categories.index', ['category' => $category->id]))
        ->assertOk()
        ->assertSee('id="category-products"', false)
        ->assertSee('Coat Shampoo')
        ->assertDontSee('Travel Brush');

    $this->actingAs($manager)
        ->get(route('products.index', ['category' => 'Care']))
        ->assertOk()
        ->assertSee('Add Products')
        ->assertSee('Delete')
        ->assertDontSee('Transfer Stock')
        ->assertDontSee('Move from Inventory')
        ->assertSee('Category')
        ->assertSee("onchange=\"if (!this.value) this.form.querySelector('[name=search]').value = ''; this.form.submit()\"", false)
        ->assertDontSee('value="__uncategorized__"', false)
        ->assertSee('Care')
        ->assertSee($matching->name)
        ->assertDontSee('Travel Brush');

    $this->actingAs($manager)
        ->get(route('products.index') . '?category=')
        ->assertOk()
        ->assertSee($matching->name)
        ->assertSee('Travel Brush')
        ->assertSee('value="" selected', false);

    $this->actingAs($manager)
        ->put(route('categories.update', $category), ['name' => 'Bath'])
        ->assertRedirect(route('categories.index'));

    expect($matching->fresh()->category)->toBe('Bath');

    $this->actingAs($manager)
        ->delete(route('categories.destroy', $category->fresh()))
        ->assertRedirect(route('categories.index'));

    expect($matching->fresh()->category)->toBe('Bath')
        ->and(Category::onlyTrashed()->find($category->id))->not->toBeNull();
});

it('does not show inventory-only stock in POS until it is allocated', function () {
    $manager = User::factory()->create(['role' => 'manager']);
    $product = Product::create([
        'name' => 'Inventory Only Shampoo',
        'sku' => 'INVENTORY-ONLY-SHAMPOO',
        'cost' => 10,
        'price' => 20,
        'stock' => 0,
        'available_stock' => 0,
        'low_stock_level' => 1,
        'active' => true,
    ]);

    $this->actingAs($manager)->post(route('inventory.stock-in', $product), [
        'quantity' => 4,
        'batch_reference' => 'POS-VISIBILITY-LOT',
    ])->assertRedirect();

    $this->get(route('pos.index'))->assertDontSee($product->name);

    $this->post(route('inventory.allocate', $product), ['quantity' => 2])
        ->assertRedirect(route('products.index'));

    $this->get(route('pos.index'))->assertSee($product->name);
});

it('offers a compact add-products dialog with eligible inventory stock', function () {
    $manager = User::factory()->create(['role' => 'manager']);
    $product = Product::create([
        'name' => 'Transferable Shampoo',
        'sku' => 'TRANSFERABLE-SHAMPOO',
        'cost' => 10,
        'price' => 20,
        'stock' => 5,
        'available_stock' => 0,
        'low_stock_level' => 1,
    ]);

    $this->actingAs($manager)
        ->get(route('products.index'))
        ->assertOk()
        ->assertSee('Add Products')
        ->assertSee('role="dialog"', false)
        ->assertSee('Choose an inventory item and quantity', false)
        ->assertSee('type="text"', false)
        ->assertSee('role="combobox"', false)
        ->assertSee('role="listbox"', false)
        ->assertDontSee('@focus="inventoryProductDropdownOpen = true"', false)
        ->assertSee('Search inventory products...')
        ->assertSee($product->name)
        ->assertSee('x-text="product.stock"', false)
        ->assertSee('Add to Products');
});

it('archives products from the Products page and returns to the list', function () {
    $manager = User::factory()->create(['role' => 'manager']);
    $product = Product::create([
        'name' => 'Disposable Test Product',
        'sku' => 'DISPOSABLE-TEST-PRODUCT',
        'cost' => 10,
        'price' => 20,
        'stock' => 0,
        'available_stock' => 0,
    ]);

    $this->actingAs($manager)
        ->delete(route('products.destroy', $product))
        ->assertRedirect(route('products.index'));

    expect(Product::onlyTrashed()->find($product->id))->not->toBeNull();
});