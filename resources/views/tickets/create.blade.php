@extends('layouts.app')

@section('content')
<div class="container py-5 d-flex justify-content-center">
    <div class="card shadow-sm border-0 rounded-lg" style="max-width: 746px; width: 100%;">
        <div class="card-header bg-primary text-white text-center py-3">
            <h4 class="mb-0"><i class="fa fa-ticket-alt mr-2"></i> Create Ticket</h4>
        </div>

        <div class="card-body px-4 py-4">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('tickets.store') }}" enctype="multipart/form-data">
                @csrf
                @php $isAdmin = auth()->user()->role === 1; @endphp

                @if($isAdmin)
                <div class="form-group mb-3">
                    <label for="branch_id" class="form-label label-bg">Branch</label>
                    <select name="branch_id" id="branch_id" class="form-control form-control-sm custom-select" required>
                        <option value="">-- Select Branch --</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                @if($isAdmin)
                <div class="form-group mb-3">
                    <label for="priority_id" class="form-label label-bg">Priority</label>
                    <select name="priority_id" id="priority_id" class="form-control form-control-sm custom-select" required>
                        <option value="">-- Select Priority --</option>
                        @foreach($priorities as $priority)
                            <option value="{{ $priority->id }}">{{ $priority->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

{{-- Category --}}
<div class="form-group mb-3">
    <label for="category_id" class="form-label label-bg">Category</label>

    @if(isset($selectedCategory))
        {{-- Read-only category field when opened from dashboard --}}
        <div class="readonly-wrapper d-flex align-items-center">
            <input type="hidden" name="category_id" value="{{ $selectedCategory }}">
            <input type="text"
                   class="form-control form-control-sm readonly-field"
                   value="{{ $categories->firstWhere('id', $selectedCategory)->name ?? 'Selected Category' }}"
                   readonly>
        </div>
    @else
        {{-- Normal dropdown if user opens form directly --}}
        <select name="category_id" id="category_id" class="form-control form-control-sm custom-select" required>
            <option value="">-- Select Category --</option>
            @foreach($categories as $category)
                <option value="{{ $category->id }}"
                    {{ old('category_id') == $category->id ? 'selected' : '' }}>
                    {{ $category->name }}
                </option>
            @endforeach
        </select>
    @endif
</div>

{{-- Sub Category --}}
<div class="form-group mb-3">
    <label for="sub_category_id" class="form-label label-bg">Sub Category</label>

    <select name="sub_category_id"
            id="sub_category_id"
            class="form-control form-control-sm custom-select">
        <option value="">-- Select Sub Category --</option>
        {{-- Options will be loaded dynamically via JS --}}
    </select>
</div>


                <div class="form-group mb-3">
                    <label for="subject" class="form-label label-bg">Subject</label>
                    <input type="text" name="subject" id="subject" class="form-control form-control-sm" placeholder="Enter short title..." required>
                </div>

                <div class="form-group mb-3">
                    <label for="description" class="form-label label-bg">Description</label>
                    <textarea name="description" id="description" class="form-control form-control-sm" rows="3" placeholder="Describe the issue..." required></textarea>
                </div>

                <div class="form-group mb-3">
                    <label for="contact_person" class="form-label label-bg">Contact Person</label>
                    <input type="text" name="contact_person" id="contact_person" class="form-control form-control-sm" placeholder="Name and Contact No.">
                </div>

                <div class="form-group mb-4">
                    <label class="form-label label-bg">Attach Files (optional)</label>
                    <div id="attachment-fields">
                        <input type="file" name="attachments[]" class="form-control form-control-sm mb-2 attachment-input" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.xlsx">
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary mb-2" id="addAttachmentBtn">+ Add another file</button>
                    <small class="text-muted d-block">Allowed: jpg, png, pdf, doc, docx, xlsx (max 2 MB each). Use the button to add more files.</small>
                </div>

                <div class="text-center">
                    <button type="submit" class="btn btn-primary btn-sm px-4">
                        <i class="fa fa-paper-plane mr-2"></i> Submit Ticket
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Compact Blue & Grey Styling --}}
<style>
body {
    background-color: #f5f8fb;
}

.card {
    border-radius: 10px;
}

/* Header */
.card-header {
    background: rgba(var(--bs-info-rgb), var(--bs-bg-opacity)) !important;
}

/* Label styling */
.label-bg {
    display: inline-block;
    background-color: #f1f1f1;
    color: #333;
    font-weight: 600;
    padding: 4px 10px;
    border-radius: 5px;
    margin-bottom: 6px;
}

/* Inputs smaller & neat */
.form-control-sm,
.custom-select {
    font-size: 0.9rem;
    padding: 0.4rem 0.6rem;
    border-radius: 6px;
}

.form-control-sm:focus,
.custom-select:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.15rem rgba(0,123,255,0.25);
}

/* Button */
.btn-primary {
    border: none;
    background: rgb(4 206 240);
    border-radius: 6px;
    transition: all 0.25s ease;
}

.btn-primary:hover {
    background: linear-gradient(90deg, #009eb3ff, #00b0cfff);
    transform: translateY(-2px);
}

/* Compact form spacing */
.form-group {
    margin-bottom: 0.9rem;
}

.card-body {
    padding: 1.5rem 2rem;
}
/* Read-only category styling */
.readonly-field {
    background-color: #e9ecef !important; /* light grey background */
    color: ##494949; /* muted text */
    border: 1px solid #ced4da;
    cursor: not-allowed;
}

.readonly-wrapper {
    display: flex;
    align-items: center;
}

}

</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const categorySelect    = document.getElementById('category_id');
    const subCategorySelect = document.getElementById('sub_category_id');

    function resetSubCategories() {
        if (!subCategorySelect) return;
        subCategorySelect.innerHTML = '<option value="">-- Select Sub Category --</option>';
    }

    function loadSubCategories(categoryId, preselectedId = null) {
        if (!subCategorySelect) return;

        resetSubCategories();

        if (!categoryId) {
            subCategorySelect.disabled = true;
            return;
        }

        subCategorySelect.disabled = false;

        fetch(`{{ url('/sub-categories') }}/${categoryId}`)
            .then(res => res.json())
            .then(data => {
                data.forEach(subCat => {
                    const option = document.createElement('option');
                    option.value = subCat.id;
                    option.text  = subCat.name;
                    subCategorySelect.appendChild(option);
                });

                // Preselect after all options are added
                if (preselectedId) {
                    subCategorySelect.value = String(preselectedId);
                }
            })
            .catch(err => console.error('Error loading subcategories:', err));
    }

    const selectedCategory    = @json($selectedCategory ?? null);
    const selectedSubCategory = @json($selectedSubCategory ?? null);
    const oldCategory         = @json(old('category_id'));
    const oldSubCategory      = @json(old('sub_category_id'));

    // Case 1: coming from dashboard with ?category=&sub_category=
    if (selectedCategory) {
        loadSubCategories(selectedCategory, selectedSubCategory ?? oldSubCategory);
    }

    // Case 2: normal create page, user picks category
    if (categorySelect) {
        categorySelect.addEventListener('change', function () {
            loadSubCategories(this.value);
        });

        if (oldCategory) {
            loadSubCategories(oldCategory, oldSubCategory);
        }
    }

    const addAttachmentBtn = document.getElementById('addAttachmentBtn');
    const attachmentFields = document.getElementById('attachment-fields');
    if (addAttachmentBtn && attachmentFields) {
        addAttachmentBtn.addEventListener('click', function () {
            const input = document.createElement('input');
            input.type = 'file';
            input.name = 'attachments[]';
            input.className = 'form-control form-control-sm mb-2 attachment-input';
            input.setAttribute('accept', '.jpg,.jpeg,.png,.pdf,.doc,.docx,.xlsx');
            attachmentFields.appendChild(input);
        });
    }
});
</script>






@endsection
