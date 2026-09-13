<?php
$page_title = 'Prescriptions';
require __DIR__ . '/../partials/header.php';
require __DIR__ . '/../partials/navbar.php';
?>
<h1 class="page-title"><i class="fas fa-prescription"></i> Prescriptions</h1>

<div class="form-panel">
    <h3><?= $edit ? 'Edit Prescription' : 'New Prescription' ?></h3>
    <form method="POST" data-validate>
        <?= csrf_field() ?>
        <input type="hidden" name="form_action" value="<?= $edit ? 'update' : 'create' ?>">
        <?php if ($edit): ?><input type="hidden" name="prescription_id" value="<?= (int)$edit['id'] ?>"><?php endif; ?>
        
        <?php if (!$edit): ?>
        <div class="form-group">
            <label>Patient *</label>
            <select name="patient_id" class="form-control" required>
                <option value="">-- Select Patient --</option>
                <?php foreach ($patients as $p): ?>
                    <option value="<?= (int)$p['id'] ?>"><?= e($p['full_name']) ?> (<?= e($p['phone']??'') ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>
        
        <div class="form-group">
            <label>Diagnosis</label>
            <input type="text" name="diagnosis" class="form-control" value="<?= e($edit['diagnosis']??'') ?>">
        </div>
        <div class="form-group">
            <label>Medicines *</label>
            <textarea name="medicines" class="form-control" rows="3" required placeholder="e.g. Paracetamol 500mg - 1 tablet 3 times daily"><?= e($edit['medicines']??'') ?></textarea>
        </div>
        <div class="form-group">
            <label>Dosage Instructions</label>
            <textarea name="dosage_instructions" class="form-control" rows="2"><?= e($edit['dosage_instructions']??'') ?></textarea>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Date</label>
                <input type="date" name="prescribed_date" class="form-control" value="<?= e($edit['prescribed_date']??date('Y-m-d')) ?>">
            </div>
            <?php if ($edit): ?>
            <div class="form-group">
                <label>Status</label>
                <select name="status" class="form-control">
                    <option value="active" <?= ($edit['status']??'')==='active'?'selected':'' ?>>Active</option>
                    <option value="completed" <?= ($edit['status']??'')==='completed'?'selected':'' ?>>Completed</option>
                    <option value="cancelled" <?= ($edit['status']??'')==='cancelled'?'selected':'' ?>>Cancelled</option>
                </select>
            </div>
            <?php endif; ?>
        </div>
        <div class="form-group">
            <label>Notes</label>
            <textarea name="notes" class="form-control" rows="2"><?= e($edit['notes']??'') ?></textarea>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save</button>
            <?php if ($edit): ?><a href="index.php?page=doctor_prescriptions" class="btn btn-secondary">Cancel</a><?php endif; ?>
        </div>
    </form>
</div>

<div class="card">
    <div class="card-header">
        <h3>All Prescriptions</h3>
        <form class="search-bar" method="GET">
            <input type="hidden" name="page" value="doctor_prescriptions">
            <input type="text" name="q" placeholder="Search..." value="<?= e($_GET['q']??'') ?>">
            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i></button>
        </form>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead><tr><th>Date</th><th>Patient</th><th>Diagnosis</th><th>Medicines</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            <?php if (empty($prescriptions)): ?>
                <tr><td colspan="6" class="empty-state">No prescriptions yet.</td></tr>
            <?php else: foreach ($prescriptions as $pr): ?>
                <tr>
                    <td><?= e($pr['prescribed_date']) ?></td>
                    <td><?= e($pr['patient_name']) ?></td>
                    <td><?= e($pr['diagnosis']??'-') ?></td>
                    <td><?= e(mb_strimwidth($pr['medicines'],0,40,'...')) ?></td>
                    <td><span class="badge badge-<?= e($pr['status']) ?>"><?= e($pr['status']) ?></span></td>
                    <td class="actions">
                        <a href="index.php?page=doctor_prescriptions&id=<?= (int)$pr['id'] ?>" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i></a>
                        <form method="POST" style="display:inline;" data-confirm="Delete prescription?">
                            <?= csrf_field() ?>
                            <input type="hidden" name="form_action" value="delete">
                            <input type="hidden" name="prescription_id" value="<?= (int)$pr['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require __DIR__ . '/../partials/footer.php'; ?>
