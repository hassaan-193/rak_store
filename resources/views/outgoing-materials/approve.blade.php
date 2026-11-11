@extends('layouts.app')

@section('title', 'Review Outgoing Material Request')

@section('content')

<div class="content-wrapper">
    <section class="content-header">
        <h1>Review Outgoing Material Request</h1>
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
                            </div>
                        </div>
                    </div>

                    @if ($outgoingMaterial->status === 'pending')

                        <!-- Approval Form -->
                        <form method="POST" action="{{ route('outgoing-materials.approve', $outgoingMaterial->id) }}">
                            @csrf
                            <div class="card card-primary">
                                <div class="card-header">
                                    <h3 class="card-title">Material Request Items</h3>
                                </div>
                                <div class="card-body">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Item Name</th>
                                                <th>Category</th>
                                                <th>Unit</th>
                                                <th>Requested Qty</th>
                                                <th>Approve Qty</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($outgoingMaterial->items as $item)
                                                <tr>
                                                    <td>{{ $item->item_name }}</td>
                                                    <td>{{ $item->category }}</td>
                                                    <td>{{ $item->unit }}</td>
                                                    <td>{{ $item->quantity }}</td>
                                                    <td>
                                                        <input type="number" class="form-control" name="materials[{{ $item->id }}][quantity]" value="{{ $item->quantity }}" min="0" required>
                                                        @error('materials.' . $item->id . '.quantity')
                                                            <span class="text-danger">{{ $message }}</span>
                                                        @enderror
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="card card-success">
                                <div class="card-header">
                                    <h3 class="card-title">Approve Request</h3>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label>Comment (optional)</label>
                                        <textarea name="approval_comment" class="form-control" rows="3"></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-success">Approve</button>
                                </div>
                            </div>
                        </form> <!-- ✅ End of Approve form -->

                        <!-- Rejection Form -->
                        <div class="card card-danger">
                            <div class="card-header">
                                <h3 class="card-title">Reject Request</h3>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="{{ route('outgoing-materials.reject', $outgoingMaterial->id) }}">
                                    @csrf
                                    <div class="form-group">
                                        <label>Reject Comment (required)</label>
                                        <textarea name="approval_comment" class="form-control" rows="3" required></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-danger">Reject</button>
                                </form>
                            </div>
                        </div>

                    @else
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
                        <!-- Status Display -->
                        <div class="card card-info">
                            <div class="card-header">
                                <h3 class="card-title">Request Status</h3>
                            </div>
                            <div class="card-body">
                                <p><strong>Status:</strong> {{ ucfirst($outgoingMaterial->status) }}</p>
                                <p><strong>Comment:</strong> {{ $outgoingMaterial->approval_comment }}</p>
                            </div>
                        </div>
                    @endif

                    <a href="{{ route('outgoing-materials.index') }}" class="btn btn-default">Back</a>

                </div>
            </div>
        </div>
    </section>
</div>

@endsection

