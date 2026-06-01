<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    private function getStore()
    {
        return Auth::user()->store;
    }

    private function ensureOwnership(Product $product)
    {
        $store = $this->getStore();
        if (!$store || $product->store_id !== $store->id) {
            abort(403, 'Нет доступа');
        }
    }

    // ======================= CRUD =========================

    public function index()
    {
        $store = $this->getStore();
        $products = $store->products()->with('category', 'supplier')->latest()->paginate(10);
        return view('store.products.index', compact('store', 'products'));
    }

    public function create()
    {
        $rootCategories = Category::root()->get();
        $allCategories = Category::all();
        $suppliers = Supplier::all();
        return view('store.products.create', compact('rootCategories', 'allCategories', 'suppliers'));
    }

    public function store(Request $request)
    {
        $store = $this->getStore();
        $validated = $request->validate([
            'name'             => 'required|string|max:255',
            'description'      => 'nullable|string',
            'price'            => 'required|numeric',
            'supplier_id'      => 'nullable|exists:suppliers,id',
            'supplier_price'   => 'nullable|numeric',
            'weight'           => 'required|integer',
            'category_id'      => 'required|exists:categories,id',
            'image'            => 'nullable|image|max:2048',
            'is_available'     => 'boolean',
            'stock'            => 'nullable|integer|min:0',
            'initial_stock'    => 'nullable|integer|min:0',
        ]);

        $validated['store_id'] = $store->id;
        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        DB::transaction(function () use ($validated) {
            $product = Product::create($validated);
            // Если указан начальный остаток, проводим начисление
            if (!is_null($validated['stock']) && $validated['stock'] > 0) {
                $product->stockMovements()->create([
                    'type'     => 'in',
                    'quantity' => $validated['stock'],
                    'comment'  => 'Начальное поступление',
                ]);
            }
        });

        return redirect()->route('store.products.index')->with('success', 'Товар добавлен');
    }

    public function edit(Product $product)
    {
        $this->ensureOwnership($product);
        $rootCategories = Category::root()->get();
        $allCategories = Category::all();
        $suppliers = Supplier::all();
        return view('store.products.edit', compact('product', 'rootCategories', 'allCategories', 'suppliers'));
    }

    public function update(Request $request, Product $product)
    {
        $this->ensureOwnership($product);
        $validated = $request->validate([
            'name'             => 'required|string|max:255',
            'description'      => 'nullable|string',
            'price'            => 'required|numeric',
            'supplier_id'      => 'nullable|exists:suppliers,id',
            'supplier_price'   => 'nullable|numeric',
            'weight'           => 'required|integer',
            'category_id'      => 'required|exists:categories,id',
            'image'            => 'nullable|image|max:2048',
            'is_available'     => 'boolean',
            'stock'            => 'nullable|integer|min:0',
            'initial_stock'    => 'nullable|integer|min:0',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        DB::transaction(function () use ($product, $validated) {
            $oldStock = $product->stock;
            $product->update($validated);

            // Если вручную изменили stock, создаём корректировку
            if (array_key_exists('stock', $validated) && $validated['stock'] !== $oldStock) {
                $diff = $validated['stock'] - $oldStock;
                if ($diff != 0) {
                    $product->stockMovements()->create([
                        'type'     => $diff > 0 ? 'in' : 'out',
                        'quantity' => abs($diff),
                        'comment'  => 'Корректировка вручную',
                    ]);
                }
            }
        });

        return redirect()->route('store.products.index')->with('success', 'Товар обновлён');
    }

    public function destroy(Product $product)
    {
        $this->ensureOwnership($product);
        $product->delete();
        return back()->with('success', 'Товар удалён');
    }

    // ======================= ИСТОРИЯ =========================

    public function history(Product $product)
    {
        $this->ensureOwnership($product);
        $movements = $product->stockMovements()->latest()->paginate(10);
        return view('store.products.history', compact('product', 'movements'));
    }

    // ======================= ПОСТАВКА =========================

    public function showSupplyForm(Product $product)
    {
        $this->ensureOwnership($product);
        return view('store.products.supply', compact('product'));
    }

    public function supply(Request $request, Product $product)
    {
        $this->ensureOwnership($product);
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
            'comment'  => 'nullable|string|max:255',
        ]);

        $newStock = ($product->stock ?? 0) + $validated['quantity'];
        $product->update(['stock' => $newStock]);

        $product->stockMovements()->create([
            'type'     => 'in',
            'quantity' => $validated['quantity'],
            'comment'  => $validated['comment'] ?: 'Поставка',
        ]);

        return redirect()->route('store.products.index')->with('success', 'Остаток пополнен на ' . $validated['quantity'] . ' шт.');
    }

    // ======================= СПИСАНИЕ =========================

    public function showWriteOffForm(Product $product)
    {
        $this->ensureOwnership($product);
        return view('store.products.writeoff', compact('product'));
    }

    public function writeOff(Request $request, Product $product)
    {
        $this->ensureOwnership($product);
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
            'comment'  => 'nullable|string|max:255',
        ]);

        $currentStock = $product->stock ?? 0;
        if ($validated['quantity'] > $currentStock) {
            return back()->with('error', 'Нельзя списать больше, чем есть на складе (остаток: ' . $currentStock . ' шт.)')->withInput();
        }

        $newStock = $currentStock - $validated['quantity'];
        $product->update(['stock' => $newStock]);

        $product->stockMovements()->create([
            'type'     => 'out',
            'quantity' => $validated['quantity'],
            'comment'  => $validated['comment'] ?: 'Списание',
        ]);

        return redirect()->route('store.products.index')->with('success', 'Списано ' . $validated['quantity'] . ' шт.');
    }
}