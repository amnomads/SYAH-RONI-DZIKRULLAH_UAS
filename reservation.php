<?php
session_start();
require_once 'config/database.php';

$movie_id = isset($_GET['movie_id']) ? (int)$_GET['movie_id'] : 0;
if ($movie_id <= 0) {
    header('Location: index.php');
    exit;
}

// Ambil data film
$stmt = $pdo->prepare("SELECT * FROM movies WHERE id = :id");
$stmt->execute(['id' => $movie_id]);
$movie = $stmt->fetch();
if (!$movie) {
    header('Location: index.php');
    exit;
}

// Ambil jadwal
$stmtSched = $pdo->prepare("SELECT * FROM schedules WHERE movie_id = :movie_id ORDER BY show_time ASC");
$stmtSched->execute(['movie_id' => $movie_id]);
$schedules = $stmtSched->fetchAll();

$error = '';
$success = false;

// Proses pemesanan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $schedule_id = (int)($_POST['schedule_id'] ?? 0);
    $quantity = (int)($_POST['quantity'] ?? 1);

    // Validasi sederhana
    if (empty($name) || empty($phone) || empty($email) || $schedule_id <= 0 || $quantity < 1) {
        $error = 'Semua field harus diisi dengan benar.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid.';
    } else {
        // Cek jadwal valid
        $stmtCheck = $pdo->prepare("SELECT * FROM schedules WHERE id = :id AND movie_id = :movie_id");
        $stmtCheck->execute(['id' => $schedule_id, 'movie_id' => $movie_id]);
        if ($stmtCheck->rowCount() === 0) {
            $error = 'Jadwal tidak valid.';
        } else {
            // Hitung total
            $total = $movie['price'] * $quantity;
            // Simpan reservasi
            $stmtInsert = $pdo->prepare("INSERT INTO reservations (movie_id, schedule_id, customer_name, phone, email, ticket_quantity, total_price) VALUES (?, ?, ?, ?, ?, ?, ?)");
            if ($stmtInsert->execute([$movie_id, $schedule_id, $name, $phone, $email, $quantity, $total])) {
                $reservation_id = $pdo->lastInsertId();
                // Simpan di session untuk ditampilkan di confirmation
                $_SESSION['reservation_id'] = $reservation_id;
                header('Location: confirmation.php');
                exit;
            } else {
                $error = 'Gagal menyimpan pemesanan, silakan coba lagi.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Reservasi Tiket - <?php echo htmlspecialchars($movie['title']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css" />
    <style>
        body { background-color: #f8f9fa; font-family: 'Segoe UI', sans-serif; }
        .reservation-card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        .reservation-card .card-header {
            background: linear-gradient(135deg, #0d1b2a, #1b3a5c);
            color: white;
            padding: 20px 28px;
            font-weight: 700;
            font-size: 1.25rem;
            border: none;
        }
        .reservation-card .card-body { padding: 30px; }
        .reservation-card .form-label { font-weight: 600; color: #1a1a2e; }
        .reservation-card .form-control, .reservation-card .form-select {
            border-radius: 10px;
            padding: 10px 16px;
            border: 2px solid #e9ecef;
        }
        .reservation-card .form-control:focus, .reservation-card .form-select:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 3px rgba(13,110,253,0.15);
        }
        .total-display {
            background: #f8f9fa;
            padding: 16px 24px;
            border-radius: 12px;
            font-size: 1.2rem;
            font-weight: 700;
            color: #0d6efd;
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
            <div class="reservation-card card">
                <div class="card-header">
                    <i class="bi bi-ticket-perforated"></i> Reservasi Tiket
                    <span class="float-end"><?php echo htmlspecialchars($movie['title']); ?></span>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    <form method="POST">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Nama Pemesan</label>
                                <input type="text" class="form-control" id="name" name="name" required value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" />
                            </div>
                            <div class="col-md-6">
                                <label for="phone" class="form-label">Nomor Telepon</label>
                                <input type="text" class="form-control" id="phone" name="phone" required value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>" />
                            </div>
                            <div class="col-12">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" />
                            </div>
                            <div class="col-md-6">
                                <label for="schedule_id" class="form-label">Jadwal Tayang</label>
                                <select class="form-select" id="schedule_id" name="schedule_id" required>
                                    <option value="">Pilih Jadwal</option>
                                    <?php foreach ($schedules as $sched): ?>
                                        <option value="<?php echo $sched['id']; ?>" <?php echo (isset($_POST['schedule_id']) && $_POST['schedule_id'] == $sched['id']) ? 'selected' : ''; ?>>
                                            <?php echo date('d M Y H:i', strtotime($sched['show_time'])) . ' - ' . htmlspecialchars($sched['studio']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="quantity" class="form-label">Jumlah Tiket</label>
                                <input type="number" class="form-control" id="quantity" name="quantity" min="1" required value="<?php echo isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1; ?>" />
                            </div>
                            <div class="col-12">
                                <div class="total-display text-center">
                                    Total Pembayaran: Rp <span id="totalPrice"><?php echo number_format($movie['price'] * (isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1), 0, ',', '.'); ?></span>
                                </div>
                            </div>
                            <div class="col-12 text-center">
                                <button type="submit" class="btn btn-primary btn-lg rounded-pill px-5 py-3 fw-bold">
                                    <i class="bi bi-check-circle"></i> Pesan Sekarang
                                </button>
                                <a href="detail.php?id=<?php echo $movie['id']; ?>" class="btn btn-outline-secondary rounded-pill px-4 ms-2">Batal</a>
                            </div>
                        </div>
                    </form>
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

<script>
    // Update total otomatis saat jumlah tiket berubah
    document.getElementById('quantity').addEventListener('input', function() {
        const price = <?php echo $movie['price']; ?>;
        const qty = parseInt(this.value) || 0;
        const total = price * qty;
        document.getElementById('totalPrice').textContent = new Intl.NumberFormat('id-ID').format(total);
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>