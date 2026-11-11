@extends('layouts.app')

@section('title', 'Add Driver')

@section('content')
<div class="content-wrapper">
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1>Add Driver</h1>
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
              <form action="{{ route('drivers.store') }}" method="POST">
                @csrf

                <div class="form-group">
                  <label for="name">Driver Name</label>
                  <input type="text" name="name" id="name" class="form-control" required>
                </div>

                <div class="form-group">
                  <label for="phone">Phone</label>
                  <input type="text" name="phone" id="phone" class="form-control">
                </div>

                <div class="form-group">
                  <label for="email">Email</label>
                  <input type="email" name="email" id="email" class="form-control">
                </div>

                <div class="form-group">
                  <label for="address">Address</label>
                  <input type="text" name="address" id="address" class="form-control">
                </div>

                <div class="form-group">
                  <button type="submit" class="btn btn-primary">Save Driver</button>
                  <a href="{{ route('drivers.index') }}" class="btn btn-secondary">Cancel</a>
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
