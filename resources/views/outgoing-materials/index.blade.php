@extends('layouts.app')

@section('title', 'Outgoing Materials Requests | FTS')

@section('content')

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Outgoing Materials</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">Requests</li>
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
                        <div class="card-header">
                            <h3 class="card-title">Pending Outgoing Material Requests</h3>
                            <div class="card-tools">
                                <a href="{{ route('outgoing-materials.pending') }}" class="btn btn-sm btn-info mr-2">For Review</a>
                                <a href="{{ route('outgoing-materials.processed') }}" class="btn btn-sm btn-secondary mr-2">History</a>
                                @can('create_outgoing_request')
                                    <a href="{{ route('outgoing-materials.create') }}" class="btn btn-sm btn-primary">Create Request</a>
                                @endcan
                            </div>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Date / Time</th>
                                        <th>Requested By</th>
                                        <th>Driver</th>
                                        <th>Project</th>
                                        <th>Site</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($outgoingMaterials as $outgoingMaterial)
                                    <tr>
                                        <td>{{ $outgoingMaterial->id }}</td>
                                        <td>{{ $outgoingMaterial->date_time }}</td>
                                        <td>
                                            <span class="badge badge-info">{{ $outgoingMaterial->creator->name ?? 'N/A' }}</span>
                                        </td>
                                        <td>{{ $outgoingMaterial->driver->name }}</td>
                                        <td>{{ $outgoingMaterial->project ? $outgoingMaterial->project->name : 'N/A' }}</td>
                                        <td>{{ $outgoingMaterial->site ?? 'N/A' }}</td>
                                        <td>
                                            <span class="badge badge-warning">{{ ucfirst($outgoingMaterial->status) }}</span>
                                        </td>
                                        <td>
                                            <a href="{{ route('outgoing-materials.show', $outgoingMaterial->id) }}" class="btn btn-sm btn-info">View</a>
                                            @can('create_outgoing_request')
                                                @if($outgoingMaterial->created_by === Auth::id())
                                                    <a href="{{ route('outgoing-materials.edit', $outgoingMaterial->id) }}" class="btn btn-sm btn-warning">Edit</a>
                                                @endif
                                            @endcan
                                            <form action="{{ route('outgoing-materials.destroy', $outgoingMaterial->id) }}" method="POST" style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted">No pending requests found. <a href="{{ route('outgoing-materials.create') }}">Create a new request</a></td>
                                    </tr>
                                    @endforelse
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
