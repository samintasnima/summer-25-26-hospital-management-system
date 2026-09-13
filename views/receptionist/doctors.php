<?php
$page_title = 'Find Doctors';
require __DIR__ . '/../partials/header.php';
require __DIR__ . '/../partials/navbar.php';
?>
<h1 class="page-title"><i class="fas fa-user-md"></i> Find & Book Doctors</h1>

<div class="card">
    <form class="search-bar" method="GET">
        <input type="hidden" name="page" value="patient_doctors">
        <input type="text" name="q" placeholder="Search by name or specialization..." value="<?= e($_GET['q']??'') ?>">
        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i></button>
    </form>
</div>

<?php if (empty($doctors)): ?>
    <div class="card"><div class="empty-state"><i class="fas fa-user-md"></i><p>No doctors found.</p></div></div>
<?php else: foreach ($doctors as $d): ?>
<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-user-md"></i> <?= e($d['full_name']) ?></h3>
        <span class="badge badge-confirmed"><?= e($d['specialization'] ?? 'General') ?></span>
    </div>
    <p><i class="fas fa-phone"></i> <?= e($d['phone']??'-') ?> &nbsp;|&nbsp; <i class="fas fa-envelope"></i> <?= e($d['email']) ?> &nbsp;|&nbsp; <strong>⭐ <?= number_format((float)($d['rating']['avg_rating']??0),1) ?>/5</strong> (<?= (int)($d['rating']['cnt']??0) ?> reviews)</p>
    
    <?php if (!empty($d['availability'])): ?>
        <p style="margin:0.5rem 0;font-size:0.9rem;"><strong>Availability:</strong>
        <?php foreach ($d['availability'] as $av): if ($av['is_available']): ?>
            <span class="badge badge-active"><?= e($av['day_of_week']) ?> <?= substr($av['start_time'],0,5) ?>-<?= substr($av['end_time'],0,5) ?></span>
        <?php endif; endforeach; ?>
        </p>
    <?php endif; ?>
    
    <details style="margin-top:0.75rem;">
        <summary class="btn btn-primary btn-sm" style="cursor:pointer;list-style:none;display:inline-flex;"><i class="fas fa-calendar-plus"></i> Book Appointment</summary>
        <form method="POST" action="index.php?page=patient_doctors" style="margin-top:1rem;" data-validate>
            <?= csrf_field() ?>
            <input type="hidden" name="form_action" value="book">
            <input type="hidden" name="doctor_id" value="<?= (int)$d['id'] ?>">
            <div class="form-row">
                <div class="form-group">
                    <label>Date *</label>
                    <input type="date" name="appointment_date" class="form-control" required min="<?= date('Y-m-d') ?>">
                </div>
                <div class="form-group">
                    <label>Time *</label>
                    <input type="time" name="appointment_time" class="form-control" required>
                </div>
            </div>
            <div class="form-group">
                <label>Reason</label>
                <textarea name="reason" class="form-control" rows="2"></textarea>
            </div>
            <button type="submit" class="btn btn-success"><i class="fas fa-check"></i> Submit Request</button>
        </form>
    </details>
</div>
<?php endforeach; endif; ?>
<?php require __DIR__ . '/../partials/footer.php'; ?>
