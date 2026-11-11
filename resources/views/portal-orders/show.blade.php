@extends('layouts.app')

@section('title', 'Portal Order')

@section('content')
@php
  $fmt = function ($n) { return rtrim(rtrim(number_format((float) $n, 3, '.', ''), '0'), '.') ?: '0'; };
  $canReceive = auth()->user()->can('po-receive');
@endphp
<div class="content-wrapper">
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1>PO {{ $portalOrder->po_number ?: '#' . $portalOrder->portal_po_id }}
            @include('portal-orders.status-badge', ['status' => $statusKey])
          </h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ route('portal-orders.index') }}">Portal Orders</a></li>
            <li class="breadcrumb-item active">Details</li>
          </ol>
        </div>
      </div>
    </div>
  </section>

  <section class="content">
    <div class="container-fluid">
      @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
      @endif
      @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
      @endif
      @if($statusKey === 'cancelled')
        <div class="alert alert-danger">This order is cancelled in the portal. Entries stay visible but no new entries can be recorded.</div>
      @elseif($statusKey === 'voided')
        <div class="alert alert-secondary"><strong>Voided purchase order (archived).</strong> Voided {{ optional($portalOrder->voided_at)->format('d/M/Y H:i') }} by {{ $portalOrder->voided_by_name ?: 'portal admin' }}. Reason: {{ $portalOrder->void_reason }}. History is read-only; completed stock movements were kept.</div>
      @elseif($statusKey === 'fully_covered')
        <div class="alert alert-info">Fully covered (stock issued). The vendor balance stays open; a later vendor delivery is recorded normally.</div>
      @elseif($statusKey === 'legacy_flow')
        <div class="alert alert-info">Completed under the old dispatch flow. No delivery entries exist for this order and none can be added.</div>
      @endif

      {{-- Header card --}}
      <div class="card">
        <div class="card-body">
          <div class="row">
            <div class="col-md-5">
              <dl class="row mb-0">
                <dt class="col-sm-4">Project</dt><dd class="col-sm-8">{{ $portalOrder->project_name ?: '-' }}</dd>
                <dt class="col-sm-4">Supplier</dt><dd class="col-sm-8">{{ $portalOrder->supplier_name ?: '-' }}</dd>
                <dt class="col-sm-4">Required by</dt><dd class="col-sm-8">{{ optional($portalOrder->required_date)->format('d/M/Y') ?: '-' }}</dd>
              </dl>
            </div>
            <div class="col-md-4">
              @if($statusKey !== 'legacy_flow')
                <strong>PO coverage: {{ $fmt($percent) }}%</strong>
                <div class="progress mt-1" style="height:20px"><div class="progress-bar bg-success" style="width: {{ $percent }}%">{{ $fmt($percent) }}%</div></div>
              @endif
            </div>
            <div class="col-md-3 text-md-right mt-2 mt-md-0">
              @if($canReceive && $statusKey !== 'voided')
                <form method="POST" action="{{ route('portal-orders.resync', $portalOrder) }}" class="d-inline">@csrf
                  <button class="btn btn-default btn-sm"><i class="fa fa-sync"></i> Resync to portal</button>
                </form>
              @endif
            </div>
          </div>

          @if($canReceive && $canEnter)
            <hr>
            <button type="button" class="btn btn-primary entry-btn" data-type="store_receipt"><i class="fa fa-warehouse"></i> Record receipt at store</button>
            <button type="button" class="btn btn-info entry-btn" data-type="direct_to_site"><i class="fa fa-truck"></i> Record direct to site</button>
            <button type="button" class="btn btn-secondary entry-btn" data-type="from_stock"><i class="fa fa-boxes"></i> Supply from stock</button>
          @endif
        </div>
      </div>

      {{-- Items --}}
      <div class="card">
        <div class="card-header"><h3 class="card-title">Items</h3></div>
        <div class="card-body table-responsive p-0">
          <table class="table table-sm mb-0">
            <thead>
              <tr><th>Code</th><th>Material</th><th>Unit</th><th class="text-right">Ordered</th><th class="text-right">At store</th><th class="text-right">Direct to site</th><th class="text-right">Vendor received</th><th class="text-right">Vendor due</th><th class="text-right">From stock</th><th class="text-right">To cover</th><th style="min-width:150px">Progress</th></tr>
            </thead>
            <tbody>
              @forelse($items as $i)
              <tr>
                <td>{{ $i['item_code'] }}</td>
                <td>{{ $i['material_name'] }}</td>
                <td>{{ $i['unit'] }}</td>
                <td class="text-right">{{ $fmt($i['ordered']) }}</td>
                <td class="text-right">{{ $fmt($i['at_store']) }}</td>
                <td class="text-right">{{ $fmt($i['direct_to_site']) }}</td>
                <td class="text-right">{{ $fmt($i['vendor_received']) }}</td>
                <td class="text-right">{{ $fmt($i['vendor_due']) }}</td>
                <td class="text-right">{{ $fmt($i['from_stock']) }}</td>
                <td class="text-right">{{ $fmt($i['to_cover']) }}</td>
                <td>
                  <div class="progress" style="height:14px"><div class="progress-bar {{ $i['percent'] >= 100 ? 'bg-success' : 'bg-warning' }}" style="width: {{ $i['percent'] }}%"></div></div>
                  <small>{{ $fmt($i['percent']) }}%</small>
                  @if($i['percent'] >= 100 && $i['vendor_due'] > 0)
                    <span class="badge badge-info" title="Stock completed the line; vendor balance stays open">Fully covered</span>
                  @endif
                  @if($i['over_delivered'] > 0)
                    <span class="badge badge-danger" title="More than ordered">Over by {{ $fmt($i['over_delivered']) }}</span>
                  @endif
                </td>
              </tr>
              @empty
              <tr><td colspan="11" class="text-center text-muted">No items.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>

      {{-- Delivery log --}}
      <div class="card">
        <div class="card-header"><h3 class="card-title">Delivery log</h3></div>
        <div class="card-body table-responsive p-0">
          <table class="table table-sm mb-0">
            <thead><tr><th>Date</th><th>Type</th><th>Material</th><th class="text-right">Qty</th><th>Vendor DN</th><th>Site receiver / source</th><th>Note</th><th>By</th><th></th></tr></thead>
            <tbody>
              @forelse($portalOrder->entries as $e)
              <tr class="{{ $e->voided_at ? 'text-muted' : '' }}" @if($e->voided_at) style="text-decoration: line-through" @endif>
                <td>{{ $e->entry_date->format('d/M/Y') }}</td>
                <td>{{ $e->type_label }}</td>
                <td>{{ optional($e->line)->material_name }}</td>
                <td class="text-right">{{ $fmt($e->quantity) }} {{ optional($e->line)->unit }}</td>
                <td>{{ $e->vendor_dn_number }}</td>
                <td>
                  {{ $e->site_receiver_name }}{{ $e->stockLabel() }}
                  @if($e->stock_issue_details)
                    @php $d = $e->stock_issue_details; @endphp
                    <div class="small" style="text-decoration:none"><strong>Supplied from Store stock</strong><br>
                      Row #{{ $d['source_stock_item_id'] ?? '' }} · DN {{ $d['original_dn'] ?? '-' }} · {{ $d['warehouse_location'] ?? '-' }} · {{ $d['source_scope'] ?? '' }}<br>
                      To: {{ $d['destination'] ?? '-' }} · by {{ $d['storekeeper'] ?? '-' }} · balance {{ $fmt($d['balance_before'] ?? 0) }} → {{ $fmt($d['balance_after'] ?? 0) }}
                      @if(!empty($d['reversal']))<br>Reversed {{ $d['reversal']['restored_at'] }}: {{ $fmt($d['reversal']['balance_before']) }} → {{ $fmt($d['reversal']['balance_after']) }}@endif
                    </div>
                  @endif
                  @if($e->dn_file_path)
                    <a href="{{ route('portal-orders.entries.dn', [$portalOrder, $e]) }}" style="text-decoration:none"><i class="fa fa-download"></i> DN</a>
                  @endif
                </td>
                <td>{{ $e->note }}</td>
                <td>{{ optional($e->recorder)->name }}</td>
                <td style="text-decoration:none">
                  @if($e->voided_at)
                    <span class="badge badge-secondary" style="text-decoration:none" title="{{ $e->void_reason }}">Voided: {{ \Illuminate\Support\Str::limit($e->void_reason, 40) }}</span>
                  @elseif($canReceive && $statusKey !== 'voided')
                    <button type="button" class="btn btn-outline-danger btn-xs void-btn" data-action="{{ route('portal-orders.entries.void', [$portalOrder, $e]) }}">Void</button>
                  @endif
                </td>
              </tr>
              @empty
              <tr><td colspan="9" class="text-center text-muted">Nothing recorded yet.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </section>
</div>

@if($canReceive)
{{-- Record entry modal (one form, fields shown per type) --}}
<div class="modal fade" id="entryModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <form method="POST" action="{{ route('portal-orders.entries.store', $portalOrder) }}" enctype="multipart/form-data" class="modal-content">
      @csrf
      <input type="hidden" name="type" id="entry-type" value="{{ old('type', 'store_receipt') }}">
      <input type="hidden" name="submission_uuid" id="submission-uuid" value="{{ old('submission_uuid') }}">
      <div class="modal-header">
        <h5 class="modal-title" id="entry-title">Record entry</h5>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label>Item</label>
          <select name="portal_order_line_id" id="entry-line" class="form-control" required>
            @foreach($portalOrder->lines as $l)
              <option value="{{ $l->id }}" data-name="{{ mb_strtolower($l->material_name) }}" {{ (string) old('portal_order_line_id') === (string) $l->id ? 'selected' : '' }}>{{ $l->material_name }} ({{ $fmt($l->quantity_ordered) }} {{ $l->unit }})</option>
            @endforeach
          </select>
        </div>
        <div class="form-row">
          <div class="form-group col-6">
            <label>Quantity</label>
            <input type="number" name="quantity" step="0.001" min="0.001" class="form-control" required value="{{ old('quantity') }}">
            <small class="text-warning d-none" id="over-warn"><span id="over-warn-text"></span></small>
          </div>
          <div class="form-group col-6">
            <label>Date</label>
            <input type="date" name="entry_date" max="{{ now()->toDateString() }}" class="form-control" required value="{{ old('entry_date', now()->toDateString()) }}">
          </div>
        </div>
        <div class="form-group type-field type-store_receipt type-direct_to_site">
          <label>Vendor DN number</label>
          <input type="text" name="vendor_dn_number" class="form-control" maxlength="100" value="{{ old('vendor_dn_number') }}">
        </div>
        <div class="form-group type-field type-store_receipt">
          <label>Stock category <small class="text-muted">(default General)</small></label>
          <input type="text" name="category" class="form-control" maxlength="255" value="{{ old('category') }}" placeholder="General">
        </div>
        <div class="form-row type-field type-store_receipt">
          <div class="form-group col-6">
            <label>Warehouse location</label>
            <input type="text" name="store_location" class="form-control" maxlength="255" value="{{ old('store_location') }}">
          </div>
          <div class="form-group col-6">
            <label>Stock destination</label>
            <select name="stock_scope" class="form-control">
              <option value="general" {{ old('stock_scope') === 'general' ? 'selected' : '' }}>General stock</option>
              @if($portalOrder->project_id)<option value="project" {{ old('stock_scope') === 'project' ? 'selected' : '' }}>Project: {{ $portalOrder->project_name }}</option>@endif
            </select>
          </div>
        </div>
        <div class="form-group type-field type-direct_to_site">
          <label>Site receiver name</label>
          <input type="text" name="site_receiver_name" class="form-control" maxlength="255" value="{{ old('site_receiver_name') }}">
        </div>
        <div class="form-group type-field type-direct_to_site">
          <label>Photo / PDF of the signed DN <small class="text-muted">(jpg, png, pdf, max 5 MB)</small></label>
          <input type="file" name="dn_file" class="form-control-file" accept=".pdf,.jpg,.jpeg,.png">
        </div>
        <div class="form-group type-field type-from_stock">
          <label>Take from stock row</label>
          <select name="source_stock_item_id" id="entry-stock" class="form-control">
            <option value="">-- choose --</option>
            @foreach($stockByLine as $lineId => $rows)
              @foreach($rows as $s)
              <option value="{{ $s->id }}" data-line="{{ $lineId }}" data-qty="{{ (float) $s->quantity }}" {{ (string) old('source_stock_item_id') === (string) $s->id ? 'selected' : '' }}>
                #{{ $s->id }} | DN {{ optional($s->incomingMaterial)->dn_number ?: '-' }} | {{ optional($s->incomingMaterial)->store_location ?: '-' }} | {{ optional(optional($s->incomingMaterial)->project)->name ?: 'General' }} | {{ $fmt($s->quantity) }} {{ $s->unit }} available
              </option>
              @endforeach
            @endforeach
          </select>
          <small class="text-muted" id="stock-empty"></small>
        </div>
        <div class="form-group type-field type-from_stock">
          <label>Stock destination <small class="text-muted">(where the stock goes)</small></label>
          <input type="text" name="issue_destination" class="form-control" maxlength="255" value="{{ old('issue_destination', $portalOrder->project_name) }}">
        </div>
        <div class="form-group">
          <label>Note</label>
          <textarea name="note" rows="2" class="form-control" maxlength="1000">{{ old('note') }}</textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
        <button class="btn btn-primary">Save entry</button>
      </div>
    </form>
  </div>
</div>

{{-- Void modal --}}
<div class="modal fade" id="voidModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <form method="POST" id="void-form" action="" class="modal-content">
      @csrf
      <div class="modal-header">
        <h5 class="modal-title">Void entry</h5>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <div class="modal-body">
        <p class="text-muted">The entry stays in the log marked as voided. Stock moved by it is reversed.</p>
        <div class="form-group">
          <label>Reason (required)</label>
          <textarea name="void_reason" rows="3" class="form-control" maxlength="1000" required></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
        <button class="btn btn-danger">Void entry</button>
      </div>
    </form>
  </div>
</div>

@push('scripts')
<script>
  (function () {
    var titles = {store_receipt: 'Record receipt at store', direct_to_site: 'Record direct to site', from_stock: 'Supply from stock'};
    var vendorDue = @json(collect($items)->pluck('vendor_due', 'line_id'));
    var toCover = @json(collect($items)->pluck('to_cover', 'line_id'));
    function uuid() { return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) { var r = Math.random() * 16 | 0; return (c === 'x' ? r : (r & 0x3 | 0x8)).toString(16); }); }

    function setType(type) {
      $('#entry-type').val(type);
      if (!$('#submission-uuid').val()) { $('#submission-uuid').val(uuid()); }
      $('#entry-title').text(titles[type]);
      $('.type-field').addClass('d-none').find('input,select,textarea').prop('disabled', true);
      $('.type-' + type).removeClass('d-none').find('input,select,textarea').prop('disabled', false);
      $('[name=vendor_dn_number]').prop('required', type !== 'from_stock');
      $('[name=store_location]').prop('required', type === 'store_receipt');
      $('[name=issue_destination]').prop('required', type === 'from_stock');
      $('[name=site_receiver_name],[name=dn_file]').prop('required', type === 'direct_to_site');
      $('#entry-stock').prop('required', type === 'from_stock');
      filterStock();
    }
    function filterStock() {
      var line = ($('#entry-line').val() || '').toString();
      var n = 0;
      $('#entry-stock option[data-line]').each(function () {
        var match = String($(this).data('line')) === line;
        $(this).prop('hidden', !match).prop('disabled', !match);
        if (match) { n++; }
      });
      var sel = $('#entry-stock option:selected');
      if (sel.data('line') !== undefined && String(sel.data('line')) !== line) { $('#entry-stock').val(''); }
      $('#stock-empty').text(n ? '' : 'No eligible stock for this item.');
    }
    function warnOver() {
      var line = $('#entry-line').val();
      var q = parseFloat($('[name=quantity]').val());
      var type = $('#entry-type').val();
      var msg = '';
      if (type === 'from_stock') {
        var avail = parseFloat($('#entry-stock option:selected').data('qty'));
        var max = Math.min(parseFloat(toCover[line]), isNaN(avail) ? Infinity : avail);
        if (q > max) { msg = 'Stock issue is limited to ' + max + ' (still to cover / row balance).'; }
      } else {
        var max = parseFloat(vendorDue[line]);
        if (q > max) { msg = 'Not allowed: the vendor only still owes ' + max + '.'; }
      }
      // browser-side limit (the server enforces it too)
      var cap = (typeof max !== 'undefined' && isFinite(max)) ? max : '';
      $('[name=quantity]').attr('max', cap);
      $('[name=quantity]')[0].setCustomValidity(msg ? 'Quantity is above the allowed maximum.' : '');
      $('#over-warn-text').text(msg);
      $('#over-warn').toggleClass('d-none', !msg);
    }
    $('.entry-btn').on('click', function () { $('#submission-uuid').val(uuid()); setType($(this).data('type')); $('#entryModal').modal('show'); });
    $('#entry-line').on('change', function () { filterStock(); warnOver(); });
    $('[name=quantity]').on('input', warnOver);
    $('#entry-stock').on('change', warnOver);
    $('.void-btn').on('click', function () { $('#void-form').attr('action', $(this).data('action')); $('#voidModal').modal('show'); });
    @if(old('type'))
      setType(@json(old('type'))); $('#entryModal').modal('show');
    @endif
  })();
</script>
@endpush
@endif
@endsection
