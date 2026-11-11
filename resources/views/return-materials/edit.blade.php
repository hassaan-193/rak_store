@extends('layouts.app')

@section('title', 'Edit Return Material')

@section('content')

<div class="content-wrapper">
    <section class="content-header">
        <h1>Edit Return Material</h1>
    </section>

    <section class="content">
        <form method="POST" action="{{ route('return-materials.update', $returnMaterial->id) }}">
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
                                            <input type="datetime-local" name="date_time" class="form-control"  value="{{ \Carbon\Carbon::parse($returnMaterial->date_time)->format('Y-m-d\TH:i') }}"
                                            required>
                                            
                                        </div>
                                    </div>

                                    <div class="col-4">
                                        <div class="form-group">
                                            <label for="driver_id">Driver</label>
                                            <select class="form-control select2" name="driver_id" required>
                                                <option value="">Select Driver</option>
                                                @foreach ($drivers as $id => $name)
                                                    <option value="{{ $id }}" {{ $returnMaterial->driver_id == $id ? 'selected' : '' }}>{{ $name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-4">
                                        <div class="form-group">
                                            <label for="project_type">Project Type</label>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="project_type" value="general" id="project_general" {{ $returnMaterial->project_type == 'general' ? 'checked' : '' }}>
                                                <label class="form-check-label" for="project_general">General</label>
                                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                <input class="form-check-input" type="radio" name="project_type" value="project" id="project_project" {{ $returnMaterial->project_type == 'project' ? 'checked' : '' }}>
                                                <label class="form-check-label" for="project_project">Project</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Project selection -->
                                <div class="row mt-2" id="projectSection" style="{{ $returnMaterial->project_type == 'project' ? 'display: block;' : 'display: none;' }}">
                                    <div class="col-4">
                                        <div class="form-group">
                                            <label>Project Name</label>
                                            <select class="form-control select2" name="project_id" id="project_id">
                                                <option value="">Select Project</option>
                                                @foreach ($projects as $project)
                                                    <option value="{{ $project->id }}" {{ $returnMaterial->project_id == $project->id ? 'selected' : '' }}>{{ $project->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-2">
                                    <div class="col-4">
                                        <div class="form-group">
                                            <label>Return Reason</label>
                                            <input type="text" name="return_reason" class="form-control" value="{{ old('return_reason', $returnMaterial->return_reason) }}" required>
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
                                <h3 class="card-title">Selected Materials</h3>
                            </div>

                            <div class="card-body" id="materialsWrapper">
                                <!-- Populate existing materials -->
                                @foreach ($returnMaterial->items as $item)
                                    <div class="row">
                                        <div class="col-3">
                                            <input type="text" class="form-control" value="{{ $item->item_name }}" readonly>
                                        </div>
                                        <div class="col-2">
                                            <input type="text" class="form-control" value="{{ $item->category }}" readonly>
                                        </div>
                                        <div class="col-2">
                                            <input type="text" class="form-control" value="{{ $item->unit }}" readonly>
                                        </div>
                                        <div class="col-2">
                                            <input type="number" class="form-control" name="materials[{{ $loop->index }}][quantity]" value="{{ $item->quantity }}" min="0" max="{{ $item->quantity }}">
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="card-body">
                                <button type="submit" class="btn btn-primary">Save</button>
                                <a href="{{ route('return-materials.index') }}" class="btn btn-default">Cancel</a>
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
<script>
    $(document).ready(function() {
        // Handle project type change
        $('input[name="project_type"]').change(function() {
            if ($('#project_project').is(':checked')) {
                $('#projectSection').show();
            } else {
                $('#projectSection').hide();
            }
            loadMaterials();
        });

        // Load materials based on project selection
        function loadMaterials() {
            let projectType = $('input[name="project_type"]:checked').val();
            let projectId = $('#project_id').val();

            if (projectType) {
                $.ajax({
                    url: "{{ route('return-materials.get-materials') }}",
                    type: "GET",
                    data: {
                        project_type: projectType,
                        project_id: projectId
                    },
                    success: function(data) {
                        let html = '';
                        if (data.length > 0) {
                            html += '<table class="table table-bordered"><thead><tr><th>Item Name</th><th>Category</th><th>Unit</th><th>Available Qty</th><th>Return Qty</th></tr></thead><tbody>';
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
</script>
@endpush
