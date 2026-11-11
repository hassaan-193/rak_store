@extends('layouts.app')

@section('title', 'Edit Driver')

@section('content')
<div class="content-wrapper">
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1>Edit Driver</h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="#">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('drivers.index') }}">Drivers</a></li>
            <li class="breadcrumb-item active">Edit Driver</li>
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
              <form method="POST" action="{{ route('drivers.update', $driver->id) }}">
                @csrf
                @method('PUT')

                <div class="form-group">
                  <label for="name"><strong>Name</strong></label>
                  <input type="text" name="name" id="name" value="{{ old('name', $driver->name) }}" class="form-control @error('name') is-invalid @enderror" required>
                  @error('name')
                  <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <div class="form-group">
                  <label for="phone"><strong>Phone</strong></label>
                  <input type="text" name="phone" id="phone" value="{{ old('phone', $driver->phone) }}" class="form-control @error('phone') is-invalid @enderror">
                  @error('phone')
                  <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <div class="form-group">
                  <label for="email"><strong>Email</strong></label>
                  <input type="email" name="email" id="email" value="{{ old('email', $driver->email) }}" class="form-control @error('email') is-invalid @enderror">
                  @error('email')
                  <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <div class="form-group">
                  <label for="address"><strong>Address</strong></label>
                  <textarea name="address" id="address" rows="4" class="form-control @error('address') is-invalid @enderror">{{ old('address', $driver->address) }}</textarea>
                  @error('address')
                  <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <div class="form-group">
                  <button type="submit" class="btn btn-primary">Update Driver</button>
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
