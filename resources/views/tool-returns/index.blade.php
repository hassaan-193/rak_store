@extends('layouts.app')
@section('title', 'Tool Returns')

@section('content')
<div class="content-wrapper">
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1>Tool Returns</h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="#">Home</a></li>
            <li class="breadcrumb-item active">Tool Returns</li>
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
                  <a href="{{ route('tool-returns.export', ['export_type' => 'csv']) }}" class="btn btn-success">Export to CSV</a>
                  <button type="button" class="btn btn-default" onclick="location.reload();"><i class="fa fa-sync"></i> Reload</button>
                  <a href="{{ route('tool-returns.create') }}" class="btn btn-default"><i class="fa fa-plus"></i> Return Tool</a>
                </div>
              </div>

              <table id="toolReturnTable" class="table table-bordered table-hover">
                <thead>
                  <tr>
                    <th>Date / Time</th>
                    <th>Item</th>
                    <th>Category</th>
                    <th>Code</th>
                    <th>Worker</th>
                    <th>Project</th>
                    <th>Qty Returned</th>
                    <th>Return Condition</th>
                    <th>Return Date</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($toolReturns as $return)
                    <tr>
                      <td>{{ \Carbon\Carbon::parse($return->toolAssignment->date_time)->format('d/M/Y h:i A') }}</td>
                      <td>{{ $return->toolAssignment->item_name }}</td>
                      <td>{{ $return->toolAssignment->category }}</td>
                      <td>{{ $return->toolAssignment->item_code }}</td>
                      <td>{{ $return->toolAssignment->worker_name }}</td>
                      <td>{{ $return->toolAssignment->project_name }}</td>
                      <td>{{ $return->quantity_returned }}</td>
                      <td>{{ $return->return_condition }}</td>
                      <td>{{ \Carbon\Carbon::parse($return->return_date)->format('d/M/Y') }}</td>
                      <td>
                        <div class="btn-group">
                          <a href="{{ route('tool-returns.show', $return->id) }}" class="btn btn-default btn-sm" title="View"><i class="fa fa-eye"></i></a>
                          <!-- <a href="{{ route('tool-returns.edit', $return->id) }}" class="btn btn-default btn-sm" title="Edit"><i class="fa fa-edit"></i></a> -->
                          <form method="POST" action="{{ route('tool-returns.destroy', $return->id) }}" onsubmit="return confirm('Are you sure you want to delete this return record?');">
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
    $('#toolReturnTable').DataTable({
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
