<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\InventoryImport;
use App\Models\Inventory;

class BulkUploadInventoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function create()
    {
        return view('inventories.bulk-upload.create');
    }

    public function store(Request $request)
    {  
        Excel::import(new InventoryImport, $request->file('file'));

        // return to inventory index
        return redirect('/inventories')->with('success', 'Inventories imported successfully.');
    }
}
