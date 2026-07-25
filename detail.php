<?php
session_start();
require_once 'config/database.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM movies WHERE id = :id");
$stmt->execute(['id' => $id]);
$movie = $stmt->fetch();

if (!$movie) {
    header('Location: index.php');
    exit;
}

// Ambil jadwal untuk film ini
$stmtSched = $pdo->prepare("SELECT * FROM schedules WHERE movie_id = :movie_id ORDER BY show_time ASC");
$stmtSched->execute(['movie_id' => $id]);
$schedules = $stmtSched->fetchAll();

// --- TAMBAHKAN VARIABEL POSTER DENGAN SEED JUDUL ---
$poster = !empty($movie['poster']) 
    ? $movie['poster'] 
    : 'https://picsum.photos/seed/' . urlencode($movie['title']) . '/500/700';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo htmlspecialchars($movie['title']); ?> - Detail</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css" />
    <style>
        /* (style sama seperti sebelumnya, tidak diubah) */
        body { background-color: #f8f9fa; font-family: 'Segoe UI', sans-serif; }
        .detail-poster {
            border-radius: 16px;
            width: 100%;
            max-height: 500px;
            object-fit: cover;
            box-shadow: 0 8px 30px rgba(0,0,0,0.12);
        }
        .detail-info .badge-genre {
            background-color: #e9ecef;
            color: #1a1a2e;
            padding: 6px 16px;
            border-radius: 50px;
            font-weight: 500;
        }
        .detail-info .info-item {
            padding: 10px 0;
            border-bottom: 1px solid #f1f3f5;
        }
        .detail-info .info-item:last-child { border-bottom: none; }
        .btn-book {
            border-radius: 50px;
            padding: 12px 40px;
            font-weight: 700;
            font-size: 1.1rem;
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
        @media (max-width: 768px) {
            .detail-poster { max-height: 300px; margin-bottom: 20px; }
        }
    </style>
</head>
<body>

<!-- Navbar -->
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
    <div class="row g-4">
        <div class="col-md-5">
            <!-- GAMBAR POSTER DENGAN SEED JUDUL -->
            <img src="<?php echo htmlspecialchars($poster); ?>" 
                 class="detail-poster" 
                 alt="<?php echo htmlspecialchars($movie['title']); ?>" 
                 onerror="this.src='https://picsum.photos/seed/<?php echo urlencode($movie['title']); ?>/500/700'" />
        </div>
        <div class="col-md-7 detail-info">
            <h2 class="fw-bold mb-2"><?php echo htmlspecialchars($movie['title']); ?></h2>
            <div class="mb-3">
                <span class="badge-genre"><i class="bi bi-tag"></i> <?php echo htmlspecialchars($movie['genre']); ?></span>
                <span class="badge-genre ms-2"><i class="bi bi-clock"></i> <?php echo htmlspecialchars($movie['duration']); ?></span>
                <span class="badge-genre ms-2"><i class="bi bi-ticket"></i> Rp <?php echo number_format($movie['price'], 0, ',', '.'); ?></span>
            </div>
            <div class="info-item">
                <strong>Deskripsi</strong>
                <p class="mt-2"><?php echo nl2br(htmlspecialchars($movie['description'])); ?></p>
            </div>
            <div class="info-item">
                <strong>Jadwal Tayang</strong>
                <ul class="list-unstyled mt-2">
                    <?php if (count($schedules) > 0): ?>
                        <?php foreach ($schedules as $sched): ?>
                            <li><i class="bi bi-calendar-event"></i> <?php echo date('d M Y H:i', strtotime($sched['show_time'])); ?> - <?php echo htmlspecialchars($sched['studio']); ?></li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li class="text-muted">Belum ada jadwal tersedia.</li>
                    <?php endif; ?>
                </ul>
            </div>
            <div class="mt-4">
                <a href="reservation.php?movie_id=<?php echo $movie['id']; ?>" class="btn btn-primary btn-book">
                    <i class="bi bi-ticket-perforated"></i> Pesan Tiket
                </a>
                <a href="index.php" class="btn btn-outline-secondary rounded-pill px-4 ms-2">Kembali</a>
            </div>
        </div>
    </div>
</div>

<!-- Footer -->
<footer class="footer">
    <div class="container text-center">
        <p class="mb-0">&copy; <?php echo date('Y'); ?> CineReserve. All rights reserved.</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>