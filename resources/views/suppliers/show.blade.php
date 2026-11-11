@extends('layouts.app')

@section('title', 'Supplier Details')

@section('content')
<div class="content-wrapper">
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1>Supplier Details</h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="#">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('suppliers.index') }}">Suppliers</a></li>
            <li class="breadcrumb-item active">Supplier Details</li>
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
                <label><strong>Supplier Name</strong></label>
                <p>{{ $supplier->name }}</p>
              </div>

              <div class="form-group">
                <label><strong>Phone</strong></label>
                <p>{{ $supplier->phone ?? 'Not Provided' }}</p>
              </div>

              <div class="form-group">
                <label><strong>Email</strong></label>
                <p>{{ $supplier->email ?? 'Not Provided' }}</p>
              </div>

              <div class="form-group">
                <label><strong>Address</strong></label>
                <p>{{ $supplier->address ?? 'Not Provided' }}</p>
              </div>

              <div class="form-group">
                <a href="{{ route('suppliers.index') }}" class="btn btn-secondary">Back to Suppliers</a>
                <a href="{{ route('suppliers.edit', $supplier->id) }}" class="btn btn-primary">Edit Supplier</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>
@endsection
