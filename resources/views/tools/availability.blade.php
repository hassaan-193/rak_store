@extends('layouts.app')
@section('title', 'Add Tools')

@section('content')
<div class="container">
    <h2>Availabe Tools & Assignments</h2>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Item Name</th>
                <th>Category</th>
                <th>Item Code</th>
                <th>Total Quantity</th>
                <th>Available Quantity</th>
                <th>Assigned Quantity</th>
                <th>Assignments</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tools as $tool)
            <tr>
                <td>{{ $tool['item_name'] }}</td>
                <td>{{ $tool['category'] }}</td>
                <td>{{ $tool['item_code'] }}</td>
                <td>{{ $tool['total_quantity'] }}</td>
                <td>{{ $tool['available_quantity'] }}</td>
                <td>{{ $tool['assigned_quantity'] }}</td>
                <td>
                    <ul>
                        @foreach($tool['assignments'] as $assignment)
                        <li>
                            <strong>Project:</strong> {{ $assignment->project_name }} |
                            <strong>Worker:</strong> {{ $assignment->worker_name }} |
                            <strong>Qty:</strong> {{ $assignment->quantity_issued }}
                        </li>
                        @endforeach
                    </ul>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
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
      $('.table').DataTable({
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
