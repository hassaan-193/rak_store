@extends('layouts.app')

@section('title', 'Edit Project')

@section('content')
<div class="content-wrapper">
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1>Edit Project</h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="#">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('projects.index') }}">Projects</a></li>
            <li class="breadcrumb-item active">Edit Project</li>
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
              <form action="{{ route('projects.update', $project->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                  <label for="name">Project Name</label>
                  <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $project->name) }}" required>
                </div>

                <div class="form-group">
                  <label for="description">Project Location</label>
                  <textarea class="form-control" id="description" name="description" rows="3">{{ old('description', $project->description) }}</textarea>
                </div>

                <div class="form-group">
                  <button type="submit" class="btn btn-primary">Update Project</button>
                  <a href="{{ route('projects.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>
@endsection
