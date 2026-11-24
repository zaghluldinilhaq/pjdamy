<?php
session_start();

/*
 * DAFTAR PRODUK
 * Tambahkan produk baru di dalam array ini dengan format di bawah.
 * ID harus unik (1, 2, 3, dst).
 *
 * Template:
 *
 * 1 => [
 *     "nama" => "Nama Produk",
 *     "kategori" => "Kategori",
 *     "harga" => 0,
 *     "satuan" => "Pcs",
 *     "gambar" => "URL_GAMBAR",
 *     "deskripsi" => "Deskripsi produk."
 * ],
 */
$produk = [

];

if (!isset($_SESSION["keranjang"])) {
    $_SESSION["keranjang"] = [];
}

/* Tambah produk */
if (isset($_POST["tambah"])) {
    $id = (int) $_POST["id"];
    $jumlah = max(1, (int) $_POST["jumlah"]);

    if (isset($produk[$id])) {
        $_SESSION["keranjang"][$id] =
            ($_SESSION["keranjang"][$id] ?? 0) + $jumlah;
    }

    header("Location: " . $_SERVER["PHP_SELF"] . "?status=ditambahkan");
    exit;
}

/* Update keranjang */
if (isset($_POST["update_keranjang"])) {
    foreach ($_POST["jumlah"] ?? [] as $id => $jumlah) {
        $id = (int) $id;
        $jumlah = (int) $jumlah;

        if ($jumlah <= 0) {
            unset($_SESSION["keranjang"][$id]);
        } elseif (isset($produk[$id])) {
            $_SESSION["keranjang"][$id] = $jumlah;
        }
    }

    header("Location: " . $_SERVER["PHP_SELF"] . "#keranjang");
    exit;
}

/* Hapus produk */
if (isset($_GET["hapus"])) {
    $id = (int) $_GET["hapus"];
    unset($_SESSION["keranjang"][$id]);

    header("Location: " . $_SERVER["PHP_SELF"] . "#keranjang");
    exit;
}

/* Kosongkan keranjang */
if (isset($_GET["kosongkan"])) {
    $_SESSION["keranjang"] = [];

    header("Location: " . $_SERVER["PHP_SELF"] . "#keranjang");
    exit;
}

/* Checkout */
$pesananBerhasil = false;

if (isset($_POST["checkout"])) {
    $nama = trim($_POST["nama"] ?? "");
    $telepon = trim($_POST["telepon"] ?? "");
    $alamat = trim($_POST["alamat"] ?? "");

    if ($nama !== "" && $telepon !== "" && $alamat !== "" && !empty($_SESSION["keranjang"])) {
        $pesananBerhasil = true;
        $_SESSION["keranjang"] = [];
    }
}

$totalItem = 0;
$totalHarga = 0;

foreach ($_SESSION["keranjang"] as $id => $jumlah) {
    if (isset($produk[$id])) {
        $totalItem += $jumlah;
        $totalHarga += $produk[$id]["harga"] * $jumlah;
    }
}

function rupiah($angka) {
    return "Rp " . number_format($angka, 0, ",", ".");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SegarMart - Penjualan Buah Segar</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background:
                linear-gradient(rgba(0, 0, 0, .50), rgba(0, 0, 0, .50)),
                url('https://images.unsplash.com/photo-1610832958506-aa56368176cf?auto=format&fit=crop&w=2000&q=85');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            min-height: 100vh;
            padding: 25px;
            color: #1f2937;
        }

        .main-container {
            width: 100%;
            max-width: 1250px;
            margin: auto;
        }

        .header {
            background: rgba(255, 255, 255, .96);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 18px 28px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 10px 30px rgba(0,0,0,.15);
            position: sticky;
            top: 15px;
            z-index: 100;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 18px;
        }

        .logo {
            width: 65px;
            height: 65px;
            border-radius: 16px;
            object-fit: cover;
        }

        .header-text h1 {
            color: #166534;
            font-size: 1.8rem;
            margin-bottom: 3px;
        }

        .header-text p {
            color: #64748b;
            font-size: .9rem;
        }

        .cart-badge {
            background: linear-gradient(135deg, #16a34a, #15803d);
            color: white;
            padding: 12px 18px;
            border-radius: 50px;
            font-weight: 700;
            text-decoration: none;
            box-shadow: 0 6px 18px rgba(22,163,74,.3);
        }

        .hero {
            background: rgba(255,255,255,.95);
            border-radius: 25px;
            padding: 38px;
            margin-bottom: 25px;
            box-shadow: 0 15px 40px rgba(0,0,0,.15);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 25px;
        }

        .hero-text {
            max-width: 700px;
        }

        .hero h2 {
            color: #14532d;
            font-size: 2.2rem;
            margin-bottom: 12px;
        }

        .hero p {
            color: #64748b;
            line-height: 1.8;
        }

        .hero-icon {
            font-size: 95px;
        }

        .alert {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #86efac;
            padding: 15px 20px;
            border-radius: 15px;
            margin-bottom: 20px;
            font-weight: 600;
        }

        .section-title {
            color: white;
            font-size: 1.5rem;
            margin: 25px 0 15px;
            text-shadow: 0 2px 5px rgba(0,0,0,.4);
        }

        .produk-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }

        .product-card {
            background: rgba(255,255,255,.96);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 12px 30px rgba(0,0,0,.15);
            transition: .3s;
        }

        .product-card:hover {
            transform: translateY(-6px);
        }

        .product-image {
            width: 100%;
            height: 210px;
            object-fit: cover;
        }

        .product-body {
            padding: 20px;
        }

        .category {
            display: inline-block;
            background: #dcfce7;
            color: #166534;
            padding: 5px 10px;
            border-radius: 30px;
            font-size: .75rem;
            font-weight: 600;
            margin-bottom: 9px;
        }

        .product-body h3 {
            color: #14532d;
            font-size: 1.15rem;
            margin-bottom: 7px;
        }

        .description {
            color: #64748b;
            font-size: .82rem;
            min-height: 43px;
            line-height: 1.6;
        }

        .price {
            color: #15803d;
            font-size: 1.2rem;
            font-weight: 800;
            margin: 13px 0;
        }

        .product-form {
            display: flex;
            gap: 8px;
        }

        .quantity {
            width: 65px;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            padding: 10px;
            text-align: center;
            font-family: inherit;
        }

        .btn {
            border: none;
            border-radius: 11px;
            padding: 11px 15px;
            font-family: inherit;
            font-weight: 600;
            cursor: pointer;
            transition: .25s;
        }

        .btn:hover {
            transform: translateY(-2px);
        }

        .btn-success {
            background: linear-gradient(135deg, #16a34a, #15803d);
            color: white;
            flex: 1;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .btn-primary {
            background: #2563eb;
            color: white;
        }

        .cart-section,
        .checkout-section {
            background: rgba(255,255,255,.96);
            border-radius: 25px;
            padding: 25px;
            margin-top: 25px;
            box-shadow: 0 15px 40px rgba(0,0,0,.15);
        }

        .cart-section h2,
        .checkout-section h2 {
            color: #14532d;
            margin-bottom: 20px;
        }

        .cart-table {
            width: 100%;
            border-collapse: collapse;
        }

        .cart-table th,
        .cart-table td {
            padding: 13px 10px;
            border-bottom: 1px solid #e2e8f0;
            text-align: left;
        }

        .cart-table th {
            color: #166534;
            background: #f0fdf4;
        }

        .cart-product {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .cart-product img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 12px;
        }

        .total-box {
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            padding: 18px;
            background: #f0fdf4;
            border-radius: 15px;
        }

        .total-box strong {
            color: #166534;
            font-size: 1.35rem;
        }

        .checkout-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 7px;
            font-weight: 600;
            color: #334155;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            border: 1px solid #cbd5e1;
            border-radius: 11px;
            padding: 12px;
            font-family: inherit;
            outline: none;
        }

        .form-group textarea {
            min-height: 120px;
            resize: vertical;
        }

        .summary {
            background: #f8fafc;
            border-radius: 16px;
            padding: 20px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e2e8f0;
        }

        .summary-total {
            display: flex;
            justify-content: space-between;
            padding-top: 15px;
            font-size: 1.25rem;
            font-weight: 800;
            color: #15803d;
        }

        .empty {
            text-align: center;
            color: #64748b;
            padding: 25px;
        }

        .empty-produk {
            background: rgba(255,255,255,.96);
            border-radius: 20px;
            box-shadow: 0 12px 30px rgba(0,0,0,.15);
        }

        .footer {
            text-align: center;
            color: white;
            margin: 25px 0 5px;
            font-size: .9rem;
        }

        @media (max-width: 900px) {
            .produk-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .checkout-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 650px) {
            body {
                padding: 12px;
            }

            .header {
                position: static;
                flex-direction: column;
                text-align: center;
            }

            .header-left {
                flex-direction: column;
            }

            .hero {
                flex-direction: column;
                text-align: center;
                padding: 25px;
            }

            .hero h2 {
                font-size: 1.7rem;
            }

            .produk-grid {
                grid-template-columns: 1fr;
            }

            .cart-table {
                display: block;
                overflow-x: auto;
            }

            .total-box {
                flex-direction: column;
                align-items: stretch;
                text-align: center;
            }
        }
    </style>
</head>

<body>
<div class="main-container">

    <header class="header">
        <div class="header-left">
            <img
                class="logo"
                src="https://images.unsplash.com/photo-1611080626919-7cf5a9dbab5b?auto=format&fit=crop&w=200&q=80"
                alt="Logo"
            >

            <div class="header-text">
                <h1>SegarMart</h1>
                <p>Website Penjualan Buah Segar</p>
            </div>
        </div>

        <a href="#keranjang" class="cart-badge">
            🛒 Keranjang (<?= $totalItem ?>)
        </a>
    </header>

    <?php if ($pesananBerhasil): ?>
        <div class="alert">
            ✅ Pesanan berhasil dibuat. Terima kasih telah berbelanja di SegarMart!
        </div>
    <?php endif; ?>

    <section class="hero">
        <div class="hero-text">
            <h2>Buah Segar, Berkualitas, dan Siap Dikirim 🍊</h2>
            <p>
                Pilih berbagai buah segar favorit Anda dengan harga terjangkau.
                Tambahkan ke keranjang dan lakukan pemesanan dengan mudah.
            </p>
        </div>
        <div class="hero-icon">🍎🍊🍌</div>
    </section>

    <h2 class="section-title">🍉 Produk Buah</h2>

    <?php if (empty($produk)): ?>

        <div class="empty empty-produk">
            <div style="font-size:55px;">📦</div>
            <p>Belum ada produk.</p>
        </div>

    <?php else: ?>

        <section class="produk-grid">
            <?php foreach ($produk as $id => $item): ?>
                <article class="product-card">
                    <img
                        class="product-image"
                        src="<?= htmlspecialchars($item["gambar"]) ?>"
                        alt="<?= htmlspecialchars($item["nama"]) ?>"
                    >

                    <div class="product-body">
                        <span class="category">
                            <?= htmlspecialchars($item["kategori"]) ?>
                        </span>

                        <h3><?= htmlspecialchars($item["nama"]) ?></h3>

                        <p class="description">
                            <?= htmlspecialchars($item["deskripsi"]) ?>
                        </p>

                        <div class="price">
                            <?= rupiah($item["harga"]) ?> / <?= htmlspecialchars($item["satuan"]) ?>
                        </div>

                        <form method="POST" class="product-form">
                            <input type="hidden" name="id" value="<?= $id ?>">

                            <input
                                class="quantity"
                                type="number"
                                name="jumlah"
                                value="1"
                                min="1"
                            >

                            <button
                                type="submit"
                                name="tambah"
                                class="btn btn-success"
                            >
                                🛒 Tambah
                            </button>
                        </form>
                    </div>
                </article>
            <?php endforeach; ?>
        </section>

    <?php endif; ?>

    <section class="cart-section" id="keranjang">
        <h2>🛒 Keranjang Belanja</h2>

        <?php if (empty($_SESSION["keranjang"])): ?>

            <div class="empty">
                <div style="font-size:55px;">🛒</div>
                <p>Keranjang masih kosong.</p>
                <p>Silakan pilih produk yang ingin dibeli.</p>
            </div>

        <?php else: ?>

            <form method="POST">
                <div style="overflow-x:auto;">
                    <table class="cart-table">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th>Harga</th>
                                <th>Jumlah</th>
                                <th>Subtotal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>

                        <tbody>
                        <?php foreach ($_SESSION["keranjang"] as $id => $jumlah): ?>
                            <?php if (!isset($produk[$id])) continue; ?>

                            <?php
                                $item = $produk[$id];
                                $subtotal = $item["harga"] * $jumlah;
                            ?>

                            <tr>
                                <td>
                                    <div class="cart-product">
                                        <img
                                            src="<?= htmlspecialchars($item["gambar"]) ?>"
                                            alt="<?= htmlspecialchars($item["nama"]) ?>"
                                        >
                                        <strong><?= htmlspecialchars($item["nama"]) ?></strong>
                                    </div>
                                </td>

                                <td><?= rupiah($item["harga"]) ?></td>

                                <td>
                                    <input
                                        class="quantity"
                                        type="number"
                                        min="0"
                                        name="jumlah[<?= $id ?>]"
                                        value="<?= $jumlah ?>"
                                    >
                                </td>

                                <td>
                                    <strong><?= rupiah($subtotal) ?></strong>
                                </td>

                                <td>
                                    <a
                                        class="btn btn-danger"
                                        href="?hapus=<?= $id ?>#keranjang"
                                        onclick="return confirm('Hapus produk ini dari keranjang?')"
                                    >
                                        Hapus
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="total-box">
                    <div>
                        Total <?= $totalItem ?> item
                        <br>
                        <strong><?= rupiah($totalHarga) ?></strong>
                    </div>

                    <div style="display:flex; gap:10px; flex-wrap:wrap;">
                        <button
                            type="submit"
                            name="update_keranjang"
                            class="btn btn-primary"
                        >
                            🔄 Update Keranjang
                        </button>

                        <a
                            href="?kosongkan=1#keranjang"
                            class="btn btn-danger"
                            onclick="return confirm('Kosongkan semua isi keranjang?')"
                        >
                            🗑 Kosongkan
                        </a>
                    </div>
                </div>
            </form>

        <?php endif; ?>
    </section>

    <?php if (!empty($_SESSION["keranjang"])): ?>
        <section class="checkout-section" id="checkout">
            <h2>📦 Checkout</h2>

            <div class="checkout-grid">

                <form method="POST">
                    <div class="form-group">
                        <label>Nama Lengkap</label>
                        <input
                            type="text"
                            name="nama"
                            placeholder="Masukkan nama lengkap"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label>Nomor WhatsApp</label>
                        <input
                            type="tel"
                            name="telepon"
                            placeholder="08xxxxxxxxxx"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label>Alamat Pengiriman</label>
                        <textarea
                            name="alamat"
                            placeholder="Masukkan alamat lengkap"
                            required
                        ></textarea>
                    </div>

                    <button
                        type="submit"
                        name="checkout"
                        class="btn btn-success"
                        style="width:100%; padding:14px;"
                    >
                        ✅ Buat Pesanan
                    </button>
                </form>

                <div class="summary">
                    <h3 style="color:#14532d; margin-bottom:12px;">
                        Ringkasan Pesanan
                    </h3>

                    <?php foreach ($_SESSION["keranjang"] as $id => $jumlah): ?>
                        <?php if (!isset($produk[$id])) continue; ?>

                        <div class="summary-row">
                            <span>
                                <?= htmlspecialchars($produk[$id]["nama"]) ?>
                                × <?= $jumlah ?>
                            </span>

                            <strong>
                                <?= rupiah($produk[$id]["harga"] * $jumlah) ?>
                            </strong>
                        </div>
                    <?php endforeach; ?>

                    <div class="summary-total">
                        <span>Total</span>
                        <span><?= rupiah($totalHarga) ?></span>
                    </div>
                </div>

            </div>
        </section>
    <?php endif; ?>

    <footer class="footer">
        © 2026 SegarMart - Website Penjualan Buah Segar
    </footer>

</div>
</body>
</html>