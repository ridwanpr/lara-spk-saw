    <div class="modal" id="create-subcriteria-modal" tabindex="-1">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Sub Kriteria</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.criterias.sub-criterias.store', $criteria) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label required">Nama Sub Kriteria</label>
                            <input type="text" name="description"
                                class="form-control @error('description') is-invalid @enderror"
                                value="{{ old('description') }}">
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label required">Nilai Crisp</label>
                            <input type="number" min="1" max="5" placeholder="Nilai 1-5"
                                name="crisp_value" class="form-control @error('crisp_value') is-invalid @enderror"
                                value="{{ old('crisp_value') }}">
                            @error('crisp_value')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        @if ($criteria->input_type->value === 'range')
                            <div class="mb-3">
                                <label class="form-label required">Nilai Minimum</label>
                                <input type="number" name="min_value"
                                    class="form-control @error('min_value') is-invalid @enderror"
                                    value="{{ old('min_value') }}">
                                @error('min_value')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label required">Nilai Maksimum</label>
                                <input type="number" name="max_value"
                                    class="form-control @error('max_value') is-invalid @enderror"
                                    value="{{ old('max_value') }}">
                                @error('max_value')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        @endif

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn me-auto" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @if ($errors->hasAny(['description', 'crisp_value', 'min_value', 'max_value']))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const modalElement = document.getElementById('create-subcriteria-modal');
                if (modalElement && window.Modal) {
                    const modal = new window.Modal(modalElement);
                    modal.show();
                }
            });
        </script>
    @endif
