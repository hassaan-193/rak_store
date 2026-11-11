@extends('layouts.app')

@section('title', 'Create Incoming Material | FTS')

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Create Incoming Material</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">Create Incoming Material</li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
        <form method="POST" action="{{ route('incoming-materials.store') }}">
            @csrf
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="card card-primary">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-4">
                                        <div class="form-group">
                                            <label for="date_time">Date / Time</label>
                                            <input type="datetime-local" name="date_time" class="form-control" value="{{ old('date_time') }}" required>
                                        </div>
                                    </div>

                                    <div class="col-4">
                                        <div class="form-group">
                                            <label for="source_type">Incoming Material</label>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="source_type" value="driver" id="source_driver" 
                                                    {{ old('source_type', 'supplier') == 'driver' ? 'checked' : '' }}>
                                                <label class="form-check-label" for="source_driver">With FTS Driver</label>
                                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                <input class="form-check-input" type="radio" name="source_type" value="supplier" id="source_supplier" 
                                                    {{ old('source_type', 'supplier') == 'supplier' ? 'checked' : '' }}>
                                                <label class="form-check-label" for="source_supplier">Supplier Only</label>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Add General/Project Radio Options -->
                                    <div class="col-4">
                                        <div class="form-group">
                                            <label for="project_type">Project Type</label>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="project_type" value="general" id="project_general" {{ old('project_type') == 'general' ? 'checked' : '' }}>
                                                <label class="form-check-label" for="project_general">General</label>
                                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                <input class="form-check-input" type="radio" name="project_type" value="project" id="project_project" {{ old('project_type') == 'project' ? 'checked' : '' }}>
                                                <label class="form-check-label" for="project_project">Project</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Additional Row for Project Selection -->
                            <div class="row mt-2" id="projectSection" style="display: none;">
                                <div class="col-3">
                                    <div class="form-group">
                                        <label>Project Name</label>
                                        <select class="form-control select2" name="project_id" >
                                            <option value="">Select Project</option>
                                            @foreach ($projects as $project)
                                                <option value="{{ $project->id }}" {{ old('project_id') == $project->id ? 'selected' : '' }}>{{ $project->name }}</option>
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
                                                <option value="{{ $id }}" {{ old('driver_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-4" id="supplierSection">
                                    <div class="form-group">
                                        <label>Supplier</label>
                                        <select class="form-control select2" name="supplier_id">
                                            <option value="">Select Supplier</option>
                                            @foreach ($suppliers as $id => $name)
                                                <option value="{{ $id }}" {{ old('supplier_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-3">
                                    <div class="form-group">
                                        <label>Engineer Name</label>
                                        <input type="text" name="engineer_name" class="form-control" placeholder="Engineer Name" value="{{ old('engineer_name') }}" required>
                                    </div>
                                </div>

                                <div class="col-3">
                                    <div class="form-group">
                                        <label>Store Location</label>
                                        <input type="text" name="store_location" class="form-control" placeholder="Store Location" value="{{ old('store_location') }}" required>
                                    </div>
                                </div>

                                <div class="col-3">
                                    <div class="form-group">
                                        <label>DN Number</label>
                                        <input type="text" name="dn_number" class="form-control" placeholder="DN Number" value="{{ old('dn_number') }}" required>
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
                                <h3 class="card-title">Add Material</h3>
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
                                <div class="row material-row">
                                    <div class="col-3">
                                        <div class="form-group">
                                            <label>Item Name</label>
                                            <input type="text" name="materials[0][item_name]" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-3">
                                        <div class="form-group">
                                            <label>Category</label>
                                            <input type="text" name="materials[0][category]" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-2">
                                        <div class="form-group">
                                            <label>Brand</label>
                                            <input type="text" name="materials[0][brand]" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-2">
                                        <div class="form-group">
                                            <label>Unit</label>
                                            <input type="text" name="materials[0][unit]" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-2">
                                        <div class="form-group">
                                            <label>Quantity</label>
                                            <input type="number" name="materials[0][quantity]" class="form-control" required>
                                        </div>
                                    </div>
                                </div>
                            </div> <!-- /.card-body -->

                            <div class="card-body">
                                <button type="submit" class="btn btn-primary">Save</button>
                                <a href="{{ route('incoming-materials.index') }}" class="btn btn-default">Cancel</a>
                            </div>
                        </div>
                    </div>

                </div> <!-- /.row -->
            </div> <!-- /.container-fluid -->
        </form>
    </section>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->

@endsection

@push('scripts')
<link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>

<script>
    let rowIndex = 1;

    $(document).ready(function() {
        // Initialize Select2 for driver, supplier, and project dropdowns
        $('.select2').select2({
            placeholder: "Select an option",
            allowClear: true,
            width: '100%' // Ensure it takes full width
        });
    });

    document.getElementById('addRow').addEventListener('click', function () {
        const wrapper = document.getElementById('materialsWrapper');
        const row = document.querySelector('.material-row').cloneNode(true);

        // Update all name attributes to ensure unique input names
        row.querySelectorAll('input').forEach(input => {
            let name = input.getAttribute('name');
            name = name.replace(/\[\d+\]/, `[${rowIndex}]`);
            input.setAttribute('name', name);
            input.value = ''; // Clear value
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

    // Toggle visibility of the project selection based on the "Project" radio button selection
    document.querySelectorAll('input[name="project_type"]').forEach(input => {
        input.addEventListener('change', function () {
            if (document.getElementById('project_project').checked) {
                document.getElementById('projectSection').style.display = 'block';
            } else {
                document.getElementById('projectSection').style.display = 'none';
            }
        });
    });

    // Toggle the driver/supplier sections based on the radio selection
    document.querySelectorAll('input[name="source_type"]').forEach(input => {
        input.addEventListener('change', function () {
            if (document.getElementById('source_driver').checked) {
                document.getElementById('driverSection').style.display = 'block';
            } else {
                document.getElementById('driverSection').style.display = 'none';
            }
        });
    });
</script>
@endpush
