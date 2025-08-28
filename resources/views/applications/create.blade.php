@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Applications Create') }}</div>

                <div class="card-body">
                    <form method="POST" action="">
                        @csrf
                        <div class="mb-3">
                            <label for="name" class="form-label">User Name</label>
                            <select class="form-select" id="user" name="user" required>
                                <option value="" disabled selected>---- SELECT ------</option>

                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="name" class="form-label">Inventory</label>
                            <select class="form-select" id="inventory" name="name" required>
                                
                            </select>
                        </div>
                    
                        <div class="mb-3">
                            <label for="serial_no" class="form-label">Remark</label>
                            <input type="text" class="form-control" id="serial_no" name="serial_no" required>
                        </div>

                        <button type="submit" class="btn btn-primary">Create Applications</button>
                    </form> 
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const userSelect = document.getElementById('user');
        const inventorySelect = document.getElementById('inventory');

        userSelect.addEventListener('change', function () {
            const userId = this.value;

            fetch(`/inventories-by-user/${userId}`)
                .then(response => response.json())
                .then(data => {
                    inventorySelect.innerHTML = '';

                    data.forEach(inventory => {
                        const option = document.createElement('option');
                        option.value = inventory.id;
                        option.textContent = inventory.name;
                        inventorySelect.appendChild(option);
                    });
                })
                .catch(error => console.error('Error fetching inventories:', error));
        });
    });
</script>
