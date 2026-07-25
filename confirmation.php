<?php
session_start();
require_once 'config/database.php';

// Cek apakah ada ID reservasi di session
if (!isset($_SESSION['reservation_id'])) {
    header('Location: index.php');
    exit;
}

$reservation_id = (int)$_SESSION['reservation_id'];

// Ambil data reservasi beserta detail film dan jadwal
$stmt = $pdo->prepare("
    SELECT r.*, m.title, m.poster, m.duration, m.genre, s.show_time, s.studio
    FROM reservations r
    JOIN movies m ON r.movie_id = m.id
    JOIN schedules s ON r.schedule_id = s.id
    WHERE r.id = :id
");
$stmt->execute(['id' => $reservation_id]);
$reservation = $stmt->fetch();

if (!$reservation) {
    // Hapus session dan redirect
    unset($_SESSION['reservation_id']);
    header('Location: index.php');
    exit;
}

// Hapus session agar tidak bisa diakses lagi (opsional)
// unset($_SESSION['reservation_id']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Konfirmasi Pemesanan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css" />
    <style>
        body { background-color: #f8f9fa; font-family: 'Segoe UI', sans-serif; }
        .confirmation-card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        .confirmation-card .card-header {
            background: linear-gradient(135deg, #198754, #20c997);
            color: white;
            padding: 24px 28px;
            font-weight: 700;
            font-size: 1.4rem;
            border: none;
        }
        .confirmation-card .card-body { padding: 30px; }
        .receipt-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px dashed #e9ecef;
        }
        .receipt-item:last-child { border-bottom: none; }
        .receipt-item .label { font-weight: 600; color: #495057; }
        .receipt-item .value { color: #1a1a2e; font-weight: 500; }
        .receipt-total {
            background: #f8f9fa;
            padding: 16px 20px;
            border-radius: 12px;
            font-size: 1.3rem;
            font-weight: 700;
            color: #198754;
        }
        .navbar-custom {
            background: #0d1b2a !important;
            padding: 12px 0;
        }
        .navbar-custom .navbar-brand {
            font-weight: 700;
            font-size: 1.4rem;
            color: white !important;
        }
        .navbar-custom .navbar-brand i { color: #ffc107; }
        .navbar-custom .nav-link {
            color: rgba(255,255,255,0.8) !important;
            font-weight: 500;
        }
        .navbar-custom .nav-link:hover { color: white !important; }
        .footer {
            background: #0d1b2a;
            color: rgba(255,255,255,0.7);
            padding: 30px 0;
            margin-top: 60px;
            border-radius: 30px 30px 0 0;
        }
        .footer a { color: rgba(255,255,255,0.7); text-decoration: none; }
        .footer a:hover { color: white; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-custom sticky-top">
    <div class="container">
        <a class="navbar-brand" href="index.php"><i class="bi bi-film"></i> CineReserve</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="index.php"><i class="bi bi-house-door"></i> Beranda</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php#movie-list"><i class="bi bi-grid"></i> Film</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="confirmation-card card">
                <div class="card-header text-center">
                    <i class="bi bi-check-circle-fill"></i> Pemesanan Berhasil!
                </div>
                <div class="card-body">
                    <h5 class="mb-4">Ringkasan Pemesanan</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="receipt-item"><span class="label">Kode Booking</span><span class="value">#<?php echo str_pad($reservation['id'], 6, '0', STR_PAD_LEFT); ?></span></div>
                            <div class="receipt-item"><span class="label">Nama Pemesan</span><span class="value"><?php echo htmlspecialchars($reservation['customer_name']); ?></span></div>
                            <div class="receipt-item"><span class="label">Telepon</span><span class="value"><?php echo htmlspecialchars($reservation['phone']); ?></span></div>
                            <div class="receipt-item"><span class="label">Email</span><span class="value"><?php echo htmlspecialchars($reservation['email']); ?></span></div>
                        </div>
                        <div class="col-md-6">
                            <div class="receipt-item"><span class="label">Film</span><span class="value"><?php echo htmlspecialchars($reservation['title']); ?></span></div>
                            <div class="receipt-item"><span class="label">Genre</span><span class="value"><?php echo htmlspecialchars($reservation['genre']); ?></span></div>
                            <div class="receipt-item"><span class="label">Durasi</span><span class="value"><?php echo htmlspecialchars($reservation['duration']); ?></span></div>
                            <div class="receipt-item"><span class="label">Jadwal</span><span class="value"><?php echo date('d M Y H:i', strtotime($reservation['show_time'])) . ' - ' . htmlspecialchars($reservation['studio']); ?></span></div>
                            <div class="receipt-item"><span class="label">Jumlah Tiket</span><span class="value"><?php echo $reservation['ticket_quantity']; ?></span></div>
                        </div>
                    </div>
                    <div class="receipt-total text-center mt-3">
                        Total Pembayaran: Rp <?php echo number_format($reservation['total_price'], 0, ',', '.'); ?>
                    </div>
                    <div class="text-center mt-4">
                        <a href="index.php" class="btn btn-success rounded-pill px-5 py-2 fw-bold">
                            <i class="bi bi-house-door"></i> Kembali ke Beranda
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<footer class="footer">
    <div class="container text-center">
        <p class="mb-0">&copy; <?php echo date('Y'); ?> CineReserve. All rights reserved.</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>