{{--
    Tabel Virtual Account milik siswa yang login.
    Nomor VA ditampilkan besar dan jelas supaya siswa mudah menyalin saat
    mau transfer.
--}}
<div class="overflow-hidden rounded-xl border border-garis dark:border-garis-gelap bg-white dark:bg-gelap-2 shadow-sm">
    <div class="border-b border-garis dark:border-garis-gelap px-5 py-4">
        <h2 class="text-sm font-semibold text-redup dark:text-redup-gelap">Virtual Account</h2>
        <p class="mt-1 text-xs text-redup dark:text-redup-gelap">
            Gunakan nomor ini untuk membayar SPP via transfer bank.
        </p>
    </div>

    @if ($daftar_va->isEmpty())
        <p class="px-5 py-8 text-center text-sm text-redup dark:text-redup-gelap">
            Anda belum punya Virtual Account.
        </p>
    @else
        <div class="divide-y divide-garis dark:divide-garis-gelap">
            @foreach ($daftar_va as $va)
                <div class="px-5 py-5">
                    {{-- Nomor VA ditampilkan besar dan jelas. --}}
                    <p class="text-xs font-medium uppercase tracking-wide text-redup dark:text-redup-gelap">Nomor Virtual Account</p>
                    @if ($va->account_number)
                        <p class="mt-1 font-mono text-3xl font-bold text-tinta dark:text-teks-gelap">
                            {{ $va->account_number }}
                        </p>
                        <p class="mt-1 text-sm text-redup dark:text-redup-gelap">
                            Nomor internal: {{ $va->nomor_va }}
                        </p>
                    @else
                        <p class="mt-1 font-mono text-3xl font-bold text-tinta dark:text-teks-gelap">
                            {{ $va->nomor_va }}
                        </p>
                    @endif

                    <dl class="mt-4 grid grid-cols-1 gap-3 text-sm sm:grid-cols-2">
                        @if ($va->bank_code)
                            <div>
                                <dt class="text-redup dark:text-redup-gelap">Bank</dt>
                                <dd class="text-redup dark:text-redup-gelap">{{ $va->bank_code }}</dd>
                            </div>
                        @endif
                        <div>
                            <dt class="text-redup dark:text-redup-gelap">Status</dt>
                            <dd>
                                @php
                                    $petaStatus = [
                                        'aktif' => ['label' => 'Aktif', 'kelas' => 'bg-neon text-tinta'],
                                        'nonaktif' => ['label' => 'Nonaktif', 'kelas' => 'bg-latar-2 dark:bg-gelap text-redup dark:text-redup-gelap'],
                                    ];
                                    $dataStatus = $petaStatus[$va->status] ?? ['label' => $va->status, 'kelas' => 'bg-latar-2 dark:bg-gelap text-redup dark:text-redup-gelap'];
                                @endphp
                                <span class="inline-flex whitespace-nowrap rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $dataStatus['kelas'] }}">
                                    {{ $dataStatus['label'] }}
                                </span>
                            </dd>
                        </div>
                        @if ($va->expired_at)
                            <div>
                                <dt class="text-redup dark:text-redup-gelap">Kedaluwarsa</dt>
                                <dd class="text-redup dark:text-redup-gelap">{{ $va->expired_at->format('d/m/Y H:i') }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            @endforeach
        </div>
    @endif
</div>
