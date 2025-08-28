@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Inventories Index') }}</div>

                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Quantity</th>
                                <th>Serial No</th>
                                <th>User</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($inventories as $inventory)
                                <tr>
                                    <td>{{ $inventory->id }}</td>
                                    <td>{{ $inventory->name }}</td>
                                    <td>{{ $inventory->quantity }}</td>  
                                    <td>{{ $inventory->serial_no }}</td>
                                    <td>{{ $inventory->user->name }} - {{ $inventory->user->email }}</td>
                                    <td>
                                        <a 
                                            href="{{ route('inventories.deleted.restore', $inventory) }}" 
                                            class="btn btn-success btn-sm"
                                            onclick="confirm('Are you sure you want to restore this inventory?') || event.preventDefault();">
                                            Restore
                                        </a>   
                                        <a 
                                            href="{{ route('inventories.deleted.force-delete', $inventory) }}" 
                                            class="btn btn-danger btn-xl-sm"
                                            onclick="confirm('Are you sure you want to permanently delete this inventory?') || event.preventDefault();">
                                            Force Delete
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
