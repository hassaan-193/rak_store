<!-- resources/views/return-materials/show.blade.php -->

@extends('layouts.app')

@section('title', 'Return Material Details')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <h1>Return Material Details</h1>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <!-- Return Material Details -->
                    <div class="card card-primary">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-4">
                                    <div class="form-group">
                                        <label>Date / Time</label>
                                        <input type="text" class="form-control" value="{{ \Carbon\Carbon::parse($returnMaterial->date_time)->format('Y-m-d\TH:i') }}" disabled>
                                    </div>
                                </div>

                                <div class="col-4">
                                    <div class="form-group">
                                        <label>Driver</label>
                                        <input type="text" class="form-control" value="{{ $returnMaterial->driver->name }}" disabled>
                                    </div>
                                </div>

                                <div class="col-4">
                                    <div class="form-group">
                                        <label>Return Reason</label>
                                        <input type="text" class="form-control" value="{{ $returnMaterial->return_reason }}" disabled>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Materials Section -->
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Returned Materials</h3>
                        </div>

                        <div class="card-body">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Item Name</th>
                                        <th>Category</th>
                                        <th>Unit</th>
                                        <th>Returned Quantity</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($returnMaterial->returnMaterialItems as $item)
                                    <tr>
                                        <td>{{ $item->item_name }}</td>
                                        <td>{{ $item->category }}</td>
                                        <td>{{ $item->unit }}</td>
                                        <td>{{ $item->quantity }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <a href="{{ route('return-materials.index') }}" class="btn btn-default">Back</a>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
