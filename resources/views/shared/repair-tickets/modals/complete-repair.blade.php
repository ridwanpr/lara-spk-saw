<div class="modal fade" id="complete-repair-modal" tabindex="-1" aria-labelledby="completeRepairModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('repair-tickets.complete-repair', $repairTicket->id) }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="completeRepairModalLabel">Selesaikan Perbaikan Aset</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required">Biaya Realisasi (Rp)</label>
                        <input type="number" name="actual_cost" min="0" step="1"
                            class="form-control @error('actual_cost') is-invalid @enderror"
                            placeholder="Contoh: {{ $repairTicket->estimated_cost ? (int) $repairTicket->estimated_cost : 250000 }}"
                            value="{{ old('actual_cost', $repairTicket->actual_cost ?? ($repairTicket->estimated_cost ? (int) $repairTicket->estimated_cost : '')) }}"
                            required autofocus>
                        @error('actual_cost')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        @if ($repairTicket->estimated_cost !== null)
                            <small class="form-hint">
                                Estimasi awal: Rp {{ number_format((float) $repairTicket->estimated_cost, 0, ',', '.') }}
                            </small>
                        @endif
                    </div>

                    <div class="mb-0">
                        <label class="form-label">Catatan Perbaikan</label>
                        <textarea name="completion_notes" rows="3"
                            class="form-control @error('completion_notes') is-invalid @enderror"
                            placeholder="Tuliskan tindakan perbaikan yang dilakukan, penggantian suku cadang, atau garansi...">{{ old('completion_notes', $repairTicket->completion_notes) }}</textarea>
                        @error('completion_notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-hint">
                            Opsional. Catatan ini akan tersimpan dalam riwayat penanganan aset.
                        </small>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-success">
                        Simpan & Selesaikan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
