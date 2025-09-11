@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">{{ __('Audits Index') }}</div>

                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Auditable Model</th>
                                <th>Event</th>
                                <th>Old Values</th>
                                <th>New Values</th>
                                <th>Created At</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($audits as $audit)
                                <tr>
                                    <td>{{ $audit->id }}</td>
                                    <td>{{ $audit->auditable_type }}</td>
                                    <td>{{ $audit->event }}</td>
                                    <td><pre>{{ print_r($audit->old_values, true) }}</pre></td>
                                    <td><pre>{{ print_r($audit->new_values, true) }}</pre></td>
                                    <td>{{ $audit->created_at }}</td>
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
