<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Models\StockMovement;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::all();
        return view('store.suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        return view('store.suppliers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'description' => 'nullable|string',
        ]);
        Supplier::create($validated);
        return redirect()->route('store.suppliers.index')->with('success', 'Поставщик добавлен');
    }

    public function show(Supplier $supplier)
    {
        $products = $supplier->products()->with('category.parent')->get();

        // Сгруппируем по категориям (родитель → подкатегория)
        $grouped = $products->groupBy(function ($product) {
            $cat = $product->category;
            $parentName = $cat && $cat->parent ? $cat->parent->name : 'Без категории';
            return $parentName;
        });

        // Расчёт продаж и суммы к оплате
        $totalSoldQuantity = 0;
        $totalDebt = 0;

        foreach ($products as $product) {
            // Все движения типа 'out' с привязкой к заказам (продажи)
            $salesMovements = $product->stockMovements()
                ->where('type', 'out')
                ->whereNotNull('order_id')
                ->get();

            $product->sold_quantity = $salesMovements->sum('quantity');
            $product->debt = $product->sold_quantity * ($product->supplier_price ?? 0);

            $totalSoldQuantity += $product->sold_quantity;
            $totalDebt += $product->debt;
        }

        return view('store.suppliers.show', compact(
            'supplier',
            'grouped',
            'products',
            'totalSoldQuantity',
            'totalDebt'
        ));
    }

    public function edit(Supplier $supplier)
    {
        return view('store.suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'description' => 'nullable|string',
        ]);
        $supplier->update($validated);
        return redirect()->route('store.suppliers.index')->with('success', 'Поставщик обновлён');
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();
        return back()->with('success', 'Поставщик удалён');
    }
}