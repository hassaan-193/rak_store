@extends('layouts.app')

@section('title', 'Portal Orders')

@section('content')
<div class="content-wrapper">
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1>Portal Orders</h1>
          <small class="text-muted">Purchase orders approved in the FTS portal</small>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
            <li class="breadcrumb-item active">Portal Orders</li>
          </ol>
        </div>
      </div>
    </div>
  </section>

  <section class="content">
    <div class="container-fluid">
      @if(!config('portal.enabled'))
        <div class="alert alert-warning">Portal link is switched OFF (PORTAL_INTEGRATION_ENABLED=false). Changes here are not sent to the portal.</div>
      @endif
      @if(($outbox->failed ?? 0) > 0 || ($outbox->pending ?? 0) > 0)
        <div class="alert alert-info">
          Messages waiting for the portal: {{ (int) $outbox->pending }} pending, {{ (int) $outbox->failed }} failed.
        </div>
      @endif

      <div class="card">
        <div class="card-body">
          <form method="GET" class="form-inline mb-3">
            <select name="status" class="form-control mr-2">
              <option value="">All statuses</option>
              @foreach(\App\Models\PortalOrder::STATUS_LABELS as $k => $label)
                <option value="{{ $k }}" {{ (request('status') === $k) ? 'selected' : '' }}>{{ $label }}</option>
              @endforeach
            </select>
            <select name="project" class="form-control mr-2">
              <option value="">All projects</option>
              @foreach($projects as $p)
                <option value="{{ $p }}" {{ request('project') === $p ? 'selected' : '' }}>{{ $p }}</option>
              @endforeach
            </select>
            <select name="supplier" class="form-control mr-2">
              <option value="">All suppliers</option>
              @foreach($suppliers as $p)
                <option value="{{ $p }}" {{ request('supplier') === $p ? 'selected' : '' }}>{{ $p }}</option>
              @endforeach
            </select>
            <button class="btn btn-default"><i class="fa fa-filter"></i> Filter</button>
          </form>

          <table class="table table-bordered table-hover">
            <thead>
              <tr>
                <th>PO No.</th>
                <th>Project</th>
                <th>Supplier</th>
                <th>Items</th>
                <th>Required</th>
                <th style="min-width:160px">Received</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              @forelse($rows as $row)
              @php $order = $row->order; @endphp
              <tr>
                <td>{{ $order->po_number ?: '#' . $order->portal_po_id }}</td>
                <td>
                  {{ $order->project_name }}
                  @if($order->project_name && !$order->project_id)
                    <span class="badge badge-warning" title="No store project with the same name">not matched</span>
                  @endif
                </td>
                <td>{{ $order->supplier_name }}</td>
                <td>{{ $order->lines->count() }}</td>
                <td>{{ optional($order->required_date)->format('d/M/Y') }}</td>
                <td>
                  @if($row->status === 'legacy_flow')
                    <span class="text-muted">-</span>
                  @else
                    <div class="progress" style="height:14px"><div class="progress-bar bg-success" style="width: {{ $row->percent }}%"></div></div>
                    <small>{{ rtrim(rtrim(number_format($row->percent, 2), '0'), '.') }}%</small>
                  @endif
                </td>
                <td>@include('portal-orders.status-badge', ['status' => $row->status])</td>
                <td><a href="{{ route('portal-orders.show', $order) }}" class="btn btn-default btn-sm"><i class="fa fa-eye"></i></a></td>
              </tr>
              @empty
              <tr><td colspan="8" class="text-center text-muted">No orders received from the portal yet.</td></tr>
              @endforelse
            </tbody>
          </table>

          {{ $rows->links() }}
        </div>
      </div>
    </div>
  </section>
</div>
@endsection
