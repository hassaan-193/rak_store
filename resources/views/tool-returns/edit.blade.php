@extends('layouts.app')
@section('title', 'Edit Tool Return')

@section('content')
<div class="content-wrapper">
  <section class="content-header">
    <div class="container-fluid">
      <h1>Edit Tool Return</h1>
    </div>
  </section>

  <section class="content">
    <div class="container-fluid">
      <form action="{{ route('tool-returns.update', $return->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="card">
          <div class="card-body row">

            <div class="form-group col-md-6">
              <label for="assignment_id">Assigned Tool</label>
              <select name="assignment_id" id="assignmentSelect" class="form-control" required>
                <option value="">Select Assignment</option>
                @foreach($assignments as $assignment)
                  <option 
                    value="{{ $assignment->id }}"
                    data-item-name="{{ $assignment->item_name }}"
                    data-category="{{ $assignment->category }}"
                    data-item-code="{{ $assignment->item_code }}"
                    data-worker="{{ $assignment->worker->name ?? '-' }}"
                    data-project="{{ $assignment->project_name }}"
                    data-quantity="{{ $assignment->quantity_issued }}"
                    data-purpose="{{ $assignment->purpose }}"
                    data-date-time="{{ $assignment->date_time }}"
                    @if($assignment->id == $return->assignment_id) selected @endif
                  >
                    {{ $assignment->item_name }} ({{ $assignment->item_code }}) - {{ $assignment->worker->name ?? '-' }}
                  </option>
                @endforeach
              </select>
            </div>

            <div class="form-group col-md-6">
              <label>Item Name</label>
              <input type="text" id="itemName" class="form-control" value="{{ $return->assignment->item_name ?? '' }}" readonly>
            </div>

            <div class="form-group col-md-6">
              <label>Category</label>
              <input type="text" id="itemCategory" class="form-control" value="{{ $return->assignment->category ?? '' }}" readonly>
            </div>

            <div class="form-group col-md-6">
              <label>Item Code</label>
              <input type="text" id="itemCode" class="form-control" value="{{ $return->assignment->item_code ?? '' }}" readonly>
            </div>

            <div class="form-group col-md-6">
              <label>Assigned Worker</label>
              <input type="text" id="assignedWorker" class="form-control" value="{{ $return->assignment->worker->name ?? '' }}" readonly>
            </div>

            <div class="form-group col-md-6">
              <label>Assigned Project</label>
              <input type="text" id="assignedProject" class="form-control" value="{{ $return->assignment->project_name ?? '' }}" readonly>
            </div>

            <div class="form-group col-md-6">
              <label>Quantity Issued</label>
              <input type="text" id="quantityIssued" class="form-control" value="{{ $return->assignment->quantity_issued ?? '' }}" readonly>
            </div>

            <div class="form-group col-md-6">
              <label>Purpose / Task</label>
              <input type="text" id="purpose" class="form-control" value="{{ $return->assignment->purpose ?? '' }}" readonly>
            </div>

            <div class="form-group col-md-6">
              <label>Date / Time</label>
              <input type="text" id="dateTime" class="form-control" value="{{ \Carbon\Carbon::parse($return->assignment->date_time)->format('Y-m-d H:i') ?? '' }}" readonly>
            </div>

            <div class="form-group col-md-6">
              <label for="return_condition">Return Condition</label>
              <input type="text" name="return_condition" class="form-control" value="{{ $return->return_condition }}" required>
            </div>

            <div class="form-group col-md-6">
              <label for="return_date">Return Date</label>
              <input type="date" name="return_date" class="form-control" value="{{ \Carbon\Carbon::parse($return->return_date)->format('Y-m-d') }}" required>
            </div>

            <div class="form-group col-md-6">
              <label for="quantity_returned">Quantity Returned</label>
              <input type="number" name="quantity_returned" class="form-control" value="{{ $return->quantity_returned }}" min="1" required>
            </div>

          </div>
          <div class="card-footer text-right">
            <button type="submit" class="btn btn-primary">Update Return</button>
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
@endpush
