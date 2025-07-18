<?php

namespace App\Http\Controllers;

use App\Models\Layanan;
use App\Models\Pesanan;
use App\Models\Pesanan_detail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class pembayaranController extends Controller
{
    public function lanjut($id_pesanan)
    {
        $pesanan = Pesanan::with([
            'details.hewan', // Ambil data hewan
            'details.layanan',
            'details.layanan_detail', // Ambil layanan dari detail
            'penyediaLayanan', // jika ingin info toko
        ])->findOrFail($id_pesanan);

         $biayaPotongan = $pesanan->total_biaya * 0.1;

         $biayaTotal = $pesanan->total_biaya + $biayaPotongan;

         $layanan = optional($pesanan->details->first())->layanan_detail;

         $tipe = strtolower(optional($pesanan->details->first()?->layanan_detail?->layanan)->tipe_input ?? 'lainnya');


        return view('page.User.pembayaran', compact('pesanan', 'layanan', 'tipe', 'biayaPotongan', 'biayaTotal'));
    }

    public function proses(Request $request)
    {
        $request->validate([
            'id_pesanan' => 'required|exists:pesanans,id',
            'metode_pembayaran' => 'required|string',
            'lokasi_awal' => 'nullable|string',
            'lokasi_tujuan' => 'nullable|string',
            'lokasi_kandang' => 'nullable|string',
            'bukti_bayar' => 'nullable|image|max:2048',
        ]);

        // Simpan data pembayaran di tabel 'pembayarans' (bisa disesuaikan)
        // Lalu arahkan ke halaman selesai

        return redirect()->route('home')->with('success', 'Pembayaran berhasil diproses.');
    }
    public function updateStatus(Request $request, $id)
    {
        $pesanan = Pesanan::findOrFail($id);
        $pesanan->status = 'menunggu diproses';
        $pesanan->save();

        return response()->json(['message' => 'Status diperbarui ke diproses.']);
    }

}
