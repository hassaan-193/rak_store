@extends('layouts.app')

@section('title', 'Manage Permissions | FTS')

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Manage Permissions</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">Permissions</li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
        <form method="POST" action="{{ route('permissions.store') }}">
            @csrf
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="card card-primary">
                            <div class="card-header">
                                <h3 class="card-title">Add New Permission</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-8 col-sm-12 mb-3">
                                        <div class="form-group">
                                            <label for="name">Permission Name</label>
                                            <input type="text" name="name" class="form-control" placeholder="Permission Name" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-sm-12 mb-3 d-flex align-items-end">
                                        <div class="form-group w-100">
                                            <button type="submit" class="btn btn-primary w-100">Add Permission</button>
                                        </div>
                                    </div>
                                </div>
                            </div><!-- /.card-body -->
                        </div><!-- /.card -->
                    </div>
                </div> <!-- /.row -->
            </div> <!-- /.container-fluid -->
        </form>

        <!-- Success Message -->
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <!-- Permissions List -->
        <div class="col-12">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Permissions List</h3>
                </div>
                <div class="card-body table-responsive">
                    <!-- Table for Permissions -->
                    <table class="table table-bordered table-striped" id="permissionsTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Permission Name</th>
                                <th>update</th>
                                <th>delete</th>

                            </tr>
                        </thead>
                        <tbody>
                            @foreach($permissions as $permission)
                                <tr>
                                    <td>{{ $permission->id }}</td>
                                    <td>{{ $permission->name }}</td>
                                    <td>
                                        <form action="{{ route('permissions.update', $permission) }}" method="POST" class="d-flex justify-content-between">
                                            @csrf @method('PUT')
                                            <input type="text" name="name" value="{{ $permission->name }}" class="form-control me-2" style="max-width: 300px;">
                                            <button class="btn btn-sm btn-success me-2">Update</button>
                                        </form>
                                    </td>
                                    <td>
                                        <form method="POST" action="{{ route('permissions.destroy', $permission) }}" class="d-inline">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div><!-- /.card-body -->
            </div><!-- /.card -->
        </div>

    </section>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->

@endsection

@push('styles')
    <!-- DataTables -->
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
            // Initialize DataTable with configuration options
            $('#permissionsTable').DataTable({
                paging: true,          // Enable pagination
                searching: true,       // Enable search box
                ordering: true,        // Enable column sorting
                info: true,            // Display information (e.g., showing 1 to 10 of 100 entries)
                lengthChange: true,    // Allow the user to change the number of entries per page
                responsive: true,      // Make the table responsive (adjust layout on smaller screens)
                columnDefs: [{
                    targets: [0],       // Disabling sorting for the ID column (if necessary)
                    orderable: false
                }]
            });
        });
    </script>
@endpush
