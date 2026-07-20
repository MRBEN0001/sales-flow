<?php

function format_uang ($angka) {
    return number_format($angka, 0, '.', ',');
}

function tenant_shop_login_url(string $subdomain, ?\Illuminate\Http\Request $request = null): string
{
    $request = $request ?? request();
    $tenantDomain = config('app.tenant_domain');
    $port = $request->getPort();
    $portSuffix = in_array($port, [80, 443], true) ? '' : ':'.$port;

    return $request->getScheme().'://'.$subdomain.'.'.$tenantDomain.$portSuffix.'/login';
}

/**
 * Absolute URL on the central domain (works even when called inside a tenant request).
 */
function central_url(string $path = '/'): string
{
    $request = request();
    $central = config('app.central_domain') ?: config('app.tenant_domain', 'localhost');
    $port = $request->getPort();
    $portSuffix = in_array((int) $port, [80, 443], true) ? '' : ':'.$port;
    $path = '/'.ltrim($path, '/');

    if ($path === '/') {
        $path = '';
    }

    return $request->getScheme().'://'.$central.$portSuffix.$path;
}

/**
 * Format a date/time in Nigeria (Africa/Lagos) using 12-hour clock.
 *
 * $withSeconds = true  => 20 Jul 2026, 3:15:51 PM
 * $withSeconds = false => 20 Jul 2026, 3:15 PM
 * $dateOnly    = true  => 20 Jul 2026
 */
function nigeria_datetime($value, bool $withSeconds = true, bool $dateOnly = false): string
{
    if (! $value) {
        return '—';
    }

    try {
        $date = $value instanceof \Carbon\Carbon
            ? $value->copy()
            : \Carbon\Carbon::parse($value);
    } catch (\Throwable $e) {
        return '—';
    }

    $date = $date->timezone(config('app.timezone', 'Africa/Lagos'));

    if ($dateOnly) {
        return $date->format('d M Y');
    }

    return $date->format($withSeconds ? 'd M Y, g:i:s A' : 'd M Y, g:i A');
}

// function terbilang ($angka) {
//     $angka = abs($angka);
//     $baca  = array('', "One",       "Two",      "Three",
//     "Four",    "Five",      "Six",      "Seven",
//     "Eight",   "Nine",      "Ten",      "Eleven",
//     "Twelve",  "Thirteen",  "Fourteen", "Fifteen",
//     "Sixteen", "Seventeen", "Eighteen", "Nineteen");
// visit "codeastro" for more projects!
//     $tens = array("",      "Twenty",  "Thirty", "Forty", "Fifty",
//     "Sixty", "Seventy", "Eighty", "Ninety");
//     $terbilang = '';

//     if ($angka < 20) { // 0 - 14
//         $terbilang = ' ' . $baca[$angka];
//     } 
//     elseif ($angka < 20) { // 14 - 19
//         $terbilang = terbilang($angka -10) . 'teen';
//     } 
//     elseif ($angka < 100) { // 20 - 99
//         $terbilang = terbilang($angka / 10) . ' tens' . terbilang($angka % 10);
//     } elseif ($angka < 200) { // 100 - 199
//         $terbilang = ' one hundred' . terbilang($angka -100);
//     } elseif ($angka < 1000) { // 200 - 999
//         $terbilang = terbilang($angka / 100) . ' hundred' . terbilang($angka % 100);
//     } elseif ($angka < 2000) { // 1.000 - 1.999
//         $terbilang = ' one thousand' . terbilang($angka -1000);
//     } elseif ($angka < 1000000) { // 2.000 - 999.999
//         $terbilang = terbilang($angka / 1000) . ' thousand' . terbilang($angka % 1000);
//     } elseif ($angka < 1000000000) { // 1000000 - 999.999.990
//         $terbilang = terbilang($angka / 1000000) . ' million' . terbilang($angka % 1000000);
//     }

//     return $terbilang;
// }
// visit "codeastro" for more projects!
// function terbilang($angka)
// {
//     $angka = abs($angka);
//     $baca = [
//         '', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten',
//         'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'
//     ];

//     $tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];

//     if ($angka < 20) {
//         $terbilang = ' ' . $baca[$angka];
//     } elseif ($angka < 100) {
//         $terbilang = ' ' . $tens[(int)($angka / 10)] . ' ' . terbilang($angka % 10);
//     } elseif ($angka < 1000) {
//         $terbilang = ' ' . $baca[(int)($angka / 100)] . ' Hundred' . terbilang($angka % 100);
//     } elseif ($angka < 1000000) {
//         $terbilang = terbilang((int)($angka / 1000)) . ' Thousand' . terbilang($angka % 1000);
//     } elseif ($angka < 1000000000) {
//         $terbilang = terbilang((int)($angka / 1000000)) . ' Million' . terbilang($angka % 1000000);
//     } elseif ($angka < 1000000000000) {
//         $terbilang = terbilang((int)($angka / 1000000000)) . ' Billion' . terbilang($angka % 1000000000);
//     } else {
//         $terbilang = 'Number is too large to convert.';
//     }

//     return $terbilang;
// }

function terbilang($angka)
{
    $angka = abs($angka);
    $baca = [
        '', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten',
        'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'
    ];

    $tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];

    if ($angka < 20) {
        $terbilang = ' ' . $baca[$angka];
    } elseif ($angka < 100) {
        $terbilang = ' ' . $tens[(int)($angka / 10)];
        if ($angka % 10 !== 0) {
            $terbilang .= ' ' . $baca[$angka % 10];
        }
    } elseif ($angka < 1000) {
        $terbilang = ' ' . $baca[(int)($angka / 100)] . ' Hundred';
        if ($angka % 100 !== 0) {
            $terbilang .= ' and' . terbilang($angka % 100);
        }
    } elseif ($angka < 1000000) {
        $terbilang = terbilang((int)($angka / 1000)) . ' Thousand';
        if ($angka % 1000 !== 0) {
            $terbilang .= terbilang($angka % 1000);
        }
    } elseif ($angka < 1000000000) {
        $terbilang = terbilang((int)($angka / 1000000)) . ' Million';
        if ($angka % 1000000 !== 0) {
            $terbilang .= terbilang($angka % 1000000);
        }
    } elseif ($angka < 1000000000000) {
        $terbilang = terbilang((int)($angka / 1000000000)) . ' Billion';
        if ($angka % 1000000000 !== 0) {
            $terbilang .= terbilang($angka % 1000000000);
        }
    } else {
        $terbilang = 'Number is too large to convert.';
    }

    return $terbilang;
}
// visit "codeastro" for more projects!
function tanggal_indonesia($tgl, $tampil_hari = true)
{
    $nama_hari  = array(
        'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'
    );
    $nama_bulan = array(1 =>
        'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'
    );

    $tahun   = substr($tgl, 0, 4);
    $bulan   = $nama_bulan[(int) substr($tgl, 5, 2)];
    $tanggal = substr($tgl, 8, 2);
    $text    = '';

    if ($tampil_hari) {
        $urutan_hari = date('w', mktime(0,0,0, substr($tgl, 5, 2), $tanggal, $tahun));
        $hari        = $nama_hari[$urutan_hari];
        $text       .= "$hari, $tanggal $bulan $tahun";
    } else {
        $text       .= "$tanggal $bulan $tahun";
    }
    
    return $text; 
}
// visit "codeastro" for more projects!
function tambah_nol_didepan($value, $threshold = null)
{
    return sprintf("%0". $threshold . "s", $value);
}

function subscription_monthly_price_ngn(): int
{
    return (int) config('subscription.monthly_price_ngn', 7500);
}

function subscription_yearly_discount_percent(): int
{
    return (int) config('subscription.yearly_discount_percent', 40);
}

/**
 * Full year at monthly rate (before discount).
 */
function subscription_yearly_full_price_ngn(): int
{
    return subscription_monthly_price_ngn() * 12;
}

/**
 * Yearly plan price after discount.
 */
function subscription_yearly_price_ngn(): int
{
    $full = subscription_yearly_full_price_ngn();
    $discount = subscription_yearly_discount_percent();

    return (int) round($full * (100 - $discount) / 100);
}

/**
 * Amount saved on the yearly plan vs paying monthly for 12 months.
 */
function subscription_yearly_savings_ngn(): int
{
    return subscription_yearly_full_price_ngn() - subscription_yearly_price_ngn();
}