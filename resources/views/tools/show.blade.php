@extends('layouts.app')
@section('title', 'Tool Details')

@section('content')
<div class="content-wrapper">
  <section class="content-header">
    <div class="container-fluid">
      <h1>Tool Details</h1>
    </div>
  </section>

  <section class="content">
    <div class="container-fluid">
      <div class="card">
        <div class="card-body">
          <h3>Tool Information</h3>
          <p><strong>Item Name:</strong> {{ $tool->item_name }}</p>
          <p><strong>Category:</strong> {{ $tool->category }}</p>
          <p><strong>Item Code:</strong> {{ $tool->item_code }}</p>
          <p><strong>Quantity:</strong> {{ $tool->quantity }}</p>
          <p><strong>Created At:</strong> {{ $tool->created_at->format('d M Y, h:i A') }}</p>

          <a href="{{ route('tools.index') }}" class="btn btn-secondary">Back to Inventory</a>
        </div>
      </div>
    </div>
  </section>
</div>
@endsection
