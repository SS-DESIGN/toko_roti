<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Order;
use Illuminate\Http\Request;

class FrontendController extends Controller
{
    public function index()
    {
        $products = Product::all();
        return view('customer.home', compact('products'));
    }

    public function addToCart(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $cart = session()->get('cart', []);

        if(isset($cart[$id])) {
            $cart[$id]['quantity']++;
        } else {
            $cart[$id] = [
                "name" => $product->name,
                "quantity" => 1,
                "price" => $product->price,
                "image_url" => $product->image_url
            ];
        }

        session()->put('cart', $cart);
        return redirect()->back();
    }

    public function cart()
    {
        return view('customer.cart');
    }

    // UPDATE: Proses checkout sekarang mengarahkan ke halaman pembayaran
    public function checkout(Request $request)
    {
        $cart = session()->get('cart');

        if(!$cart) {
            return redirect()->route('home');
        }

        $totalPrice = 0;
        foreach($cart as $item) {
            $totalPrice += $item['price'] * $item['quantity'];
        }

        $request->validate([
            'customer_name' => 'required|string|max:255'
        ]);

        // Membuat data pesanan awal dengan status 'Belum Bayar'
        $order = Order::create([
            'customer_name' => $request->customer_name,
            'total_price' => $totalPrice,
            'status' => 'Belum Bayar'
        ]);

        // Bersihkan keranjang belanja
        session()->forget('cart');

        // Alihkan pelanggan ke halaman pembayaran sesuai ID pesanan mereka
        return redirect()->route('cart.payment', $order->id);
    }

    // BARU: Menampilkan halaman pembayaran portal
    public function payment($id)
    {
        $order = Order::findOrFail($id);

        // Jika pesanan ternyata sudah dibayar, kembalikan ke beranda
        if ($order->status !== 'Belum Bayar') {
            return redirect()->route('home');
        }

        return view('customer.payment', compact('order'));
    }

    // BARU: Mengubah status 'Belum Bayar' menjadi 'Diproses' ketika tombol bayar diklik
    public function processPayment($id)
    {
        $order = Order::findOrFail($id);
        
        // Memperbarui status pesanan di database
        $order->update([
            'status' => 'Diproses'
        ]);

        // Kembalikan ke beranda dengan session flash message sukses
        return redirect()->route('home')->with('payment_success', 'Terima kasih! Pembayaran Anda berhasil diterima. Roti sedang disiapkan.');
    }
}