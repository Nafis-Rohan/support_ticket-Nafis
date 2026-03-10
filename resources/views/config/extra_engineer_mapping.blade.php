@extends('layouts.app')

@section('content')
<h2 class="mb-4">Extra Engineer Mapping</h2>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<div class="card mb-4">
    <div class="card-body">
        <form method="POST" action="{{ route('config.extra_engineer_mapping.store') }}">
            @csrf

            <div class="row g-3 align-items-end mb-3">
                <div class="col-md-4">
                    <label class="form-label mb-1">Category<span class="text-danger">*</span></label>
                    <select id="extra-category" name="category_id" class="form-select form-select-sm" required>
                        <option value="">-- Select Category --</option>
                        @foreach($categories as $c)
                        <option value="{{ $c->id }}" data-role-ids="{{ $c->assign_role_ids }}"
                            {{ (isset($selectedCategoryId) && (int)$selectedCategoryId === (int)$c->id) ? 'selected' : '' }}>
                            {{ $c->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="card position-relative">
                <div class="card-body">
                    <div id="extra-rows">
                        <div class="row g-3 align-items-center extra-row mb-3">
                            <div class="col-md-5">
                                <label class="form-label mb-1">Engineer Name<span class="text-danger">*</span></label>
                                <select name="user_ids[]" class="form-select form-select-sm engineer-select">
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label mb-1">Hierarchy<span class="text-danger">*</span></label>
                                <input type="number" name="hierarchies[]" class="form-control form-control-sm" value="1"
                                    min="1">
                            </div>
                            <div class="col-md-2 d-flex justify-content-end align-items-center">
                                <button type="button" class="btn btn-danger btn-sm delete-row d-none">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <button type="button" id="add-extra-row"
                        class="btn btn-success rounded-circle d-flex align-items-center justify-content-center"
                        style="position: absolute; top: 16px; right: 16px; width: 36px; height: 36px;">
                        <i class="fas fa-plus text-white"></i>
                    </button>
                </div>
            </div>

            <div class="mt-3 text-end">
                <button type="submit" class="btn btn-primary btn-sm">Save</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const allEngineers = @json($engineers);
        const savedHierarchies = @json($hierarchies ?? []);
        const categorySelect = document.getElementById('extra-category');
        const rowsContainer = document.getElementById('extra-rows');
        const addRowButton = document.getElementById('add-extra-row');
        const mainEngineerSelect = null; // no top Designation To dropdown now
        const templateRow = rowsContainer.querySelector('.extra-row').cloneNode(true);

        function parseRoleIdsFromOption(option) {
            if (!option || !option.dataset.roleIds) {
                return [];
            }
            return option.dataset.roleIds
                .split(',')
                .map(s => parseInt(s.trim(), 10))
                .filter(n => !isNaN(n));
        }

        function filterEngineersByCategory(categoryId) {
            const opt = categorySelect.querySelector('option[value=\"' + categoryId + '\"]');
            const roleIds = parseRoleIdsFromOption(opt);
            if (!roleIds.length) {
                return [];
            }
            return allEngineers.filter(e => e.role_id !== null && roleIds.includes(parseInt(e.role_id, 10)));
        }

        function populateEngineerSelect(selectEl, engineers, includePlaceholder) {
            if (!selectEl) return;
            selectEl.innerHTML = '';
            if (includePlaceholder) {
                const placeholder = document.createElement('option');
                placeholder.value = '';
                placeholder.textContent = '-- Select Engineer --';
                selectEl.appendChild(placeholder);
            }
            engineers.forEach(e => {
                const opt = document.createElement('option');
                opt.value = e.id;
                opt.textContent = e.name;
                selectEl.appendChild(opt);
            });
        }

        function updateEngineerDropdowns() {
            const categoryId = categorySelect.value;
            const filtered = categoryId ? filterEngineersByCategory(categoryId) : [];

            // Rebuild rows from saved hierarchy for this category (if any)
            rowsContainer.innerHTML = '';
            const saved = savedHierarchies[categoryId] || [];
            const rowCount = saved.length > 0 ? saved.length : 1;

            for (let i = 0; i < rowCount; i++) {
                const newRow = templateRow.cloneNode(true);
                rowsContainer.appendChild(newRow);
            }

            // Populate all row dropdowns and apply saved selections
            const rows = rowsContainer.querySelectorAll('.extra-row');
            rows.forEach(function(row, index) {
                const selectEl = row.querySelector('.engineer-select');
                populateEngineerSelect(selectEl, filtered, true);

                const input = row.querySelector('input[type="number"]');
                const savedRow = saved[index];
                if (savedRow) {
                    if (selectEl) {
                        selectEl.value = String(savedRow.user_id);
                    }
                    if (input) {
                        input.value = savedRow.hierarchy;
                    }
                }

                // wire up delete button for all but first row
                const delBtn = row.querySelector('.delete-row');
                if (delBtn) {
                    delBtn.onclick = null;
                    if (index === 0) {
                        delBtn.classList.add('d-none');
                    } else {
                        delBtn.classList.remove('d-none');
                        delBtn.addEventListener('click', function() {
                            row.remove();
                            renumberHierarchies();
                        });
                    }
                }
            });

            renumberHierarchies();
        }

        function renumberHierarchies() {
            const rows = rowsContainer.querySelectorAll('.extra-row');
            rows.forEach(function(row, index) {
                const input = row.querySelector('input[type="number"]');
                if (input) {
                    input.value = index + 1;
                }
                const delBtn = row.querySelector('.delete-row');
                if (delBtn) {
                    if (index === 0) {
                        delBtn.classList.add('d-none');
                    } else {
                        delBtn.classList.remove('d-none');
                    }
                }
            });
        }

        categorySelect.addEventListener('change', updateEngineerDropdowns);

        if (addRowButton && rowsContainer) {
            addRowButton.addEventListener('click', function() {
                const existingRows = rowsContainer.querySelectorAll('.extra-row');
                if (!existingRows.length) return;

                const newRow = templateRow.cloneNode(true);

                const select = newRow.querySelector('.engineer-select');
                if (select) {
                    const categoryId = categorySelect.value;
                    const filtered = categoryId ? filterEngineersByCategory(categoryId) : [];
                    populateEngineerSelect(select, filtered, true);
                }
                const input = newRow.querySelector('input[type="number"]');
                if (input) {
                    input.value = existingRows.length + 1;
                }

                const delBtn = newRow.querySelector('.delete-row');
                if (delBtn) {
                    delBtn.classList.remove('d-none');
                    delBtn.addEventListener('click', function() {
                        newRow.remove();
                        renumberHierarchies();
                    });
                }

                rowsContainer.appendChild(newRow);
                renumberHierarchies();
            });
        }

        // Ensure first row delete button is hidden and hierarchy starts at 1
        renumberHierarchies();

        // Initial population if category already selected
        if (categorySelect.value) {
            updateEngineerDropdowns();
        }
    });
</script>

@endsection