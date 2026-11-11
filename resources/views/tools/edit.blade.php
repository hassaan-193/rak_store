@extends('layouts.app')
@section('title', 'Edit Tool')

@section('content')
<div class="content-wrapper">
  <section class="content-header">
    <div class="container-fluid">
      <h1>Edit Tool</h1>
    </div>
  </section>

  <section class="content">
    <div class="container-fluid">
      <form method="POST" action="{{ route('tools.update', $tool->id) }}">
        @csrf
        @method('PUT')
        <div class="card">
          <div class="card-body">
            <div class="form-group">
              <label for="item_name">Item Name</label>
              <input type="text" name="item_name" id="item_name" class="form-control" value="{{ $tool->item_name }}" required>
            </div>
            <div class="form-group">
              <label for="category">Category</label>
              <input type="text" name="category" id="category" class="form-control" value="{{ $tool->category }}" required>
            </div>
            <div class="form-group">
              <label for="item_code">Item Code</label>
              <input type="text" name="item_code" id="item_code" class="form-control" value="{{ $tool->item_code }}" required>
            </div>
            <div class="form-group">
              <label for="quantity">Quantity</label>
              <input type="number" name="quantity" id="quantity" class="form-control" value="{{ $tool->quantity }}" required>
            </div>

            <button type="submit" class="btn btn-primary">Save Changes</button>
            <a href="{{ route('tools.index') }}" class="btn btn-secondary">Cancel</a>
          </div>
        </div>
      </form>
    </div>
  </section>
</div>
@endsection
