@extends('layouts.app')

@section('title', 'Return Materials | FTS')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Return Materials</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">Return Materials</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Return Materials List</h3>
                            <div class="card-tools">
                                <a href="{{ route('return-materials.create') }}" class="btn btn-success">Create New Return</a>
                            </div>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Date</th>
                                        <th>Driver</th>
                                        <th>Project</th>
                                        <th>Return Reason</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($returnMaterials as $returnMaterial)
                                    <tr>
                                        <td>{{ $returnMaterial->id }}</td>
                                        <td>{{ \Carbon\Carbon::parse($returnMaterial->date_time)->format('d-m-Y') }}
                                        </td>
                                        <td>{{ $returnMaterial->driver->name }}</td>
                                        <td>{{ $returnMaterial->project ? $returnMaterial->project->name : 'General' }}</td>
                                        <td>{{ $returnMaterial->return_reason }}</td>
                                        <td>
                                            <a href="{{ route('return-materials.show', $returnMaterial->id) }}" class="btn btn-warning btn-sm">View</a>
                                            <form action="{{ route('return-materials.destroy', $returnMaterial->id) }}" method="POST" style="display: inline-block;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this?')">Delete</button>
                                            </form>
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
    </section>
</div>
@endsection

@push('styles')
  <link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap4.min.css">
@endpush

@push('scripts')
  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

  <!-- DataTables -->
  <script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap4.min.js"></script>

  <script>
    $(document).ready(function () {
      $('.table-bordered').DataTable({
        paging: true,
        searching: true,
        ordering: true,
        info: true,
        lengthChange: true,
        responsive: true
      });
    });
  </script>
@endpush
