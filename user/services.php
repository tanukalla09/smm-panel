<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/notifications.php';
requireLogin();

$db = getDB();
$category = trim($_GET['category'] ?? '');
$search = trim($_GET['q'] ?? '');

$sql = "SELECT * FROM services WHERE status = 'active'";
$params = [];
if ($category !== '') { $sql .= ' AND category = ?'; $params[] = $category; }
if ($search !== '') { $sql .= ' AND (name LIKE ? OR description LIKE ?)'; $params[] = "%$search%"; $params[] = "%$search%"; }
$sql .= ' ORDER BY category, name';

$stmt = $db->prepare($sql);
$stmt->execute($params);
$services = $stmt->fetchAll();

$categories = $db->query("SELECT DISTINCT category FROM services WHERE status = 'active' ORDER BY category")->fetchAll(PDO::FETCH_COLUMN);

$pageTitle = 'Services';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid px-4 py-4">
    <div class="page-header mb-4">
        <h1>Browse Services</h1>
        <p>Find the perfect service to grow your social media</p>
    </div>

    <?php if ($msg = flash('success')): ?><div class="alert alert-success"><?= sanitize($msg) ?></div><?php endif; ?>
    <?php if ($msg = flash('error')): ?><div class="alert alert-danger"><?= sanitize($msg) ?></div><?php endif; ?>

    <div class="services-toolbar mb-4">
        <form method="GET" class="search-bar">
            <i class="bi bi-search"></i>
            <input type="text" name="q" class="form-control" placeholder="Search services..." value="<?= sanitize($search) ?>">
            <?php if ($category): ?><input type="hidden" name="category" value="<?= sanitize($category) ?>"><?php endif; ?>
        </form>
        <div class="filter-tabs">
            <a href="<?= SITE_URL ?>/user/services.php<?= $search ? '?q=' . urlencode($search) : '' ?>" class="filter-tab <?= $category === '' ? 'active' : '' ?>">All</a>
            <?php foreach ($categories as $cat): ?>
            <a href="?category=<?= urlencode($cat) ?><?= $search ? '&q=' . urlencode($search) : '' ?>" class="filter-tab <?= $category === $cat ? 'active' : '' ?>"><?= sanitize($cat) ?></a>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="row g-4">
        <?php if (empty($services)): ?>
        <div class="col-12 empty-state"><i class="bi bi-search"></i><p>No services match your search.</p></div>
        <?php else: foreach ($services as $svc): ?>
        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <div class="card card-smm service-card h-100">
                <div class="service-card-body">
                    <div class="service-card-top">
                        <div class="platform-emoji" aria-hidden="true"><?= platformEmoji($svc['category']) ?></div>
                        <span class="category-badge"><?= sanitize($svc['category']) ?></span>
                        <h3 class="service-card-name"><?= sanitize($svc['name']) ?></h3>
                        <?php if ($svc['description']): ?>
                        <p class="service-card-desc"><?= sanitize($svc['description']) ?></p>
                        <?php endif; ?>
                        <div class="service-rate-line">$<?= number_format((float)$svc['rate'], 2) ?> / 1K</div>
                        <div class="service-card-qty">Min <?= number_format($svc['min_quantity']) ?> — Max <?= number_format($svc['max_quantity']) ?></div>
                    </div>
                    <div class="service-card-actions">
                        <button type="button" class="btn-order" data-bs-toggle="modal" data-bs-target="#orderModal"
                            data-id="<?= $svc['id'] ?>" data-name="<?= sanitize($svc['name']) ?>"
                            data-rate="<?= $svc['rate'] ?>" data-min="<?= $svc['min_quantity'] ?>" data-max="<?= $svc['max_quantity'] ?>">
                            Order Now <i class="bi bi-arrow-right ms-1"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; endif; ?>
    </div>
</div>

<div class="modal fade" id="orderModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <form method="POST" action="<?= SITE_URL ?>/user/place_order.php">
            <div class="modal-header"><h5 class="modal-title">Place Order</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <input type="hidden" name="service_id" id="orderServiceId">
                <p class="fw-semibold" id="orderServiceName"></p>
                <div class="mb-3"><label class="form-label">Link / Username</label><input type="url" name="link" class="form-control" placeholder="https://..." required></div>
                <div class="mb-3"><label class="form-label">Quantity</label><input type="number" name="quantity" id="orderQuantity" class="form-control" required><div class="form-text" id="orderQtyHint"></div></div>
                <div class="charge-preview">Estimated: <strong id="orderCharge">$0.00</strong></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-accent">Place Order</button></div>
        </form>
    </div></div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
