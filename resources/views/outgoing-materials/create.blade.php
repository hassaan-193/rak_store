@extends('layouts.app')

@section('title', 'Create Outgoing Material | FTS')

@section('content')

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Create Outgoing Material</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">Create Outgoing Material</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <form method="POST" action="{{ route('outgoing-materials.store') }}">
            @csrf

            <div class="d-flex justify-content-between align-items-center p-2 mb-3 bg-light border sticky-top" style="z-index: 999;">
            <div>
            </div>
            <div style="width: 250px;">
                <input type="text" id="materialSearch" class="form-control" placeholder="Search material...">
            </div>
        </div>
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">

                        <!-- General Info -->
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
                                            <label for="driver_id">Driver</label>
                                            <select class="form-control select2" name="driver_id" required>
                                                <option value="">Select Driver</option>
                                                @foreach ($drivers as $id => $name)
                                                    <option value="{{ $id }}" {{ old('driver_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

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

                                <!-- Project selection -->
                                <div class="row mt-2" id="projectSection" style="display: none;">
                                    <div class="col-4">
                                        <div class="form-group">
                                            <label>Project Name</label>
                                            <select class="form-control select2" name="project_id" id="project_id">
                                                <option value="">Select Project</option>
                                                @foreach ($projects as $project)
                                                    <option value="{{ $project->id }}" {{ old('project_id') == $project->id ? 'selected' : '' }}>{{ $project->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-2">
                                    <div class="col-4">
                                        <div class="form-group">
                                            <label>Engineer Name</label>
                                            <input type="text" name="engineer_name" class="form-control" value="{{ old('engineer_name') }}" required>
                                        </div>
                                    </div>



                                    <div class="col-4">
                                        <div class="form-group">
                                            <label>DN Number</label>
                                            <input type="text" name="dn_number" class="form-control" value="{{ old('dn_number') }}" required>
                                        </div>
                                    </div>

                                    <div class="col-4" id="siteSection" style="display: none;">
                                        <div class="form-group">
                                            <label for="site">Site</label>
                                            <input type="text" name="site" class="form-control" value="{{ old('site') }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-12">
                                        <a href="{{ route('outgoing-materials.index') }}" class="btn btn-secondary">Cancel</a>
                                        <button type="submit" class="btn btn-primary">Save</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Materials Section -->
                    <div class="col-12">
                        <div class="card card-primary">
                            <div class="card-header border-0 ui-sortable-handle">
                                <h3 class="card-title">Select Materials</h3>
                            </div>

                            <div class="card-body" id="materialsWrapper">
                                <p>Select a project/general to load materials here...</p>
                            </div>

                            <div class="card-body">
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
        $(document).ready(function() {
        // Initialize Select2 for driver, supplier, and project dropdowns
        $('.select2').select2({
            placeholder: "Select an option",
            allowClear: true,
            width: '100%' // Ensure it takes full width
        });
    });
$(document).ready(function() {
    // Handle project type change
    $('input[name="project_type"]').change(function() {
        if ($('#project_project').is(':checked')) {
            $('#projectSection').show();
        } else {
            $('#projectSection').hide();
            $('#project_id').val('').trigger('change'); // Reset project dropdown
            $('#materialsWrapper').html('<p>Select a project/general to load materials here...</p>'); // Clear materials
        }
        loadMaterials();
    });

    // When project selection changes, reload materials
    $('#project_id').change(function() {
        loadMaterials();
    });

    // Load materials based on project selection
    function loadMaterials() {
        let projectType = $('input[name="project_type"]:checked').val();
        let projectId = $('#project_id').val();

        if (projectType) {
            $.ajax({
                url: "{{ route('outgoing-materials.get-materials') }}",
                type: "GET",
                data: {
                    project_type: projectType,
                    project_id: projectId
                },
                success: function(data) {
                    let html = '';
                    if (data.length > 0) {
                        html += '<table class="table table-bordered"><thead><tr><th>Item Name</th><th>Category</th><th>Unit</th><th>Available Qty</th><th>Outgoing Qty</th></tr></thead><tbody>';
                        data.forEach((item, index) => {
                            html += `<tr>
                                <td>${item.item_name}<input type="hidden" name="materials[${index}][item_name]" value="${item.item_name}"></td>
                                <td>${item.category}<input type="hidden" name="materials[${index}][category]" value="${item.category}"></td>
                                <td>${item.unit}<input type="hidden" name="materials[${index}][unit]" value="${item.unit}"></td>
                                <td>${item.quantity}</td>
                                <td><input type="number" name="materials[${index}][quantity]" class="form-control" max="${item.quantity}" min="0" value="0"></td>
                            </tr>`;
                        });
                        html += '</tbody></table>';
                    } else {
                        html = '<p>No materials available.</p>';
                    }
                    $('#materialsWrapper').html(html);

                        let currentSearch = $('#materialSearch').val().toLowerCase();
    if (currentSearch) {
        filterMaterials(currentSearch);
    }
                }
            });
        }
    }
});




$(document).ready(function() {
    // Handle project type change
    $('input[name="project_type"]').change(function() {
        if ($('#project_project').is(':checked')) {
            $('#projectSection').show();
            $('#siteSection').hide(); // hide site when project selected
        } else {
            $('#projectSection').hide();
            $('#siteSection').show(); // show site when general selected
            $('#project_id').val('').trigger('change'); // Reset project dropdown
            $('#materialsWrapper').html('<p>Select a project/general to load materials here...</p>'); // Clear materials
        }
        loadMaterials();
    });

    // Trigger on page load (in case old input is set)
    $('input[name="project_type"]:checked').trigger('change');

    // When project selection changes, reload materials
    $('#project_id').change(function() {
        loadMaterials();
    });

    // Load materials based on project selection
    function loadMaterials() {
        let projectType = $('input[name="project_type"]:checked').val();
        let projectId = $('#project_id').val();

        if (projectType) {
            $.ajax({
                url: "{{ route('outgoing-materials.get-materials') }}",
                type: "GET",
                data: {
                    project_type: projectType,
                    project_id: projectId
                },
                success: function(data) {
                    let html = '';
                    if (data.length > 0) {
                        html += '<table class="table table-bordered"><thead><tr><th>Item Name</th><th>Category</th><th>Unit</th><th>Available Qty</th><th>Outgoing Qty</th></tr></thead><tbody>';
                        data.forEach((item, index) => {
                            html += `<tr>
                                <td>${item.item_name}<input type="hidden" name="materials[${index}][item_name]" value="${item.item_name}"></td>
                                <td>${item.category}<input type="hidden" name="materials[${index}][category]" value="${item.category}"></td>
                                <td>${item.unit}<input type="hidden" name="materials[${index}][unit]" value="${item.unit}"></td>
                                <td>${item.quantity}</td>
                                <td><input type="number" name="materials[${index}][quantity]" class="form-control" max="${item.quantity}" min="0" value="0"></td>
                            </tr>`;
                        });
                        html += '</tbody></table>';
                    } else {
                        html = '<p>No materials available.</p>';
                    }
                    $('#materialsWrapper').html(html);
                }
            });
        }
    }
});


$(document).on('keyup', '#materialSearch', function() {
    let value = $(this).val().toLowerCase();
    filterMaterials(value);
});

function filterMaterials(searchValue) {
    $("#materialsWrapper table tbody tr").each(function() {
        $(this).toggle($(this).text().toLowerCase().indexOf(searchValue) > -1);
    });
}



</script>
@endpush
