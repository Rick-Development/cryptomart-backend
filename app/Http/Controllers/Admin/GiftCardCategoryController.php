<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GiftCardTradeCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class GiftCardCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $pageTitle = 'Gift Card Categories';
        $categories = GiftCardTradeCategory::latest()->paginate(10);
        return view('admin.gift-card-trade.categories.index', compact('pageTitle', 'categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:gift_card_trade_categories,name',
            'icon' => 'nullable|string', // Assuming image upload handling elsewhere or URL
            'description' => 'nullable|string',
        ]);

        $category = new GiftCardTradeCategory();
        $category->name = $request->name;
        $category->slug = Str::slug($request->name);
        $category->icon = $request->icon;
        $category->description = $request->description;
        $category->status = 1;
        $category->save();

        $notify[] = ['success', 'Category created successfully'];
        return back()->withNotify($notify);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:gift_card_trade_categories,name,' . $id,
            'icon' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $category = GiftCardTradeCategory::findOrFail($id);
        $category->name = $request->name;
        $category->slug = Str::slug($request->name);
        if ($request->has('icon')) {
             $category->icon = $request->icon;
        }
        $category->description = $request->description;
        $category->save();

        $notify[] = ['success', 'Category updated successfully'];
        return back()->withNotify($notify);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $category = GiftCardTradeCategory::findOrFail($id);
        $category->delete();

        $notify[] = ['success', 'Category deleted successfully'];
        return back()->withNotify($notify);
    }
    
    public function status($id)
    {
        return GiftCardTradeCategory::changeStatus($id);
    }
}
