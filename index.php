<?php
session_start();
require_once 'config/database.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Reservasi Tiket Bioskop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css" />
    <style>
        /* (sama seperti kode sebelumnya) */
        body { font-family: 'Segoe UI', sans-serif; background-color: #f8f9fa; }
        .hero-section {
            background: linear-gradient(135deg, #0d1b2a 0%, #1b3a5c 100%);
            color: white;
            padding: 80px 0;
            margin-bottom: 40px;
            border-radius: 0 0 30px 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        .hero-section h1 { font-size: 3.5rem; font-weight: 700; }
        .hero-section .lead { opacity: 0.9; }
        .hero-section .badge-movie {
            background-color: rgba(255,255,255,0.15);
            padding: 8px 20px;
            border-radius: 50px;
            font-size: 0.9rem;
        }
        .movie-card {
            border: none;
            border-radius: 16px;
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
            height: 100%;
            background: white;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }
        .movie-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 40px rgba(0,0,0,0.15);
        }
        .movie-card .card-img-top {
            height: 320px;
            object-fit: cover;
            background-color: #e9ecef;
        }
        .movie-card .card-body { padding: 1.25rem; }
        .movie-card .card-title { font-weight: 700; font-size: 1.1rem; }
        .movie-card .movie-meta { font-size: 0.85rem; color: #6c757d; }
        .movie-card .price-tag { font-weight: 700; color: #0d6efd; font-size: 1.1rem; }
        .movie-card .btn-detail { border-radius: 50px; padding: 6px 24px; font-weight: 600; }
        .search-section {
            background: white;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
            margin-bottom: 40px;
        }
        .search-section .input-group { max-width: 500px; margin: 0 auto; }
        .search-section .input-group input {
            border-radius: 50px 0 0 50px;
            padding: 12px 20px;
            border: 2px solid #e9ecef;
        }
        .search-section .input-group input:focus {
            border-color: #0d6efd;
            box-shadow: none;
        }
        .search-section .input-group button {
            border-radius: 0 50px 50px 0;
            padding: 12px 28px;
            font-weight: 600;
        }
        .empty-state { text-align: center; padding: 60px 20px; color: #6c757d; }
        .empty-state i { font-size: 4rem; opacity: 0.3; margin-bottom: 20px; }
        .footer {
            background: #0d1b2a;
            color: rgba(255,255,255,0.7);
            padding: 30px 0;
            margin-top: 60px;
            border-radius: 30px 30px 0 0;
        }
        .footer a { color: rgba(255,255,255,0.7); text-decoration: none; }
        .footer a:hover { color: white; }
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
        @media (max-width: 768px) {
            .hero-section h1 { font-size: 2.2rem; }
            .hero-section { padding: 50px 0; }
            .movie-card .card-img-top { height: 220px; }
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

<!-- Hero -->
<section class="hero-section">
    <div class="container text-center">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <span class="badge-movie d-inline-block mb-3">
                    <i class="bi bi-ticket-perforated"></i> Reservasi Tiket Online
                </span>
                <h1>CineReserve</h1>
                <p class="lead mb-4">
                    Pesan tiket bioskop favorit Anda dengan mudah, cepat, dan tanpa antre.
                    Temukan film terbaru, lihat jadwal, dan lakukan pemesanan dalam hitungan menit.
                </p>
                <a href="#movie-list" class="btn btn-light btn-lg rounded-pill px-5 py-3 fw-bold">
                    <i class="bi bi-arrow-down-circle"></i> Lihat Film Sekarang
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Daftar Film -->
<div class="container" id="movie-list">
    <!-- Search -->
    <div class="search-section">
        <form method="GET" action="index.php" class="row g-3 align-items-center justify-content-center">
            <div class="col-md-8">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Cari film berdasarkan judul..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>" />
                    <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i> Cari</button>
                </div>
            </div>
            <div class="col-md-2 text-md-start">
                <a href="index.php" class="btn btn-outline-secondary rounded-pill px-4"><i class="bi bi-arrow-counterclockwise"></i> Reset</a>
            </div>
        </form>
    </div>

    <!-- Movie Cards -->
    <div class="row g-4" id="movie-grid">
        <?php
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        try {
            if ($search !== '') {
                $stmt = $pdo->prepare("SELECT * FROM movies WHERE title LIKE :search ORDER BY title ASC");
                $stmt->execute(['search' => '%' . $search . '%']);
            } else {
                $stmt = $pdo->query("SELECT * FROM movies ORDER BY title ASC");
            }
            $movies = $stmt->fetchAll();

            if (count($movies) === 0) {
                echo '<div class="col-12">
                        <div class="empty-state">
                            <i class="bi bi-film"></i>
                            <h4>Film tidak ditemukan</h4>
                            <p class="text-muted">Coba gunakan kata kunci lain atau reset pencarian.</p>
                            <a href="index.php" class="btn btn-primary rounded-pill px-4 mt-3">
                                <i class="bi bi-arrow-counterclockwise"></i> Reset Pencarian
                            </a>
                        </div>
                      </div>';
            } else {
                foreach ($movies as $movie) {
                    $price = number_format($movie['price'], 0, ',', '.');
                    $poster = !empty($movie['poster']) ? $movie['poster'] : 'https://picsum.photos/seed/' . $movie['id'] . '/400/320';
                    ?>
                    <div class="col-6 col-md-4 col-lg-3">
                        <div class="movie-card card">
                            <img src="<?php echo htmlspecialchars($poster); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($movie['title']); ?>" onerror="this.src='https://picsum.photos/seed/<?php echo $movie['id']; ?>/400/320'" />
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title"><?php echo htmlspecialchars($movie['title']); ?></h5>
                                <div class="movie-meta mb-2">
                                    <span><i class="bi bi-tag"></i> <?php echo htmlspecialchars($movie['genre']); ?></span>
                                    <span class="ms-2"><i class="bi bi-clock"></i> <?php echo htmlspecialchars($movie['duration']); ?></span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-auto pt-2">
                                    <span class="price-tag">Rp <?php echo $price; ?></span>
                                    <a href="detail.php?id=<?php echo $movie['id']; ?>" class="btn btn-primary btn-detail">
                                        <i class="bi bi-eye"></i> Detail
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            }
        } catch (PDOException $e) {
            echo '<div class="col-12"><div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill"></i> Gagal memuat data film: ' . htmlspecialchars($e->getMessage()) . '</div></div>';
        }
        ?>
    </div>
</div>

<!-- Footer -->
<footer class="footer">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h5 class="text-white fw-bold"><i class="bi bi-film"></i> CineReserve</h5>
                <p class="mb-0">Sistem reservasi tiket bioskop online &copy; <?php echo date('Y'); ?></p>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>