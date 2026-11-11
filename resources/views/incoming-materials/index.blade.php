@extends('layouts.app')

@section('title', 'Incoming Materials | FTS')

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Incoming Materials</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">Incoming Materials</li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- Table for Incoming Materials -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">All Incoming Materials</h3>
                            <div class="card-tools">
                                <a href="{{ route('incoming-materials.create') }}" class="btn btn-primary btn-sm">Create New</a>
                            </div>
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Date / Time</th>
                                        <th>Source Type</th>
                                        <th>Engineer Name</th>
                                        <th>Store Location</th>
                                        <th>Project Type</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($incomingMaterials as $material)
                                    <tr>
                                        <td>{{ $material->id }}</td>
                                        <td>{{ $material->date_time }}</td>
                                        <td>{{ ucfirst($material->source_type) }}</td>
                                        <td>{{ $material->engineer_name }}</td>
                                        <td>{{ $material->store_location }}</td>
                                        <td>{{ ucfirst($material->project_type) }}</td>
                                        <td>
                                            <a href="{{ route('incoming-materials.show', $material->id) }}" class="btn btn-info btn-sm">View</a>
                                            <a href="{{ route('incoming-materials.edit', $material->id) }}" class="btn btn-warning btn-sm">Edit</a>
                                            <form action="{{ route('incoming-materials.destroy', $material->id) }}" method="POST" style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                            </form>
                                        </td>
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
