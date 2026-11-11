@extends('layouts.app')
@section('title', 'Edit Tool Assignment')

@section('content')
<div class="content-wrapper">
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1>Edit Tool Assignment</h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ route('tool-assignments.index') }}">Tool Assignments</a></li>
            <li class="breadcrumb-item active">Edit Tool Assignment</li>
          </ol>
        </div>
      </div>
    </div>
  </section>

  <section class="content">
    <div class="container-fluid">
      <form action="{{ route('tool-assignments.update', $assignment->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="card">
          <div class="card-body row">

            <div class="form-group col-md-6">
              <label for="date_time">Date / Time</label>
              <input type="datetime-local" class="form-control" name="date_time"
                value="{{ \Carbon\Carbon::parse($assignment->date_time)->format('Y-m-d\TH:i') }}" required>
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
                    @if($tool->id == $assignment->tool_id) selected @endif
                  >
                    {{ $tool->item_name }} ({{ $tool->item_code }})
                  </option>
                @endforeach
              </select>
            </div>

            <div class="form-group col-md-6">
              <label for="item_name">Item Name</label>
              <input type="text" class="form-control" name="item_name" id="itemName" value="{{ $assignment->item_name }}" readonly required>
            </div>

            <div class="form-group col-md-6">
              <label for="category">Category</label>
              <input type="text" class="form-control" name="category" id="itemCategory" value="{{ $assignment->category }}" readonly required>
            </div>

            <div class="form-group col-md-6">
              <label for="item_code">Item Code</label>
              <input type="text" class="form-control" name="item_code" id="itemCode" value="{{ $assignment->item_code }}" readonly required>
            </div>

            <div class="form-group col-md-6">
              <label for="worker_name">Assigned Worker</label>
              <input type="text" class="form-control" name="worker_name" value="{{ $assignment->worker_name }}" required>
            </div>
            <div class="form-group col-md-6">
  <label for="project_name">Assigned Project</label>
  <select name="project_name" class="form-control select2" required>
    <option value="">Select Project</option>
    @foreach($projectList as $id => $project)
      <option value="{{ $id }}" @if($assignment->project_name == $project) selected @endif>{{ $project }}</option>
    @endforeach
  </select>
</div>








            <div class="form-group col-md-6">
              <label for="quantity_issued">Quantity Issued</label>
              <input type="number" class="form-control" name="quantity_issued" value="{{ $assignment->quantity_issued }}" min="1" required>
            </div>

            <div class="form-group col-md-6">
              <label for="purpose">Purpose / Task</label>
              <input type="text" class="form-control" name="purpose" value="{{ $assignment->purpose }}" required>
            </div>

            <div class="form-group col-md-6">
              <label for="condition_before_issue">Condition Before Issue</label>
              <input type="text" class="form-control" name="condition_before_issue" value="{{ $assignment->condition_before_issue }}" required>
            </div>

          </div>
          <div class="card-footer text-right">
            <button type="submit" class="btn btn-primary">Update</button>
            <a href="{{ route('tool-assignments.index') }}" class="btn btn-secondary">Cancel</a>
          </div>
        </div>
      </form>
    </div>
  </section>
</div>
@endsection

@push('scripts')
<!-- Select2 -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>

$(document).ready(function() {
    $('.select2').select2({
      placeholder: "Select a project",
      allowClear: true
    });
  });


  document.getElementById('toolSelect').addEventListener('change', function () {
    const selected = this.options[this.selectedIndex];
    document.getElementById('itemName').value = selected.dataset.itemName || '';
    document.getElementById('itemCategory').value = selected.dataset.category || '';
    document.getElementById('itemCode').value = selected.dataset.itemCode || '';
  });


</script>
@endpush
