@extends('layouts.app')
@section('title', 'Add Tools')

@section('content')
<div class="content-wrapper">
  <section class="content-header">
    <div class="container-fluid">
      <h1>Add Tools</h1>
    </div>
  </section>

  <section class="content">
    <form method="POST" action="{{ route('tools.store') }}">
      @csrf
      <div class="container-fluid">
        <div class="row">
          <div class="col-12">
            <div class="card card-primary">
              <div class="card-body">
                <div class="row">
                  <div class="col-4">
                    <div class="form-group">
                      <label for="date_time">Date / Time</label>
                      <input type="datetime-local" name="date_time" class="form-control" value="{{ old('date_time') }}" required>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Tools Section -->
          <div class="col-12">
            <div class="card card-primary">
              <div class="card-header">
                <h3 class="card-title">Add Tools</h3>
                <div class="card-tools">
                  <button type="button" class="btn btn-sm btn-success" id="addRow"><i class="fas fa-plus"></i> Add Row</button>
                  <button type="button" class="btn btn-sm btn-danger" id="removeRow"><i class="fas fa-times"></i> Remove Row</button>
                </div>
              </div>

              <div class="card-body" id="toolsWrapper">
                <div class="row tool-row">
                  <div class="col-3">
                    <div class="form-group">
                      <label>Item Name</label>
                      <input type="text" name="tools[0][item_name]" class="form-control" required>
                    </div>
                  </div>

                  <div class="col-3">
                    <div class="form-group">
                      <label>Category</label>
                      <input type="text" name="tools[0][category]" class="form-control" required>
                    </div>
                  </div>

                  <div class="col-3">
                    <div class="form-group">
                      <label>Item Code</label>
                      <input type="text" name="tools[0][item_code]" class="form-control" required>
                    </div>
                  </div>

                  <div class="col-3">
                    <div class="form-group">
                      <label>Quantity</label>
                      <input type="number" name="tools[0][quantity]" class="form-control" required>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-12">
            <button type="submit" class="btn btn-primary">Save</button>
            <a href="{{ route('tools.index') }}" class="btn btn-secondary">Cancel</a>
          </div>
        </div>
      </div>
    </form>
  </section>
</div>
@endsection

@push('scripts')
<script>
  let rowIndex = 1;
  document.getElementById('addRow').addEventListener('click', function() {
    const wrapper = document.getElementById('toolsWrapper');
    const row = document.querySelector('.tool-row').cloneNode(true);
    row.querySelectorAll('input').forEach(input => {
      let name = input.getAttribute('name');
      name = name.replace(/\[\d+\]/, `[${rowIndex}]`);
      input.setAttribute('name', name);
      input.value = '';
    });
    wrapper.appendChild(row);
    rowIndex++;
  });

  document.getElementById('removeRow').addEventListener('click', function() {
    const rows = document.querySelectorAll('.tool-row');
    if (rows.length > 1) {
      rows[rows.length - 1].remove();
      rowIndex--;
    }
  });
</script>
@endpush
