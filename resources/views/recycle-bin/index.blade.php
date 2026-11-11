@extends('layouts.app')
@section('title', 'Recycle Bin')

@section('content')
<div class="content-wrapper">
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1>Recycle Bin</h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
            <li class="breadcrumb-item active">Recycle Bin</li>
          </ol>
        </div>
      </div>
    </div>
  </section>

  <section class="content">
    <div class="container-fluid">
      <div class="row">
        <div class="col-12">
          @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
          @endif
          @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
          @endif

          <div class="card">
            <div class="card-body">
              <p class="text-muted">Deleted records are kept here. Records cannot be permanently deleted from the app.
                @role('super-admin') @else Only a super-admin can restore records. @endrole</p>

              <form method="GET" action="{{ route('recycle-bin.index') }}" class="form-inline mb-3">
                <label for="type" class="mr-2">Type</label>
                <select name="type" id="type" class="form-control mr-2" onchange="this.form.submit()">
                  <option value="">All types</option>
                  @foreach($types as $key => $def)
                    <option value="{{ $key }}" {{ $filter === $key ? 'selected' : '' }}>{{ $def[1] }}</option>
                  @endforeach
                </select>
                <a href="{{ route('recycle-bin.index') }}" class="btn btn-default"><i class="fa fa-sync"></i> Reset</a>
              </form>

              <table class="table table-bordered table-hover">
                <thead>
                  <tr>
                    <th>Type</th>
                    <th>Record</th>
                    <th>Deleted by</th>
                    <th>Deleted at</th>
                    <th>Reason</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($entries as $entry)
                  <tr>
                    <td>{{ $entry->type_label }}</td>
                    <td>{{ $entry->label }}</td>
                    <td>{{ $entry->deleted_by ? ($names[$entry->deleted_by] ?? 'User #'.$entry->deleted_by) : '-' }}</td>
                    <td>{{ $entry->deleted_at->format('d/M/Y H:i') }}</td>
                    <td>{{ $entry->reason ?: '-' }}</td>
                    <td>
                      @role('super-admin')
                      <form method="POST" action="{{ route('recycle-bin.restore', [$entry->type, $entry->id]) }}" onsubmit="return confirm('Restore this record?');">
                        @csrf
                        <button type="submit" class="btn btn-default btn-sm" title="Restore"><i class="fa fa-undo"></i> Restore</button>
                      </form>
                      @else
                      -
                      @endrole
                    </td>
                  </tr>
                  @empty
                  <tr><td colspan="6" class="text-center text-muted">The Recycle Bin is empty.</td></tr>
                  @endforelse
                </tbody>
              </table>

              {{ $entries->links('pagination::bootstrap-4') }}
            </div>
          </div>

          <div class="card">
            <div class="card-header"><h3 class="card-title">Voided purchase orders</h3></div>
            <div class="card-body table-responsive p-0">
              <table class="table table-bordered table-hover mb-0">
                <thead><tr><th>PO</th><th>Project</th><th>Supplier</th><th>Voided by</th><th>Voided at</th><th>Reason</th><th>Action</th></tr></thead>
                <tbody>
                  @forelse($voidedOrders as $po)
                  <tr>
                    <td>{{ $po->po_number ?: '#' . $po->portal_po_id }}</td>
                    <td>{{ $po->project_name ?: '-' }}</td>
                    <td>{{ $po->supplier_name ?: '-' }}</td>
                    <td>{{ $po->voided_by_name ?: '-' }}</td>
                    <td>{{ optional($po->voided_at)->format('d/M/Y H:i') }}</td>
                    <td>{{ $po->void_reason ?: '-' }}</td>
                    <td><a href="{{ route('portal-orders.show', $po) }}" class="btn btn-default btn-sm"><i class="fa fa-history"></i> View history</a></td>
                  </tr>
                  @empty
                  <tr><td colspan="7" class="text-center text-muted">No voided purchase orders.</td></tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>
@endsection
