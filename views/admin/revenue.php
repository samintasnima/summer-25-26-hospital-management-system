<?php
$page_title = 'Revenue Reports';
require __DIR__ . '/../partials/header.php';
require __DIR__ . '/../partials/navbar.php';
?>
<h1 class="page-title"><i class="fas fa-chart-line"></i> Revenue Reports</h1>

<div class="card">
    <form class="search-bar" method="GET" action="index.php">
        <input type="hidden" name="page" value="admin_revenue">
        <label>From:</label>
        <input type="date" name="from" value="<?= e($from) ?>">
        <label>To:</label>
        <input type="date" name="to" value="<?= e($to) ?>">
        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter"></i> Filter</button>
    </form>
    
    <div class="stats-grid" style="margin-top:1rem;">
        <div class="stat-card">
            <div class="stat-icon green"><i class="fas fa-coins"></i></div>
            <div class="stat-info">
                <h4><?= format_money($summary['total'] ?? 0) ?></h4>
                <p>Total Revenue</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon blue"><i class="fas fa-receipt"></i></div>
            <div class="stat-info">
                <h4><?= (int)($summary['cnt'] ?? 0) ?></h4>
                <p>Transactions</p>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header"><h3>Payment Breakdown</h3></div>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr><th>Date</th><th>Method</th><th>Transactions</th><th>Amount</th></tr>
            </thead>
            <tbody>
            <?php if (empty($report)): ?>
                <tr><td colspan="4" class="empty-state">No payments in this period.</td></tr>
            <?php else: foreach ($report as $row): ?>
                <tr>
                    <td><?= e($row['pay_date']) ?></td>
                    <td><?= e(ucfirst($row['payment_method'])) ?></td>
                    <td><?= (int)$row['transactions'] ?></td>
                    <td><?= format_money($row['total_amount']) ?></td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require __DIR__ . '/../partials/footer.php'; ?>
