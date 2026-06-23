<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product; // Pastikan model Product dipanggil
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard()
    {
        $totalOrders = Order::count();
        $totalRevenue = Order::sum('total_price');
        $recentOrders = Order::latest()->take(10)->get(); 

        return view('admin.dashboard', compact('totalOrders', 'totalRevenue', 'recentOrders'));
    }

    // BARU: Menampilkan halaman Kelola Menu (Daftar & Form Tambah)
    public function products()
    {
        $products = Product::latest()->get(); // Mengambil katalog roti terbaru
        return view('admin.products', compact('products'));
    }

    // BARU: Menyimpan menu baru yang diinput Admin ke Database
    public function storeProduct(Request $request)
    {
        // Validasi input data dari form
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'image_url' => 'required|url',
            'description' => 'nullable|string'
        ]);

        // Query Insert data ke tabel products
        Product::create([
            'name' => $request->name,
            'price' => $request->price,
            'stock' => $request->stock,
            'image_url' => $request->image_url,
            'description' => $request->description,
        ]);

        // Kembalikan ke halaman kelola menu dengan alert sukses
        return redirect()->route('admin.products')->with('success', 'Menu roti baru berhasil ditambahkan!');
    }
}