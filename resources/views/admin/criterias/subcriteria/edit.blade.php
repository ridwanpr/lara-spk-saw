<div class="modal fade" id="modal-edit-subcriteria" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="form-edit-subcriteria" method="POST" action="{{ old('edit_url', '') }}">
            @csrf
            @method('PUT')

            {{-- Maintain the URL of the form update when the validation redirect fails --}}
            <input type="hidden" name="edit_url" id="edit-form-url" value="{{ old('edit_url') }}">

            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Sub Kriteria</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required">Deskripsi</label>
                        <input type="text" name="edit_description" id="edit-description"
                            class="form-control @error('edit_description') is-invalid @enderror"
                            value="{{ old('edit_description') }}">
                        @error('edit_description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label required">Nilai Crisp</label>
                        <input type="number" step="any" min="1" max="5" name="edit_crisp_value"
                            id="edit-crisp" class="form-control @error('edit_crisp_value') is-invalid @enderror"
                            value="{{ old('edit_crisp_value') }}">
                        @error('edit_crisp_value')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    @if ($criteria->input_type->value === 'range')
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label required">Nilai Min</label>
                                <input type="number" step="any" name="edit_min_value" id="edit-min"
                                    class="form-control @error('edit_min_value') is-invalid @enderror"
                                    value="{{ old('edit_min_value') }}">
                                @error('edit_min_value')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label required">Nilai Max</label>
                                <input type="number" step="any" name="edit_max_value" id="edit-max"
                                    class="form-control @error('edit_max_value') is-invalid @enderror"
                                    value="{{ old('edit_max_value') }}">
                                @error('edit_max_value')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-link link-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </div>
        </form>
    </div>
</div>

@if ($errors->hasAny(['edit_description', 'edit_crisp_value', 'edit_min_value', 'edit_max_value']))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modalElement = document.getElementById('modal-edit-subcriteria');
            if (modalElement && window.Modal) {
                const modal = new window.Modal(modalElement);
                modal.show();
            }
        });
    </script>
@endif
