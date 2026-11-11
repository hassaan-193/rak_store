@extends('layouts.app')
@section('title', 'View Tool Return')

@section('content')
<div class="content-wrapper">
  <section class="content-header">
    <div class="container-fluid">
      <h1>Tool Return Details</h1>
    </div>
  </section>

  <section class="content">
    <div class="container-fluid">
      <div class="card">
        <div class="card-body">
          @php $return = $toolReturn; $assignment = $return->toolAssignment; @endphp

          <dl class="row">
            <dt class="col-sm-4">Return Date</dt>
            <dd class="col-sm-8">{{ \Carbon\Carbon::parse($return->return_date)->format('d M Y') }}</dd>

            <dt class="col-sm-4">Quantity Returned</dt>
            <dd class="col-sm-8">{{ $return->quantity_returned }}</dd>

            <dt class="col-sm-4">Return Condition</dt>
            <dd class="col-sm-8">{{ $return->return_condition }}</dd>

            <hr class="col-12">

            <dt class="col-sm-4">Assigned Item</dt>
            <dd class="col-sm-8">{{ $assignment->item_name }} ({{ $assignment->item_code }})</dd>

            <dt class="col-sm-4">Category</dt>
            <dd class="col-sm-8">{{ $assignment->category }}</dd>

            <dt class="col-sm-4">Assigned Worker</dt>
            <dd class="col-sm-8">{{ $assignment->worker_name}}</dd>

            <dt class="col-sm-4">Assigned Project</dt>
            <dd class="col-sm-8">{{ $assignment->project_name }}</dd>

            <dt class="col-sm-4">Quantity Issued</dt>
            <dd class="col-sm-8">{{ $assignment->quantity_issued }}</dd>

            <dt class="col-sm-4">Purpose</dt>
            <dd class="col-sm-8">{{ $assignment->purpose }}</dd>

            <dt class="col-sm-4">Assignment Date</dt>
            <dd class="col-sm-8">{{ \Carbon\Carbon::parse($assignment->assignment_date)->format('d M Y') }}</dd>

            <dt class="col-sm-4">Condition Before Issue</dt>
            <dd class="col-sm-8">{{ $assignment->condition_before_issue }}</dd>
          </dl>
        </div>
        <div class="card-footer">
          <a href="{{ route('tool-returns.index') }}" class="btn btn-secondary">Back</a>
        </div>
      </div>
    </div>
  </section>
</div>
@endsection
