@extends('layouts.app')
@section('title', 'Incoming Materials by Project')

@section('content')
  <div class="content-wrapper">
    <!-- Page Header -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Incoming Materials Inventory</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">Incoming Materials Inventory</li>
            </ol>
          </div>
        </div>
      </div>
    </section>

    <!-- Main Content -->
    <section class="content">
      <div class="container-fluid">

        <!-- Projects / General Toggle -->
        <div class="mb-3">
          <a href="{{ route('incoming-materials.filter-by-project') }}"
            class="btn btn-{{ request('type') === 'general' ? 'outline-primary' : 'primary' }}">
            Projects
          </a>
          <a href="{{ route('incoming-materials.filter-by-project', ['type' => 'general']) }}"
            class="btn btn-{{ request('type') === 'general' ? 'primary' : 'outline-secondary' }}">
            General
          </a>
        </div>

        <!-- Project Dropdown -->
        @if(request('type') !== 'general')
          <form method="GET" action="{{ route('incoming-materials.filter-by-project') }}" class="mb-3">
            <input type="hidden" name="type" value="project">
            <link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet" />
            <div class="form-group">
              <label for="project_id">Select Project</label>
              <select name="project_id" id="project_id"
                      class="form-control select2"
                      onchange="this.form.submit()">
                <option value="">-- All Projects --</option>
                @foreach($projects as $project)
                  <option value="{{ $project->id }}"
                          {{ request('project_id') == $project->id ? 'selected' : '' }}>
                    {{ $project->name }}
                  </option>
                @endforeach
              </select>
            </div>
          </form>
        @endif

        <!-- Materials Table -->
        <div class="card">
          <div class="card-body p-0">
            <table id="incomingMaterialsTable" class="table table-bordered table-hover mb-0">
              <thead class="thead-light">
                <tr>
                  <th>Item #</th>
                  <th>Item Name</th>
                  <th>Category</th>
                  <th>Brand</th>
                  <th>Unit</th>
                  <th>Quantity</th>
                </tr>
              </thead>
              <tbody>
                @forelse($incomingMaterials as $material)
                  @if($material->items->isNotEmpty())
                    @foreach($material->items as $item)
                      <tr>
                        <td>{{ $item->id }}</td>
                        <td>{{ $item->item_name }}</td>
                        <td>{{ $item->category }}</td>
                        <td>{{ $item->brand }}</td>
                        <td>{{ $item->unit }}</td>
                        <td>{{ $item->quantity }}</td>
                      </tr>
                    @endforeach
                  @else
                    <tr>
                      <td colspan="5" class="text-muted text-center">No items</td>
                    </tr>
                  @endif
                @empty
                  <tr>
                    <td colspan="10" class="text-center py-3">
                      No materials found for selected {{ request('type') === 'general' ? 'general' : 'project' }}.
                    </td>
                  </tr>
                @endforelse
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
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
  <script>
    $(document).ready(function () {
      $('.select2').select2({
        placeholder: "Select an option",
        allowClear: true,
        width: '100%'
      });

      $('#incomingMaterialsTable').DataTable({
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
