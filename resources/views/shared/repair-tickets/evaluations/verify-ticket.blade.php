<div class="modal fade" id="verify-ticket-modal" tabindex="-1" aria-labelledby="verifyTicketModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('repair-tickets.verify', $repairTicket->id) }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="verifyTicketModalLabel">Pemeriksaan & Verifikasi Laporan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    {{-- Input Estimasi Biaya --}}
                    <div class="mb-3">
                        <label class="form-label required">Estimasi Biaya</label>
                        <input type="number" name="estimated_cost"
                            class="form-control @error('estimated_cost') is-invalid @enderror"
                            placeholder="Masukkan nominal estimasi..." value="{{ old('estimated_cost') }}" autofocus
                            required>
                        @error('estimated_cost')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Select Option Tingkat Kerusakan --}}
                    @php
                        $damageCriteria = $criterias->firstWhere('code', 'C1');
                    @endphp

                    @if ($damageCriteria)
                        <div class="mb-3">
                            <label class="form-label required">{{ $damageCriteria->name }}</label>
                            <select name="evaluations[{{ $damageCriteria->id }}]"
                                class="form-select @error('evaluations.' . $damageCriteria->id) is-invalid @enderror"
                                required>
                                <option value="">Pilih tingkat kerusakan...</option>
                                @foreach ($damageCriteria->subCriterias as $sub)
                                    <option value="{{ $sub->id }}"
                                        {{ old('evaluations.' . $damageCriteria->id) == $sub->id ? 'selected' : '' }}>
                                        {{ $sub->description }}
                                    </option>
                                @endforeach
                            </select>
                            @error('evaluations.' . $damageCriteria->id)
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    @endif

                    {{-- Loop Kriteria Non-Sistem Tambahan --}}
                    @foreach ($criterias->where('is_system', false) as $criteria)
                        <div class="mb-3">
                            <label class="form-label required">{{ $criteria->name }}</label>

                            @if ($criteria->input_type->value === 'categorical')
                                <select name="evaluations[{{ $criteria->id }}]"
                                    class="form-select @error('evaluations.' . $criteria->id) is-invalid @enderror"
                                    required>
                                    <option value="">Pilih kondisi...</option>
                                    @foreach ($criteria->subCriterias as $sub)
                                        <option value="{{ $sub->id }}"
                                            {{ old('evaluations.' . $criteria->id) == $sub->id ? 'selected' : '' }}>
                                            {{ $sub->description }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('evaluations.' . $criteria->id)
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            @elseif ($criteria->input_type->value === 'range')
                                <input type="number" name="evaluations[{{ $criteria->id }}]"
                                    class="form-control @error('evaluations.' . $criteria->id) is-invalid @enderror"
                                    placeholder="Masukkan nilai {{ strtolower($criteria->name) }}..."
                                    value="{{ old('evaluations.' . $criteria->id) }}" required>
                                @error('evaluations.' . $criteria->id)
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            @endif
                        </div>
                    @endforeach
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-primary">
                        Verifikasi Tiket
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
