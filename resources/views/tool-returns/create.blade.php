@extends('layouts.app')
@section('title', 'Return Tool')

@section('content')
<div class="content-wrapper">
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1>Return Tool</h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ route('tool-returns.index') }}">Tool Returns</a></li>
            <li class="breadcrumb-item active">Return Tool</li>
          </ol>
        </div>
      </div>
    </div>
  </section>

  <section class="content">
    <div class="container-fluid">
      <form action="{{ route('tool-returns.store') }}" method="POST">
        @csrf
        <div class="card">
          <div class="card-body row">

            <div class="form-group col-md-6">
              <label for="assignment_id">Assigned Tool</label>
              <select name="assignment_id" id="assignmentSelect" class="form-control" required>
                <option value="">Select Assignment</option>
                @foreach($assignments as $assignment)
                  <option 
                    value="{{ $assignment->id }}"
                    data-item-name="{{ $assignment->tool->item_name }}"
                    data-category="{{ $assignment->tool->category }}"
                    data-item-code="{{ $assignment->tool->item_code }}"
                    data-worker="{{ $assignment->worker_name }}" 
                    data-project="{{ $assignment->project_name }}"
                    data-quantity="{{ $assignment->quantity_issued }}"
                    data-purpose="{{ $assignment->purpose }}"
                    data-date-time="{{ $assignment->date_time }}"
                  >
                    {{ $assignment->tool->item_name }} ({{ $assignment->tool->item_code }}) - {{ $assignment->worker_name }}
                  </option>
                @endforeach
              </select>
            </div>

            <div class="form-group col-md-6">
              <label>Item Name</label>
              <input type="text" id="itemName" class="form-control" readonly>
            </div>

            <div class="form-group col-md-6">
              <label>Category</label>
              <input type="text" id="itemCategory" class="form-control" readonly>
            </div>

            <div class="form-group col-md-6">
              <label>Item Code</label>
              <input type="text" id="itemCode" class="form-control" readonly>
            </div>

            <div class="form-group col-md-6">
              <label>Assigned Worker</label>
              <input type="text" id="assignedWorker" class="form-control" readonly>
            </div>

            <div class="form-group col-md-6">
              <label>Assigned Project</label>
              <input type="text" id="assignedProject" class="form-control" readonly>
            </div>

            <div class="form-group col-md-6">
              <label>Quantity Issued</label>
              <input type="text" id="quantityIssued" class="form-control" readonly>
            </div>

            <div class="form-group col-md-6">
              <label>Purpose / Task</label>
              <input type="text" id="purpose" class="form-control" readonly>
            </div>

            <div class="form-group col-md-6">
              <label>Date / Time</label>
              <input type="text" id="dateTime" class="form-control" readonly>
            </div>

            <div class="form-group col-md-6">
              <label for="return_condition">Return Condition</label>
              <input type="text" name="return_condition" class="form-control" required>
            </div>

            <div class="form-group col-md-6">
              <label for="return_date">Return Date</label>
              <input type="date" name="return_date" class="form-control" required>
            </div>

            <div class="form-group col-md-6">
              <label for="quantity_returned">Quantity Returned</label>
              <input type="number" id="quantity_returned" name="quantity_returned" class="form-control" min="1" required>
            </div>
            <span id="returnQuantityWarning" style="color: red; display: none;">
                Returned quantity exceeds issued quantity!
            </span>


          </div>
          <div class="card-footer text-right">
            <button type="submit" class="btn btn-primary">Return Tool</button>
            <a href="{{ route('tool-returns.index') }}" class="btn btn-secondary">Cancel</a>
          </div>
        </div>
      </form>
    </div>
  </section>
</div>
@endsection

@push('scripts')
<script>
  document.getElementById('assignmentSelect').addEventListener('change', function () {
    const selected = this.options[this.selectedIndex];
    document.getElementById('itemName').value = selected.dataset.itemName || '';
    document.getElementById('itemCategory').value = selected.dataset.category || '';
    document.getElementById('itemCode').value = selected.dataset.itemCode || '';
    document.getElementById('assignedWorker').value = selected.dataset.worker || '';
    document.getElementById('assignedProject').value = selected.dataset.project || '';
    document.getElementById('quantityIssued').value = selected.dataset.quantity || '';
    document.getElementById('purpose').value = selected.dataset.purpose || '';
    document.getElementById('dateTime').value = selected.dataset.dateTime || '';
  });
</script>

<script>
  document.getElementById('assignmentSelect').addEventListener('change', function () {
    const selected = this.options[this.selectedIndex];

    // Populate assignment-related fields
    document.getElementById('itemName').value = selected.dataset.itemName || '';
    document.getElementById('itemCategory').value = selected.dataset.category || '';
    document.getElementById('itemCode').value = selected.dataset.itemCode || '';
    document.getElementById('assignedWorker').value = selected.dataset.worker || '';
    document.getElementById('assignedProject').value = selected.dataset.project || '';
    document.getElementById('quantityIssued').value = selected.dataset.quantity || '';
    document.getElementById('purpose').value = selected.dataset.purpose || '';
    document.getElementById('dateTime').value = selected.dataset.dateTime || '';

    // Reset quantity returned and warning
    document.getElementById('quantity_returned').value = '';
    document.getElementById('returnQuantityWarning').style.display = 'none';

    // Set max allowed quantity
    document.getElementById('quantity_returned').setAttribute('max', selected.dataset.quantity || 0);
  });

  document.getElementById('quantity_returned').addEventListener('input', function () {
    const selected = document.getElementById('assignmentSelect').selectedOptions[0];
    const issuedQuantity = parseInt(selected.dataset.quantity, 10) || 0;
    const returnedQuantity = parseInt(this.value, 10) || 0;

    if (returnedQuantity > issuedQuantity) {
      document.getElementById('returnQuantityWarning').style.display = 'block';
    } else {
      document.getElementById('returnQuantityWarning').style.display = 'none';
    }
  });
</script>


@endpush
