@extends('layouts.app')

@section('title', 'Project Details')

@section('content')
<div class="content-wrapper">
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1>Project Details</h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="#">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('projects.index') }}">Projects</a></li>
            <li class="breadcrumb-item active">Project Details</li>
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
              <div class="form-group">
                <label for="name"><strong>Project Name</strong></label>
                <p>{{ $project->name }}</p>
              </div>

              <div class="form-group">
                <label for="description"><strong>Project Location</strong></label>
                <p>{{ $project->description ?? 'No description available' }}</p>
              </div>

              <div class="form-group">
                <a href="{{ route('projects.index') }}" class="btn btn-secondary">Back to Projects</a>
                <a href="{{ route('projects.edit', $project->id) }}" class="btn btn-primary">Edit Project</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>
@endsection
