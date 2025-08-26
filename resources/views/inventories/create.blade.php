@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Inventory Create') }}</div>

                <div class="card-body">
                    <form method="POST" action="">
                        @csrf

                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>

                        <div class="mb-3">
                            <label for="quantity" class="form-label">Quantity</label>
                            <input type="number" class="form-control" id="quantity" name="quantity" required>
                        </div>

                        <div class="mb-3">
                            <label for="serial_no" class="form-label">Serial No</label>
                            <input type="text" class="form-control" id="serial_no" name="serial_no" required>
                        </div>

                        <button type="submit" class="btn btn-primary">Create Inventory</button>
                    </form> 
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
