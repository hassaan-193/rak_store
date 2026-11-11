@extends('layouts.app')

@section('title', 'Edit Outgoing Material | FTS')

@section('content')

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Edit Outgoing Material</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('outgoing-materials.index') }}">Outgoing Materials</a></li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <form method="POST" action="{{ route('outgoing-materials.update', $outgoingMaterial->id) }}">
            @csrf
            @method('PUT')
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
                                            <input type="datetime-local" name="date_time" class="form-control" value="{{ old('date_time', $outgoingMaterial->date_time->format('Y-m-d\TH:i')) }}" required>

                                        </div>
                                    </div>

                                    <div class="col-4">
                                        <div class="form-group">
                                            <label for="driver_id">Driver</label>
                                            <select class="form-control select2" name="driver_id" required>
                                                <option value="">Select Driver</option>
                                                @foreach ($drivers as $id => $name)
                                                    <option value="{{ $id }}" {{ old('driver_id', $outgoingMaterial->driver_id) == $id ? 'selected' : '' }}>{{ $name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-4">
                                        <div class="form-group">
                                            <label for="project_type">Project Type</label>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="project_type" value="general" id="project_general" {{ old('project_type', $outgoingMaterial->project_type) == 'general' ? 'checked' : '' }}>
                                                <label class="form-check-label" for="project_general">General</label>
                                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                <input class="form-check-input" type="radio" name="project_type" value="project" id="project_project" {{ old('project_type', $outgoingMaterial->project_type) == 'project' ? 'checked' : '' }}>
                                                <label class="form-check-label" for="project_project">Project</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Project selection -->
                                <div class="row mt-2" id="projectSection" style="{{ $outgoingMaterial->project_type == 'project' ? '' : 'display:none' }}">
                                    <div class="col-4">
                                        <div class="form-group">
                                            <label>Project Name</label>
                                            <select class="form-control select2" name="project_id" id="project_id">
                                                <option value="">Select Project</option>
                                                @foreach ($projects as $id => $name)
                                                    <option value="{{ $id }}" {{ old('project_id', $outgoingMaterial->project_id) == $id ? 'selected' : '' }}>{{ $name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-2">
                                    <div class="col-4">
                                        <div class="form-group">
                                            <label>Engineer Name</label>
                                            <input type="text" name="engineer_name" class="form-control" value="{{ old('engineer_name', $outgoingMaterial->engineer_name) }}" required>
                                        </div>
                                    </div>



                                    <div class="col-4">
                                        <div class="form-group">
                                            <label>DN Number</label>
                                            <input type="text" name="dn_number" class="form-control" value="{{ old('dn_number', $outgoingMaterial->dn_number) }}" required>
                                        </div>
                                    </div>
                                    <div class="col-4" id="siteSection" style="{{ $outgoingMaterial->project_type == 'general' ? '' : 'display:none' }}">
                                        <div class="form-group">
                                            <label>Site</label>
                                            <input type="text" name="site" class="form-control" value="{{ old('site', $outgoingMaterial->site) }}">
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Materials Section -->
                    <div class="col-12">
                        <div class="card card-primary">
                            <div class="card-header border-0 ui-sortable-handle">
                                <h3 class="card-title">Materials</h3>
                            </div>

                            <div class="card-body" id="materialsWrapper">
                                @foreach ($outgoingMaterial->items as $item)
                                    <div class="row">
                                        <div class="col-4">
                                            <input type="text" class="form-control" name="materials[{{ $loop->index }}][item_name]" value="{{ $item->item_name }}" required>
                                        </div>
                                        <div class="col-4">
                                            <input type="text" class="form-control" name="materials[{{ $loop->index }}][category]" value="{{ $item->category }}" required>
                                        </div>
                                        <div class="col-4">

                                        <input type="text" name="materials[{{ $loop->index }}][unit]" value="{{ old('materials.'.$loop->index.'.unit', $item->unit) }}">
                                        </div>

                                        <div class="col-4">
                                            <input type="number" class="form-control" name="materials[{{ $loop->index }}][quantity]" value="{{ $item->quantity }}" required>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">Update</button>
                        <a href="{{ route('outgoing-materials.index') }}" class="btn btn-default">Cancel</a>
                    </div>
                </div>
            </div>
        </form>
    </section>
</div>

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Show/hide project section on page load
        if ($('#project_project').is(':checked')) {
            $('#projectSection').show();
            $('#siteSection').hide();
        } else {
            $('#projectSection').hide();
            $('#siteSection').show();
        }


        // Load materials immediately on page load
        loadMaterials();

        // Handle project type change
            $('input[name="project_type"]').change(function() {
                if ($('#project_project').is(':checked')) {
                    $('#projectSection').show();
                    $('#siteSection').hide(); // hide site when Project is selected
                } else {
                    $('#projectSection').hide();
                    $('#siteSection').show(); // show site when General is selected
                    $('#project_id').val(''); // Reset project selection if switching to General
                }
                loadMaterials();
            });


        // Handle project dropdown change
        $('#project_id').change(function() {
            loadMaterials();
        });

        // Function to load materials
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
                                let existingMaterial = existingMaterials.find(m => m.item_name === item.item_name && m.category === item.category);
                                let outgoingQty = existingMaterial ? existingMaterial.quantity : 0;
                                html += `<tr>
                                    <td>${item.item_name}<input type="hidden" name="materials[${index}][item_name]" value="${item.item_name}"></td>
                                    <td>${item.category}<input type="hidden" name="materials[${index}][category]" value="${item.category}"></td>
                                    <td>${item.unit}<input type="hidden" name="materials[${index}][unit]" value="${item.unit}"></td>
                                    <td>${item.quantity}</td>
                                    <td><input type="number" name="materials[${index}][quantity]" class="form-control" max="${item.quantity}" min="0" value="${outgoingQty}"></td>
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
</script>

<script>
    // Send existing outgoing material items from server to JS
    let existingMaterials = @json($outgoingMaterial->items ?? []);
</script>

@endpush
