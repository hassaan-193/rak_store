@extends('layouts.app')
@section('title', 'Assign Tool')

@section('content')
<div class="content-wrapper">
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1>Assign Tool</h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ route('tool-assignments.index') }}">Tool Assignments</a></li>
            <li class="breadcrumb-item active">Assign Tool</li>
          </ol>
        </div>
      </div>
    </div>
  </section>

  <section class="content">
    <div class="container-fluid">
      <form action="{{ route('tool-assignments.store') }}" method="POST">
        @csrf
        <div class="card">
          <div class="card-body row">

            <div class="form-group col-md-6">
              <label for="date_time">Date / Time</label>
              <input type="datetime-local" class="form-control" name="date_time" required>
            </div>

            <div class="form-group col-md-6">
              <label for="tool_id">Tool</label>
              <select name="tool_id" id="toolSelect" class="form-control" required>
                <option value="">Select Tool</option>
                @foreach($tools as $tool)
                  <option 
                    value="{{ $tool->id }}"
                    data-item-name="{{ $tool->item_name }}"
                    data-category="{{ $tool->category }}"
                    data-item-code="{{ $tool->item_code }}"
                    data-quantity="{{ $tool->quantity }}"
                  >
                    {{ $tool->item_name }} ({{ $tool->item_code }})
                  </option>
                @endforeach
              </select>
            </div>

            <div class="form-group col-md-6">
              <label for="item_name">Item Name</label>
              <input type="text" class="form-control" name="item_name" id="itemName" readonly required>
            </div>

            <div class="form-group col-md-6">
              <label for="category">Category</label>
              <input type="text" class="form-control" name="category" id="itemCategory" readonly required>
            </div>

            <div class="form-group col-md-6">
              <label for="item_code">Item Code</label>
              <input type="text" class="form-control" name="item_code" id="itemCode" readonly required>
            </div>

            <div class="form-group col-md-6">
              <label for="worker_name">Assigned Worker</label>
              <input type="text" class="form-control" name="worker_name" placeholder="Enter worker name" required>
            </div>

            <div class="form-group col-md-6">
              <label for="project_name">Assigned Project</label>
              <select name="project_name" class="form-control select2" required>
                <option value="">Select Project</option>
                @foreach($projects as $project)
                  <option value="{{ $project->name }}">{{ $project->name }}</option>
                @endforeach
              </select>
            </div>

            <div class="form-group col-md-6">
              <label for="quantity_issued">Quantity Issued</label>
              <input type="number" class="form-control" name="quantity_issued" id="quantityIssued" min="1" required>
              <span id="quantityWarning" class="text-danger" style="display:none;">Warning: The quantity issued exceeds the available quantity!</span>
            </div>

            <div class="form-group col-md-6">
              <label for="purpose">Purpose / Task</label>
              <input type="text" class="form-control" name="purpose" required>
            </div>

            <div class="form-group col-md-6">
              <label for="condition_before_issue">Condition Before Issue</label>
              <input type="text" class="form-control" name="condition_before_issue" required>
            </div>

          </div>
          <div class="card-footer text-right">
            <button type="submit" class="btn btn-primary">Assign Tool</button>
            <a href="{{ route('tool-assignments.index') }}" class="btn btn-secondary">Cancel</a>
          </div>
        </div>
      </form>
    </div>
  </section>
</div>
@endsection

@push('scripts')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
  $(document).ready(function () {
    $('.select2').select2();
  });

  document.getElementById('toolSelect').addEventListener('change', function () {
    const selected = this.options[this.selectedIndex];
    document.getElementById('itemName').value = selected.dataset.itemName || '';
    document.getElementById('itemCategory').value = selected.dataset.category || '';
    document.getElementById('itemCode').value = selected.dataset.itemCode || '';
    
    // Get the available quantity of the selected tool
    const availableQuantity = selected.dataset.quantity || 0;
    
    // Enable the quantity input and reset the warning
    document.getElementById('quantityIssued').value = '';
    document.getElementById('quantityIssued').setAttribute('max', availableQuantity);
    document.getElementById('quantityWarning').style.display = 'none';
  });

  document.getElementById('quantityIssued').addEventListener('input', function () {
    const selectedTool = document.getElementById('toolSelect').selectedOptions[0];
    const availableQuantity = selectedTool ? selectedTool.dataset.quantity : 0;
    const quantityIssued = parseInt(this.value, 10);
    
    if (quantityIssued > availableQuantity) {
      document.getElementById('quantityWarning').style.display = 'inline';
    } else {
      document.getElementById('quantityWarning').style.display = 'none';
    }
  });
</script>
@endpush
