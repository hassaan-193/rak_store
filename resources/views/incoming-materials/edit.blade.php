@extends('layouts.app')

@section('title', 'Edit Incoming Material | FTS')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Edit Incoming Material</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">Edit Incoming Material</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <form method="POST" action="{{ route('incoming-materials.update', $incomingMaterial->id) }}">
            @csrf
            @method('PUT')
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">

                        <div class="card card-primary">
                            <div class="card-body">

                                <div class="row">
                                    <div class="col-4">
                                        <div class="form-group">
                                            <label for="date_time">Date / Time</label>
                                            <input type="datetime-local" name="date_time" class="form-control" value="{{ old('date_time', \Carbon\Carbon::parse($incomingMaterial->date_time)->format('Y-m-d\TH:i')) }}" required>
                                        </div>
                                    </div>

                                    <div class="col-4">
                                        <div class="form-group">
                                            <label for="source_type">Incoming Material</label>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="source_type" value="driver" id="source_driver" {{ old('source_type', $incomingMaterial->source_type) == 'driver' ? 'checked' : '' }}>
                                                <label class="form-check-label" for="source_driver">FTS Driver</label>

                                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

                                                <input class="form-check-input" type="radio" name="source_type" value="supplier" id="source_supplier" {{ old('source_type', $incomingMaterial->source_type) == 'supplier' ? 'checked' : '' }}>
                                                <label class="form-check-label" for="source_supplier">Supplier</label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-4">
                                        <div class="form-group">
                                            <label for="project_type">Project Type</label>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="project_type" value="general" id="project_general" {{ old('project_type', $incomingMaterial->project_type) == 'general' ? 'checked' : '' }}>
                                                <label class="form-check-label" for="project_general">General</label>

                                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

                                                <input class="form-check-input" type="radio" name="project_type" value="project" id="project_project" {{ old('project_type', $incomingMaterial->project_type) == 'project' ? 'checked' : '' }}>
                                                <label class="form-check-label" for="project_project">Project</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-2" id="projectSection" style="display: none;">
                                    <div class="col-3">
                                        <div class="form-group">
                                            <label>Project Name</label>
                                            <select class="form-control select2" name="project_id">
                                                <option value="">Select Project</option>
                                                @foreach ($projects as $project)
                                                    <option value="{{ $project->id }}" {{ old('project_id', $incomingMaterial->project_id) == $project->id ? 'selected' : '' }}>
                                                        {{ $project->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-2">
                                    <div class="col-4" id="driverSection" style="display: none;">
                                        <div class="form-group">
                                            <label>FTS Driver</label>
                                            <select class="form-control select2" name="driver_id">
                                                <option value="">Select Driver</option>
                                                @foreach ($drivers as $id => $name)
                                                    <option value="{{ $id }}" {{ old('driver_id', $incomingMaterial->driver_id) == $id ? 'selected' : '' }}>
                                                        {{ $name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-4" id="supplierSection" style="display: none;">
                                        <div class="form-group">
                                            <label>Supplier</label>
                                            <select class="form-control select2" name="supplier_id">
                                                <option value="">Select Supplier</option>
                                                @foreach ($suppliers as $id => $name)
                                                    <option value="{{ $id }}" {{ old('supplier_id', $incomingMaterial->supplier_id) == $id ? 'selected' : '' }}>
                                                        {{ $name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-3">
                                        <div class="form-group">
                                            <label>Engineer Name</label>
                                            <input type="text" name="engineer_name" class="form-control" placeholder="Engineer Name" value="{{ old('engineer_name', $incomingMaterial->engineer_name) }}" required>
                                        </div>
                                    </div>

                                    <div class="col-3">
                                        <div class="form-group">
                                            <label>Store Location</label>
                                            <input type="text" name="store_location" class="form-control" placeholder="Store Location" value="{{ old('store_location', $incomingMaterial->store_location) }}" required>
                                        </div>
                                    </div>

                                    <div class="col-3">
                                        <div class="form-group">
                                            <label>DN Number</label>
                                            <input type="text" name="dn_number" class="form-control" placeholder="DN Number" value="{{ old('dn_number', $incomingMaterial->dn_number) }}" required>
                                        </div>
                                    </div>
                                </div>

                            </div> <!-- /.card-body -->
                        </div>
                    </div>

                    <!-- Materials Section -->
                    <div class="col-12">
                        <div class="card card-primary">
                            <div class="card-header border-0 ui-sortable-handle">
                                <h3 class="card-title">Edit Material Items</h3>
                                <div class="card-tools">
                                    <button type="button" class="btn btn-sm btn-success btn-flat" id="addRow">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger btn-flat" id="removeRow">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="card-body" id="materialsWrapper">
                                @foreach ($incomingMaterial->materials as $index => $material)
                                    <div class="row material-row">
                                        <div class="col-3">
                                            <div class="form-group">
                                                <label>Item Name</label>
                                                <input type="text" name="materials[{{ $index }}][item_name]" class="form-control" value="{{ $material->item_name }}" required>
                                            </div>
                                        </div>
                                        <div class="col-3">
                                            <div class="form-group">
                                                <label>Category</label>
                                                <input type="text" name="materials[{{ $index }}][category]" class="form-control" value="{{ $material->category }}" required>
                                            </div>
                                        </div>
                                        <div class="col-2">
                                            <div class="form-group">
                                                <label>Brand</label>
                                                <input type="text" name="materials[{{ $index }}][brand]" class="form-control" value="{{ $material->brand }}">
                                            </div>
                                        </div>
                                        <div class="col-2">
                                            <div class="form-group">
                                                <label>Unit</label>
                                                <input type="text" name="materials[{{ $index }}][unit]" class="form-control" value="{{ $material->unit }}" required>
                                            </div>
                                        </div>
                                        <div class="col-2">
                                            <div class="form-group">
                                                <label>Quantity</label>
                                                <input type="number" name="materials[{{ $index }}][quantity]" class="form-control" value="{{ $material->quantity }}" required>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div> <!-- /.card-body -->

                            <div class="card-body">
                                <button type="submit" class="btn btn-primary">Update</button>
                                <a href="{{ route('incoming-materials.index') }}" class="btn btn-default">Cancel</a>
                            </div>
                        </div>
                    </div>

                </div> <!-- /.row -->
            </div> <!-- /.container-fluid -->
        </form>
    </section>
</div>
@endsection

@push('scripts')
<link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>

<script>
    let rowIndex = {{ count($incomingMaterial->materials) }};

    $(document).ready(function() {
        $('.select2').select2({
            placeholder: "Select an option",
            allowClear: true,
            width: '100%'
        });

        // Show/Hide based on pre-filled data
        toggleSections();
    });

    function toggleSections() {
        if ($('#source_driver').is(':checked')) {
            $('#driverSection').show();
            $('#supplierSection').hide();
        } else if ($('#source_supplier').is(':checked')) {
            $('#supplierSection').show();
            $('#driverSection').hide();
        }

        if ($('#project_project').is(':checked')) {
            $('#projectSection').show();
        } else {
            $('#projectSection').hide();
        }
    }

    $('input[name="source_type"]').on('change', toggleSections);
    $('input[name="project_type"]').on('change', toggleSections);

    document.getElementById('addRow').addEventListener('click', function () {
        const wrapper = document.getElementById('materialsWrapper');
        const row = document.querySelector('.material-row').cloneNode(true);

        row.querySelectorAll('input').forEach(input => {
            let name = input.getAttribute('name');
            name = name.replace(/\[\d+\]/, `[${rowIndex}]`);
            input.setAttribute('name', name);
            input.value = '';
        });

        wrapper.appendChild(row);
        rowIndex++;
    });

    document.getElementById('removeRow').addEventListener('click', function () {
        const rows = document.querySelectorAll('.material-row');
        if (rows.length > 1) {
            rows[rows.length - 1].remove();
            rowIndex--;
        }
    });
</script>
@endpush
