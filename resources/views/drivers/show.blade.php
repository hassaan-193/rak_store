@extends('layouts.app')

@section('title', 'Driver Details')

@section('content')
<div class="content-wrapper">
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1>Driver Details</h1>
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
                <label><strong>Driver Name</strong></label>
                <p>{{ $driver->name }}</p>
              </div>

              <div class="form-group">
                <label><strong>Phone</strong></label>
                <p>{{ $driver->phone ?? 'Not Provided' }}</p>
              </div>

              <div class="form-group">
                <label><strong>Email</strong></label>
                <p>{{ $driver->email ?? 'Not Provided' }}</p>
              </div>

              <div class="form-group">
                <label><strong>Address</strong></label>
                <p>{{ $driver->address ?? 'Not Provided' }}</p>
              </div>

              <div class="form-group">
                <a href="{{ route('drivers.index') }}" class="btn btn-secondary">Back to Drivers</a>
                <a href="{{ route('drivers.edit', $driver->id) }}" class="btn btn-primary">Edit Driver</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>
@endsection
