@extends('layouts.app')

@section('title', 'Edit Supplier')

@section('content')
<div class="content-wrapper">
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1>Edit Supplier</h1>
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
              <form action="{{ route('suppliers.update', $supplier->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                  <label for="name">Supplier Name</label>
                  <input type="text" name="name" id="name" class="form-control" value="{{ $supplier->name }}" required>
                </div>

                <div class="form-group">
                  <label for="phone">Phone</label>
                  <input type="text" name="phone" id="phone" class="form-control" value="{{ $supplier->phone }}">
                </div>

                <div class="form-group">
                  <label for="email">Email</label>
                  <input type="email" name="email" id="email" class="form-control" value="{{ $supplier->email }}">
                </div>

                <div class="form-group">
                  <label for="address">Address</label>
                  <input type="text" name="address" id="address" class="form-control" value="{{ $supplier->address }}">
                </div>

                <div class="form-group">
                  <button type="submit" class="btn btn-primary">Update Supplier</button>
                  <a href="{{ route('suppliers.index') }}" class="btn btn-secondary">Cancel</a>
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
