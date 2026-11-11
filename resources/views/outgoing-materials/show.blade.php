@extends('layouts.app')

@section('title', 'Outgoing Material Details')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <h1>Outgoing Material Details</h1>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <!-- Outgoing Material Details -->
                    <div class="card card-primary">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-4">
                                    <div class="form-group">
                                        <label>Date / Time</label>
                                        <input type="text" class="form-control" value="{{ \Carbon\Carbon::parse($outgoingMaterial->date_time)->format('Y-m-d\TH:i') }}" disabled>
                                    </div>
                                </div>

                                <div class="col-4">
                                    <div class="form-group">
                                        <label>Driver</label>
                                        <input type="text" class="form-control" value="{{ $outgoingMaterial->driver->name }}" disabled>
                                    </div>
                                </div>

                                <div class="col-4">
                                    <div class="form-group">
                                        <label>Project Type</label>
                                        <input type="text" class="form-control" value="{{ $outgoingMaterial->project_type }}" disabled>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-4">
                                    <div class="form-group">
                                        <label>Engineer Name</label>
                                        <input type="text" class="form-control" value="{{ $outgoingMaterial->engineer_name }}" disabled>
                                    </div>
                                </div>



                                <div class="col-4">
                                    <div class="form-group">
                                        <label>DN Number</label>
                                        <input type="text" class="form-control" value="{{ $outgoingMaterial->dn_number }}" disabled>
                                    </div>
                                </div>

                                @if($outgoingMaterial->site)
                                <div class="col-4">
                                    <div class="form-group">
                                        <label>Site</label>
                                        <input type="text" class="form-control" value="{{ $outgoingMaterial->site }}" disabled>
                                    </div>
                                </div>
                                @else
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Materials Section -->
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Materials</h3>
                        </div>

                        <div class="card-body">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Item Name</th>
                                        <th>Category</th>
                                        <th>Quantity</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($outgoingMaterial->items as $item)
                                        <tr>
                                            <td>{{ $item->item_name }}</td>
                                            <td>{{ $item->category }}</td>
                                            <td>{{ $item->quantity }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if($outgoingMaterial->status === 'pending')
                            <div class="card-header">
                                <h3 class="card-title">Request Status</h3>
                            </div>
                            <div class="card-body">
                                <p><strong>Status:</strong> {{ ucfirst($outgoingMaterial->status) }}</p>
                            </div>
                        @else
                            <div class="card-header">
                                <h3 class="card-title">Request Status</h3>
                            </div>
                            <div class="card-body">
                                <p><strong>Status:</strong> {{ ucfirst($outgoingMaterial->status) }}</p>
                                <p><strong>Comment:</strong> {{ $outgoingMaterial->approval_comment }}</p>
                            </div>
                        @endif

                    </div>

                    <a href="{{ route('outgoing-materials.index') }}" class="btn btn-default">Back</a>
                </div>
            </div>
        </div>
    </section>
</div>


@endsection
