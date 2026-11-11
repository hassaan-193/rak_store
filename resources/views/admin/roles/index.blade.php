@extends('layouts.app')

@section('title', 'Manage Roles | FTS')

@section('content')

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Manage Roles</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">Roles</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <!-- Add New Role -->
        <form method="POST" action="{{ route('roles.store') }}">
            @csrf
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="card card-primary">
                            <div class="card-header">
                                <h3 class="card-title">Add New Role</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="form-group">
                                            <label for="name">Role Name</label>
                                            <input type="text" name="name" class="form-control" placeholder="Role Name" required>
                                        </div>
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <label>Assign Permissions</label>
                                        <div class="row">
                                            @foreach($permissions as $permission)
                                                <div class="col-md-3">
                                                    <div class="form-check">
                                                        <input type="checkbox" name="permissions[]" value="{{ $permission->name }}" class="form-check-input">
                                                        <label class="form-check-label">{{ $permission->name }}</label>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="col-md-12 mt-3">
                                        <button type="submit" class="btn btn-primary">Create Role</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <!-- Roles List -->
        <div class="col-12">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Roles List</h3>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-bordered table-striped" id="rolesTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Role Name</th>
                                <th>Rename</th>
                                <th>Update Permissions</th>
                                <th>Delete</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($roles as $role)
                                <tr>
                                    <td>{{ $role->id }}</td>
                                    <td>{{ $role->name }}</td>

                                    <td>
                                        <form method="POST" action="{{ route('roles.rename', $role) }}" class="d-flex">
                                            @csrf @method('PUT')
                                            <input type="text" name="name" value="{{ $role->name }}" class="form-control form-control-sm me-2" style="max-width: 200px;">
                                            <button class="btn btn-warning btn-sm">Rename</button>
                                        </form>
                                    </td>
                                    <td>
                                        <form method="POST" action="{{ route('roles.update', $role) }}">
                                            @csrf @method('PUT')
                                            <div class="row">
                                                @foreach($permissions as $permission)
                                                    <div class="col-6">
                                                        <div class="form-check">
                                                            <input type="checkbox" name="permissions[]" value="{{ $permission->name }}"
                                                                class="form-check-input"
                                                                {{ $role->permissions->contains('name', $permission->name) ? 'checked' : '' }}>
                                                            <label class="form-check-label">{{ $permission->name }}</label>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                            <button type="submit" class="btn btn-success btn-sm mt-2">Update</button>
                                        </form>
                                    </td>
                                    <td>
                                        <form method="POST" action="{{ route('roles.destroy', $role) }}">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
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
</div>

@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap4.min.css">
@endpush

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap4.min.js"></script>

    <script>
        $(document).ready(function () {
            $('#rolesTable').DataTable({
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
