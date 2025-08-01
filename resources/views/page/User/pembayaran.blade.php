@extends('layout.main')

@section('content2')
<div class="container py-4">
    <div class="card shadow border-0">
        <div class="card-body">
            <h3 class="mb-4" style="color: #bb9587;">Pembayaran Layanan</h3>
            <div class="mb-3">
                <h5 class="text-secondary">Hewan yang Dipesan:</h5>
                <ul class="list-group">
                    @foreach ($pesanan->details as $detail)
                        <li class="list-group-item">
                            @if ($detail->hewan)
                                {{ $detail->hewan->nama_hewan }} - {{ $detail->hewan->jenis_hewan }}
                            @else
                                Tidak ada hewan
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="mb-3">
                <div class="d-flex justify-content-between">
                    <strong>Layanan:</strong>
                    <span>{{ optional($pesanan->details->first()?->layanan_detail)->nama_variasi ?? '-' }}</span>
                </div>
                <div class="d-flex justify-content-between">
                    <strong>Harga Layanan:</strong>
                    <span>Rp{{ number_format(optional($pesanan->details->first()?->layanan_detail)->harga_dasar ?? '-') }}</span>
                </div>

                @if($tipe === 'penitipan')
                    <div class="d-flex justify-content-between">
                        <strong>Jumlah Hari:</strong>
                        <span>{{ $pesanan->jumlah_hari ?? '-' }}</span>
                    </div>
                @elseif($tipe === 'antar jemput')
                    <div class="d-flex justify-content-between">
                        <strong>Estimasi Jarak:</strong>
                        <span>{{ $pesanan->total_jarak ?? '-' }} km</span>
                    </div>
                @elseif($tipe === 'lokasi kandang')
                    <div class="d-flex justify-content-between">
                        <strong>Luas Kandang:</strong>
                        <span>{{ $pesanan->luas_kandang ?? '-' }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <strong>Jumlah Kandang:</strong>
                        <span>{{ $pesanan->jumlah_kandang ?? '-' }}</span>
                    </div>
                @endif

                <div class="d-flex justify-content-between">
                    <strong>Biaya Layanan:</strong>
                    <span>Rp {{ number_format($pesanan->total_biaya) }}</span>
                </div>
                <div class="d-flex justify-content-between">
                    <strong>Biaya Penanganan:</strong>
                    <span>Rp {{ number_format($biayaPotongan) }}</span>
                </div>
                <hr>
                <div class="d-flex justify-content-between fw-bold" style="color: #bb9587;">
                    <strong>Total Harga:</strong>
                    <span>Rp {{ number_format($biayaTotal) }}</span>
                </div>
            </div>

            <form id="form-pembayaran">
                @csrf
                <input type="hidden" name="id_pesanan" value="{{ $pesanan->id }}">
                <div class="d-grid">
                    <button type="button" class="btn" id="midtrans-button" style="background-color: #bb9587; color: white;">
                        Bayar Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.clientKey') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const midtransBtn = document.getElementById('midtrans-button');

        midtransBtn.addEventListener('click', function (e) {
            e.preventDefault();

            fetch("{{ route('midtrans.bayar', ['id_pesanan' => $pesanan->id]) }}")
                .then(async res => {
                    if (!res.ok) {
                        const text = await res.text();
                        console.error("Error Response:", text);
                        alert("Gagal mendapatkan Snap Token: " + res.status);
                        return;
                    }
                    return res.json();
                })
                .then(data => {
                    if (data && data.snap_token) {
                        snap.pay(data.snap_token, {
                            onSuccess: function(result) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Pembayaran Berhasil!',
                                    text: 'Silakan tunggu pesanan Anda diproses oleh penyedia layanan.',
                                    confirmButtonColor: '#bb9587',
                                }).then(() => {
                                    fetch("/pesanan/update-status/" + {{ $pesanan->id }}, {
                                        method: "POST",
                                        headers: {
                                            "X-CSRF-TOKEN": "{{ csrf_token() }}",
                                            "Content-Type": "application/json"
                                        }
                                    })
                                    .then(res => res.json())
                                    .then(response => {
                                        console.log(response.message);
                                        window.location.href = "/dashboard";
                                    })
                                    .catch(error => {
                                        console.error("Gagal update status:", error);
                                        window.location.href = "/dashboard";
                                    });
                                });
                            },
                            onPending: function(result) {
                                alert("Menunggu pembayaran...");
                                window.location.href = "/dashboard";
                            },
                            onError: function(result) {
                                alert("Pembayaran gagal!");
                                console.error(result);
                            },
                            onClose: function() {
                                alert("Transaksi dibatalkan.");
                            }
                        });
                    } else {
                        console.error("Respon tidak sesuai:", data);
                        alert("Gagal mendapatkan Snap Token (token tidak tersedia).");
                    }
                })
                .catch(err => {
                    alert("Gagal mendapatkan Snap Token (fetch error).");
                    console.error(err);
                });
        });
    });
</script>
@endsection
