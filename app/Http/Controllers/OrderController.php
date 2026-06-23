<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Order; // Tambahkan ini agar bisa menyimpan ke tabel orders
use Illuminate\Http\Request;

class FrontendController extends Controller
{
    // ... (kode index dan addToCart yang sudah ada biarkan saja) ...

    // Menampilkan halaman keranjang
    public function cart()
    {
        return view('customer.cart');
    }

    // Memproses checkout pesanan
    public function checkout(Request $request)
    {
        // Ambil isi keranjang dari session
        $cart = session()->get('cart');

        // Cegah checkout jika keranjang kosong
        if(!$cart) {
            return redirect()->route('home');
        }

        // Hitung total harga
        $totalPrice = 0;
        foreach($cart as $item) {
            $totalPrice += $item['price'] * $item['quantity'];
        }

        // Validasi input nama pelanggan
        $request->validate([
            'customer_name' => 'required|string|max:255'
        ]);

        // Simpan data pesanan ke database (Tabel orders)
        Order::create([
            'customer_name' => $request->customer_name,
            'total_price' => $totalPrice,
            'status' => 'Belum Bayar'
        ]);

        // Hapus isi keranjang setelah berhasil checkout
        session()->forget('cart');

        // Arahkan kembali ke beranda dengan pesan sukses
        return redirect()->route('home')->with('success', 'Pesanan berhasil dibuat!');
    }
}