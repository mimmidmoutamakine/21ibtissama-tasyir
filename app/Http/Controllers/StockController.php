<?php

namespace App\Http\Controllers;

use App\Models\StockItem;
use Illuminate\View\View;

class StockController extends Controller
{
    public function index(): View
    {
        return view('stock.index', [
            'items' => StockItem::with('movements')->get(),
        ]);
    }
}
