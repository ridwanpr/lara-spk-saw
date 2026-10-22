<div class="modal fade" id="reject-ticket-modal" tabindex="-1" aria-labelledby="rejectTicketModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('repair-tickets.reject', $repairTicket->id) }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="rejectTicketModalLabel">Tolak Laporan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="alert alert-warning d-flex align-items-start" role="alert">
                        <div>
                            Menolak laporan ini akan mengakhiri proses penanganan tiket.
                            Pastikan Anda sudah memeriksa kondisi aset di lokasi.
                        </div>
                    </div>

                    <div class="mb-0">
                        <label class="form-label required">Alasan Penolakan</label>
                        <textarea name="rejection_reason" rows="3"
                            class="form-control @error('rejection_reason') is-invalid @enderror"
                            placeholder="Contoh: Kerusakan bukan akibat cacat produksi atau masih dalam masa garansi pabrik..."
                            required>{{ old('rejection_reason') }}</textarea>
                        @error('rejection_reason')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-danger">
                        Tolak Laporan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
