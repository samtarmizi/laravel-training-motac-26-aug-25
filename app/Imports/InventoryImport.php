<?php

namespace App\Imports;

use App\Models\Inventory;
use Maatwebsite\Excel\Concerns\ToModel;

class InventoryImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new Inventory([
            'name' => $row[0],
            'quantity' => $row[1],
            'color' => $row[2],
            'serial_no' => $row[3],
            'user_id' => auth()->user()->id,
        ]);
    }
}
