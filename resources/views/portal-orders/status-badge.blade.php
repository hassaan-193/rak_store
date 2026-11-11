@php
  $colors = ['not_received' => 'secondary', 'partially_received' => 'warning', 'fully_covered' => 'primary', 'fully_received' => 'success', 'legacy_flow' => 'info', 'cancelled' => 'danger', 'voided' => 'dark'];
@endphp
<span class="badge badge-{{ $colors[$status] ?? 'secondary' }}">{{ \App\Models\PortalOrder::STATUS_LABELS[$status] ?? ucwords(str_replace('_', ' ', $status)) }}</span>
