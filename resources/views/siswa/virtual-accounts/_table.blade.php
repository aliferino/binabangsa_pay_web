{{--
    Tabel Virtual Account milik siswa yang login.
    Nomor VA ditampilkan besar dan jelas supaya siswa mudah menyalin saat
    mau transfer.
--}}
<div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
    <div class="border-b border-slate-200 px-5 py-4">
        <h2 class="text-sm font-semibold text-slate-700">Virtual Account</h2>
        <p class="mt-1 text-xs text-slate-500">
            Gunakan nomor ini untuk membayar SPP via transfer bank.
        </p>
    </div>

    @if ($daftar_va->isEmpty())
        <p class="px-5 py-8 text-center text-sm text-slate-500">
            Anda belum punya Virtual Account.
        </p>
    @else
        <div class="divide-y divide-slate-100">
            @foreach ($daftar_va as $va)
                <div class="px-5 py-5">
                    {{-- Nomor VA ditampilkan besar dan jelas. --}}
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Nomor Virtual Account</p>
                    @if ($va->account_number)
                        <p class="mt-1 font-mono text-3xl font-bold text-slate-900">
                            {{ $va->account_number }}
                        </p>
                        <p class="mt-1 text-sm text-slate-500">
                            Nomor internal: {{ $va->nomor_va }}
                        </p>
                    @else
                        <p class="mt-1 font-mono text-3xl font-bold text-slate-900">
                            {{ $va->nomor_va }}
                        </p>
                    @endif

                    <dl class="mt-4 grid grid-cols-1 gap-3 text-sm sm:grid-cols-2">
                        @if ($va->bank_code)
                            <div>
                                <dt class="text-slate-500">Bank</dt>
                                <dd class="text-slate-700">{{ $va->bank_code }}</dd>
                            </div>
                        @endif
                        <div>
                            <dt class="text-slate-500">Status</dt>
                            <dd>
                                @php
                                    $petaStatus = [
                                        'aktif' => ['label' => 'Aktif', 'kelas' => 'bg-emerald-100 text-emerald-800'],
                                        'nonaktif' => ['label' => 'Nonaktif', 'kelas' => 'bg-slate-100 text-slate-700'],
                                    ];
                                    $dataStatus = $petaStatus[$va->status] ?? ['label' => $va->status, 'kelas' => 'bg-slate-100 text-slate-700'];
                                @endphp
                                <span class="inline-flex whitespace-nowrap rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $dataStatus['kelas'] }}">
                                    {{ $dataStatus['label'] }}
                                </span>
                            </dd>
                        </div>
                        @if ($va->expired_at)
                            <div>
                                <dt class="text-slate-500">Kedaluwarsa</dt>
                                <dd class="text-slate-700">{{ $va->expired_at->format('d/m/Y H:i') }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            @endforeach
        </div>
    @endif
</div>
