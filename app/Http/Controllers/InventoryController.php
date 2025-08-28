<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Inventory;
use App\Jobs\InventoryCreatedJob;
use App\Notifications\StoreInventoryNotification;

class InventoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        // query all inventories from the table 'inventories' using model
        $inventories = Inventory::latest()->get();

        // return to view with $inventories (resources/views/inventories/index.blade.php)
        return view('inventories.index', compact('inventories'));
    }

    public function create()
    {
        $this->authorize('create', Inventory::class);

        return view('inventories.create');
    }

    public function store(Request $request)
    {
        // store in the table 'inventories' using model

        // POPO - Plain Old PHP Object
        $inventory = new Inventory();
        $inventory->name = $request->name;
        $inventory->quantity = $request->quantity;
        $inventory->color = $request->color;
        $inventory->serial_no = $request->serial_no;
        $inventory->user_id = auth()->user()->id;
        $inventory->save();

        InventoryCreatedJob::dispatch($inventory);

        $user = auth()->user();
        $user->notify(new StoreInventoryNotification());

        // return to inventory index
        return redirect('/inventories');
        
    }

    public function show(Inventory $inventory)
    {
        $this->authorize('view', $inventory);

        return view('inventories.show', compact('inventory'));
    }

    public function edit(Inventory $inventory)
    {
        $this->authorize('kemaskini', $inventory);

        return view('inventories.edit', compact('inventory'));
    }

    public function update(Request $request, Inventory $inventory)
    {
        // update using model
        $inventory->name = $request->name;
        $inventory->quantity = $request->quantity;
        $inventory->serial_no = $request->serial_no;
        $inventory->save();

        // return to inventory index
        return redirect('/inventories');
    }

    public function destroy(Inventory $inventory)
    {
        $this->authorize('padam', $inventory);

        // delete using model
        $inventory->delete();

        // return to inventory index
        return redirect('/inventories');
    }
}
