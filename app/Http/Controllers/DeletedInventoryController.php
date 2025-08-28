<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Inventory;

class DeletedInventoryController extends Controller
{
    public function index()
    {
        // query trashed inventory
        $inventories = Inventory::onlyTrashed()->get();

        return view('inventories.deleted.index', compact('inventories'));
    }

    public function restore($inventory)
    {
        $inventory = Inventory::onlyTrashed()->where('id', $inventory)->firstOrFail();

        $inventory->restore();

        return redirect()->route('inventories.deleted.index')->with('success', 'Inventory restored successfully.');
    }

    public function forceDelete($inventory)
    {
        $inventory = Inventory::onlyTrashed()->where('id', $inventory)->firstOrFail();

        $inventory->forceDelete();

        return redirect()->route('inventories.deleted.index')->with('success', 'Inventory permanently deleted successfully.');
    }
}
