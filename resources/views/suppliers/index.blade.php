@extends('layouts.app')

@section('title', 'Suppliers')

@section('content')
<div class="content-wrapper">
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1>Suppliers</h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="#">Home</a></li>
            <li class="breadcrumb-item active">Suppliers</li>
          </ol>
        </div>
      </div>
    </div>
  </section>

  <section class="content">
    <div class="container-fluid">
      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-body">
              <div class="mb-3">
                <div class="btn-group">
                  <button type="button" class="btn btn-default" onclick="location.reload();"><i class="fa fa-sync"></i> Reload</button>
                  <a href="{{ route('suppliers.create') }}" class="btn btn-default"><i class="fa fa-plus"></i> Create</a>
                </div>
              </div>

              <table id="suppliersTable" class="table table-bordered table-hover">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Address</th>
                    <th>Created At</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($suppliers as $supplier)
                  <tr>
                    <td>{{ $supplier->id }}</td>
                    <td>{{ $supplier->name }}</td>
                    <td>{{ $supplier->phone }}</td>
                    <td>{{ $supplier->email }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($supplier->address, 50, '...') }}</td>
                    <td>{{ $supplier->created_at->format('d/M/Y') }}</td>
                    <td>
                      <div class="btn-group">
                        <a href="{{ route('suppliers.edit', $supplier->id) }}" class="btn btn-default btn-sm" title="Edit"><i class="fa fa-edit"></i></a>
                        <a href="{{ route('suppliers.show', $supplier->id) }}" class="btn btn-default btn-sm" title="View"><i class="fa fa-eye"></i></a>
                        <form method="POST" action="{{ route('suppliers.destroy', $supplier->id) }}" onsubmit="return confirm('Are you sure you want to delete this supplier?');" style="display:inline-block;">
                          @csrf
                          @method('DELETE')
                          <button type="submit" class="btn btn-default btn-sm" title="Delete"><i class="fa fa-trash"></i></button>
                        </form>
                      </div>
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
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap4.min.js"></script>

  <script>
    $(document).ready(function () {
      $('#suppliersTable').DataTable({
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
