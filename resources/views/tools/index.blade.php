@extends('layouts.app')
@section('title', 'Tool Inventory')

@section('content')
<div class="content-wrapper">
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1>Tool Inventory</h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="#">Home</a></li>
            <li class="breadcrumb-item active">Tool Inventory</li>
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
                  <a href="{{ route('tools.create') }}" class="btn btn-default"><i class="fa fa-plus"></i> Add New Tool</a>
                </div>
              </div>

              <table id="toolTable" class="table table-bordered table-hover">
                <thead>
                  <tr>
                    <th>Item Name</th>
                    <th>Category</th>
                    <th>Item Code</th>
                    <th>Quantity</th>
                    <th>Created At</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($tools as $tool)
                    <tr>
                      <td>{{ $tool->item_name }}</td>
                      <td>{{ $tool->category }}</td>
                      <td>{{ $tool->item_code }}</td>
                      <td>{{ $tool->quantity }}</td>
                      <td>{{ $tool->created_at->format('d M Y, h:i A') }}</td>
                      <td>
                        <div class="btn-group">
                          <a href="{{ route('tools.show', $tool->id) }}" class="btn btn-default btn-sm" title="View"><i class="fa fa-eye"></i></a>
                          <a href="{{ route('tools.edit', $tool->id) }}" class="btn btn-default btn-sm" title="Edit"><i class="fa fa-edit"></i></a>
                          <form method="POST" action="{{ route('tools.destroy', $tool->id) }}" onsubmit="return confirm('Are you sure you want to delete this tool?');">
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
      $('#toolTable').DataTable({
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
