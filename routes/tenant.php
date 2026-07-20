<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PembelianController;
use App\Http\Controllers\PenjualanController;
use App\Http\Controllers\PengeluaranController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\PembelianDetailController;
use App\Http\Controllers\PenjualanDetailController;

Route::middleware(['tenant'])->group(function () {
    Route::get('/login', [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'create'])
        ->middleware('guest')
        ->name('login');
    Route::post('/login', [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'store'])
        ->middleware('guest');
    Route::post('/logout', [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'destroy'])
        ->middleware('auth')
        ->name('logout');

    Route::get('/subscription/expired', [SubscriptionController::class, 'expired'])
        ->name('subscription.expired');
    Route::post('/subscription/checkout', [SubscriptionController::class, 'checkout'])
        ->name('subscription.checkout');

    Route::get('/', function () {
        return redirect()->route('login');
    });

    Route::middleware('trial')->group(function () {
        Route::group(['middleware' => 'auth'], function () {
            Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

            Route::get('/produk', [ProdukController::class, 'index'])->name('produk.index');
            Route::get('/produk/data', [ProdukController::class, 'data'])->name('produk.data');
            Route::get('/produk/take-stock', [ProdukController::class, 'takeStockPDF'])->name('produk.take_stock');
            Route::post('/produk/{id}/add-stock', [ProdukController::class, 'addStock'])->name('produk.add_stock');
            Route::post('/produk/add-product', [ProdukController::class, 'store'])->name('produk.add_product');

            Route::group(['middleware' => 'level:1,2'], function () {
                Route::get('/penjualan', [PenjualanController::class, 'index'])->name('penjualan.index');
                Route::get('/penjualan/data', [PenjualanController::class, 'data'])->name('penjualan.data');
                Route::get('/penjualan/daily-sales', [PenjualanController::class, 'dailySales'])->name('penjualan.daily_sales');
                Route::get('/penjualan/daily-sales/pdf', [PenjualanController::class, 'dailySalesPDF'])->name('penjualan.daily_sales_pdf');
                Route::get('/penjualan/daily-room-sales', [PenjualanController::class, 'dailyRoomSales'])->name('penjualan.daily_room_sales');
                Route::get('/penjualan/daily-room-sales/pdf', [PenjualanController::class, 'dailyRoomSalesPDF'])->name('penjualan.daily_room_sales_pdf');
                Route::get('/penjualan/monthly-report', [PenjualanController::class, 'monthlyReportPDF'])->name('penjualan.monthly_report');
                Route::get('/penjualan/weekly-report', [PenjualanController::class, 'weeklyReportPDF'])->name('penjualan.weekly_report');
                Route::get('/penjualan/{id}/resume', [PenjualanController::class, 'resume'])->name('penjualan.resume');
                Route::get('/penjualan/{id}', [PenjualanController::class, 'show'])->name('penjualan.show');
            });

            Route::group(['middleware' => 'level:1'], function () {
                Route::get('/kategori/data', [KategoriController::class, 'data'])->name('kategori.data');
                Route::resource('/kategori', KategoriController::class);

                Route::get('/section/data', [SectionController::class, 'data'])->name('section.data');
                Route::resource('/section', SectionController::class);

                Route::post('/produk/delete-selected', [ProdukController::class, 'deleteSelected'])->name('produk.delete_selected');
                Route::post('/produk/cetak-barcode', [ProdukController::class, 'cetakBarcode'])->name('produk.cetak_barcode');

                Route::resource('/products', ProdukController::class)->names([
                    'index' => 'produk.admin.index',
                    'store' => 'produk.store',
                    'show' => 'produk.show',
                    'update' => 'produk.update',
                    'destroy' => 'produk.destroy',
                    'create' => 'produk.create',
                    'edit' => 'produk.edit',
                ]);

                Route::get('/member/data', [MemberController::class, 'data'])->name('member.data');
                Route::post('/member/cetak-member', [MemberController::class, 'cetakMember'])->name('member.cetak_member');
                Route::resource('/member', MemberController::class);

                Route::get('/supplier/data', [SupplierController::class, 'data'])->name('supplier.data');
                Route::resource('/supplier', SupplierController::class);

                Route::get('/pengeluaran/data', [PengeluaranController::class, 'data'])->name('pengeluaran.data');
                Route::resource('/pengeluaran', PengeluaranController::class);

                Route::get('/pembelian/data', [PembelianController::class, 'data'])->name('pembelian.data');
                Route::get('/pembelian/{id}/create', [PembelianController::class, 'create'])->name('pembelian.create');
                Route::resource('/pembelian', PembelianController::class)->except('create');

                Route::get('/pembelian_detail/{id}/data', [PembelianDetailController::class, 'data'])->name('pembelian_detail.data');
                Route::get('/pembelian_detail/loadform/{diskon}/{total}', [PembelianDetailController::class, 'loadForm'])->name('pembelian_detail.load_form');
                Route::resource('/pembelian_detail', PembelianDetailController::class)->except('create', 'show', 'edit');

                Route::delete('/penjualan/{id}', [PenjualanController::class, 'destroy'])->name('penjualan.destroy');
            });

            Route::group(['middleware' => 'level:1,2'], function () {
                Route::get('/transaksi/baru', [PenjualanController::class, 'create'])->name('transaksi.baru');
                Route::post('/transaksi/simpan', [PenjualanController::class, 'store'])->name('transaksi.simpan');
                Route::get('/transaksi/selesai', [PenjualanController::class, 'selesai'])->name('transaksi.selesai');
                Route::get('/transaksi/nota-kecil', [PenjualanController::class, 'notaKecil'])->name('transaksi.nota_kecil');
                Route::get('/transaksi/nota-besar', [PenjualanController::class, 'notaBesar'])->name('transaksi.nota_besar');
                Route::get('/transaksi/{id}/data', [PenjualanDetailController::class, 'data'])->name('transaksi.data');
                Route::get('/transaksi/loadform/{diskon}/{total}/{diterima}', [PenjualanDetailController::class, 'loadForm'])->name('transaksi.load_form');
                Route::resource('/transaksi', PenjualanDetailController::class)->except('create', 'show', 'edit');
            });

            Route::group(['middleware' => 'level:1'], function () {
                Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
                Route::get('/laporan/data/{awal}/{akhir}', [LaporanController::class, 'data'])->name('laporan.data');
                Route::get('/laporan/pdf/{awal}/{akhir}', [LaporanController::class, 'exportPDF'])->name('laporan.export_pdf');

                Route::get('/user/data', [UserController::class, 'data'])->name('user.data');
                Route::resource('/user', UserController::class);

                Route::get('/setting', [SettingController::class, 'index'])->name('setting.index');
                Route::get('/setting/first', [SettingController::class, 'show'])->name('setting.show');
                Route::post('/setting', [SettingController::class, 'update'])->name('setting.update');
            });

            Route::group(['middleware' => 'level:1,2'], function () {
                Route::get('/profil', [UserController::class, 'profil'])->name('user.profil');
                Route::post('/profil', [UserController::class, 'updateProfil'])->name('user.update_profil');
            });

            Route::get('/produk/barcode/all', [ProdukController::class, 'viewAllBarcode'])->name('produk.barcode.all');
        });
    });
});
