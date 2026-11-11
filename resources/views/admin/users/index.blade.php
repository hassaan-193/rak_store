@extends('layouts.app')

@section('title', 'User Role Management | FTS')

@section('content')

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>User Role Management</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">User Roles</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="col-12">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Manage User Roles</h3>
                    @role('super-admin')
                        <div class="card-tools">
                            <a href="{{ route('user-management.index') }}" class="btn btn-success">Manage Users</a>
                        </div>
                    @endrole
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-bordered table-striped" id="usersTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Roles</th>
                                <th>Update Roles</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                                <tr>
                                    <td>{{ $user->id }}</td>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        @foreach($user->roles as $role)
                                            <span class="badge badge-info">{{ $role->name }}</span>
                                        @endforeach
                                    </td>
                                    <td>
                                        <form method="POST" action="{{ route('users.update', $user) }}">
                                            @csrf @method('PUT')
                                            <div class="row">
                                                @foreach($roles as $role)
                                                    <div class="col-6">
                                                        <div class="form-check">
                                                            <input type="checkbox" name="roles[]" value="{{ $role->name }}"
                                                                   class="form-check-input"
                                                                   {{ $user->roles->contains('name', $role->name) ? 'checked' : '' }}>
                                                            <label class="form-check-label">{{ $role->name }}</label>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                            <button type="submit" class="btn btn-sm btn-primary mt-2">Update</button>
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
            $('#usersTable').DataTable({
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
