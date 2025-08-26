<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Inventory;

class InventoryController extends Controller
{
    public function index()
    {
        // query all inventories from the table 'inventories' using model
        $inventories = Inventory::all();

        // return to view with $inventories (resources/views/inventories/index.blade.php)
        return view('inventories.index', compact('inventories'));
    }
}
