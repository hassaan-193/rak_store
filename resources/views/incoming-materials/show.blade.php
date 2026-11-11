@extends('layouts.app')

@section('title', 'Incoming Material Details | FTS')

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Incoming Material Details</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('incoming-materials.index') }}">Home</a></li>
                        <li class="breadcrumb-item active">Incoming Material Details</li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Material Information</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Date / Time:</strong> {{ $incomingMaterial->date_time }}</p>
                                    <p><strong>Source Type:</strong> {{ ucfirst($incomingMaterial->source_type) }}</p>
                                    <p><strong>Engineer Name:</strong> {{ $incomingMaterial->engineer_name }}</p>
                                    <p><strong>Store Location:</strong> {{ $incomingMaterial->store_location }}</p>
                                    <p><strong>DN Number:</strong> {{ $incomingMaterial->dn_number }}</p>
                                    <p><strong>Project Type:</strong> {{ ucfirst($incomingMaterial->project_type) }}</p>

                                    @if($incomingMaterial->project_type == 'project')
                                        <p><strong>Project:</strong> {{ $incomingMaterial->project->name }}</p>
                                    @endif

                                    @if($incomingMaterial->source_type == 'driver')
                                        <p><strong>Driver:</strong> {{ $incomingMaterial->driver->name }}</p>
                                    @elseif($incomingMaterial->source_type == 'supplier')
                                        <p><strong>Supplier:</strong> {{ $incomingMaterial->supplier->name }}</p>
                                    @endif
                                </div>
                            </div>

                            <h4 class="mt-4">Materials List</h4>
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Item Name</th>
                                        <th>Category</th>
                                        <th>Brand</th>
                                        <th>Unit</th>
                                        <th>Quantity</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($incomingMaterial->items as $item)
                                    <tr>
                                        <td>{{ $item->item_name }}</td>
                                        <td>{{ $item->category }}</td>
                                        <td>{{ $item->brand }}</td>
                                        <td>{{ $item->unit }}</td>
                                        <td>{{ $item->quantity }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <!-- /.card-body -->
                    </div>
                    <!-- /.card -->
                </div>
            </div>
        </div>
    </section>
</div>
<!-- /.content-wrapper -->

@endsection
