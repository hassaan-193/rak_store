@extends('layouts.app')
@section('title', 'View Tool Assignment')

@section('content')
<div class="content-wrapper">
  <section class="content-header">
    <div class="container-fluid">
      <h1>Tool Assignment Details</h1>
    </div>
  </section>

  <section class="content">
    <div class="container-fluid">
      <div class="card">
        <div class="card-body row">
          @php $a = $assignment; @endphp
          <div class="col-md-6"><strong>Date / Time:</strong> {{ \Carbon\Carbon::parse($a->date_time)->format('d M Y, h:i A') }}</div>
          <div class="col-md-6"><strong>Item:</strong> {{ $a->item_name }} ({{ $a->item_code }})</div>
          <div class="col-md-6"><strong>Category:</strong> {{ $a->category }}</div>
          <div class="col-md-6"><strong>Worker:</strong> {{ $a->worker_name ?? '-' }}</div>
          <div class="col-md-6"><strong>Project:</strong> {{ $a->project_name }}</div>
          <div class="col-md-6"><strong>Quantity:</strong> {{ $a->quantity_issued }}</div>
          <div class="col-md-6"><strong>Purpose:</strong> {{ $a->purpose }}</div>
          <div class="col-md-6"><strong>Condition:</strong> {{ $a->condition_before_issue }}</div>
        </div>
        <div class="card-footer">
          <a href="{{ route('tool-assignments.index') }}" class="btn btn-secondary">Back</a>
        </div>
      </div>
    </div>
  </section>
</div>
@endsection
