@extends('layouts.app')
@section('title', 'Tool Assignment & Return Log')

@section('content')
<div class="content-wrapper">
  <section class="content-header">
    <div class="container-fluid">
      <h1>Tool Assignment & Return Log</h1>
    </div>
  </section>

  <section class="content">
    <div class="container-fluid">
      <div class="card">
        <div class="card-body">
          <table id="logTable" class="table table-bordered table-hover">
            <thead>
              <tr>
                <th>Item Name</th>
                <th>Category</th>
                <th>Item Code</th>
                <th>Quantity Issued</th>
                <th>Purpose / Task</th>
                <th>Assignment Date</th>
                <th>Condition Before Issue</th>
                <th>Return Date</th>
                <th>Condition After Return</th>
              </tr>
            </thead>
            <tbody>
              @foreach($toolReturns as $return)
                <tr>
                  <td>{{ $return->toolAssignment->item_name }}</td>
                  <td>{{ $return->toolAssignment->category }}</td>
                  <td>{{ $return->toolAssignment->item_code }}</td>
                  <td>{{ $return->toolAssignment->quantity_issued }}</td>
                  <td>{{ $return->toolAssignment->purpose }}</td>
                  <td>{{ \Carbon\Carbon::parse($return->toolAssignment->assignment_date)->format('d M Y') }}</td>
                  <td>{{ $return->toolAssignment->condition_before_issue }}</td>
                  <td>{{ \Carbon\Carbon::parse($return->return_date)->format('d M Y') }}</td>
                  <td>{{ $return->return_condition }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
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
    $('#logTable').DataTable({
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
