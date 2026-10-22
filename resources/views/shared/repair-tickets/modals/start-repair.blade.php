<div class="modal fade" id="start-repair-modal" tabindex="-1" aria-labelledby="startRepairModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('repair-tickets.start-repair', $repairTicket->id) }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="startRepairModalLabel">Mulai Proses Perbaikan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <p class="text-body mb-2">
                        Apakah Anda yakin ingin memulai proses perbaikan untuk aset ini?
                    </p>
                    <div class="card bg-muted-lt border-0 mb-0">
                        <div class="card-body p-3">
                            <div class="small text-secondary">Aset:</div>
                            <div class="fw-medium text-body">{{ $repairTicket->asset->name }} ({{ $repairTicket->asset->code }})</div>
                            <div class="small text-secondary mt-2">Estimasi Biaya:</div>
                            <div class="fw-medium text-body">
                                {{ $repairTicket->estimated_cost !== null ? 'Rp ' . number_format((float) $repairTicket->estimated_cost, 0, ',', '.') : '-' }}
                            </div>
                        </div>
                    </div>
                    <small class="text-secondary d-block mt-2">
                        Status aset akan diperbarui menjadi <strong>Dalam Perbaikan</strong>.
                    </small>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-primary">
                        Mulai Perbaikan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
