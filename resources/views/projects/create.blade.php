@extends('layouts.app')

@section('title', 'Create Project')

@section('content')
<div class="content-wrapper">
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1>Create Project</h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="#">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('projects.index') }}">Projects</a></li>
            <li class="breadcrumb-item active">Create Project</li>
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
              <form action="{{ route('projects.store') }}" method="POST">
                @csrf
                <div class="form-group">
                  <label for="name">Project Name</label>
                  <input type="text" class="form-control" id="name" name="name" placeholder="Enter project name" required>
                </div>

                <div class="form-group">
                  <label for="description">Project Location</label>
                  <textarea class="form-control" id="description" name="description" rows="3" placeholder="Enter project description (optional)"></textarea>
                </div>

                <div class="form-group">
                  <button type="submit" class="btn btn-primary">Create Project</button>
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
