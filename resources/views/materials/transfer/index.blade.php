{{-- resources/views/materials/transfer/index.blade.php --}}

@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Project Items Available for Transfer</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table">
        <thead>
            <tr>
                <th>Item Name</th>
                <th>Category</th>
                <th>Brand</th>
                <th>Unit</th>
                <th>Available Quantity</th>
                <th>Transfer Quantity</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($projectItems as $item)
                <tr>
                    <td>{{ $item->item_name }}</td>
                    <td>{{ $item->category }}</td>
                    <td>{{ $item->brand }}</td>
                    <td>{{ $item->unit }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>
                        <form action="{{ route('materials.transfer.store', $item->id) }}" method="POST" class="d-flex">
                            @csrf
                            <input type="number" name="quantity" class="form-control form-control-sm me-2"
                                   min="1" max="{{ $item->quantity }}" required>
                    </td>
                    <td>
                            <button type="submit" class="btn btn-sm btn-primary">Transfer</button>
                        </form>
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
  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

  <!-- DataTables -->
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