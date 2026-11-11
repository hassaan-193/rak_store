@extends('layouts.app') 
@section('title', 'Projects')

@section('content')
<div class="content-wrapper">
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1>Projects</h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="#">Home</a></li>
            <li class="breadcrumb-item active">Projects</li>
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
                  <a href="{{ route('projects.create') }}" class="btn btn-default"><i class="fa fa-plus"></i> Create</a>
                </div>
              </div>

              <table id="projectsTable" class="table table-bordered table-hover">
                <thead>
                  <tr>
                    <th>Project ID</th>
                    <th>Project Name</th>
                    <th>Location</th>
                    <th>Created At</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($projects as $project)
                  <tr>
                    <td>{{ $project->id }}</td>
                    <td>{{ $project->name }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($project->description, 50, '...') }}</td>
                    <td>{{ $project->created_at->format('d/M/Y') }}</td>
                    <td>
                      <div class="btn-group">
                        <!-- Edit -->
                        <a href="{{ route('projects.edit', $project->id) }}" class="btn btn-default btn-sm" title="Edit"><i class="fa fa-edit"></i></a>
                        <a href="{{ route('projects.show', $project->id) }}" class="btn btn-default btn-sm" title="Edit"><i class="fa fa-eye"></i></a>

                        <!-- Delete -->
                        <form method="POST" action="{{ route('projects.destroy', $project->id) }}" onsubmit="return confirm('Are you sure you want to delete this project?');">
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
      $('#projectsTable').DataTable({
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
