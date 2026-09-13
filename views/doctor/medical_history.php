<?php
$page_title = 'Medical History';
require __DIR__ . '/../partials/header.php';
require __DIR__ . '/../partials/navbar.php';
?>
<h1 class="page-title"><i class="fas fa-notes-medical"></i> Medical History</h1>

<div class="form-panel">
    <h3><?= $edit ? 'Edit Record' : 'Add Medical Record' ?></h3>
    <form method="POST" data-validate>
        <?= csrf_field() ?>
        <input type="hidden" name="form_action" value="<?= $edit ? 'update' : 'create' ?>">
        <?php if ($edit): ?><input type="hidden" name="history_id" value="<?= (int)$edit['id'] ?>"><?php endif; ?>
        
        <?php if (!$edit): ?>
        <div class="form-group">
            <label>Patient *</label>
            <select name="patient_id" class="form-control" required>
                <option value="">-- Select --</option>
                <?php foreach ($patients as $p): ?>
                    <option value="<?= (int)$p['id'] ?>"><?= e($p['full_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>
        
        <div class="form-row">
            <div class="form-group">
                <label>Type</label>
                <select name="record_type" class="form-control">
                    <?php foreach (['diagnosis','lab','surgery','allergy','vaccination','other'] as $t): ?>
                        <option value="<?= $t ?>" <?= ($edit['record_type']??'')===$t?'selected':'' ?>><?= ucfirst($t) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Date</label>
                <input type="date" name="record_date" class="form-control" value="<?= e($edit['record_date']??date('Y-m-d')) ?>">
            </div>
        </div>
        <div class="form-group">
            <label>Title *</label>
            <input type="text" name="title" class="form-control" required value="<?= e($edit['title']??'') ?>">
        </div>
        <div class="form-group">
            <label>Description</label>
            <textarea name="description" class="form-control" rows="3"><?= e($edit['description']??'') ?></textarea>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save</button>
            <?php if ($edit): ?><a href="index.php?page=doctor_medical_history" class="btn btn-secondary">Cancel</a><?php endif; ?>
        </div>
    </form>
</div>

<div class="card">
    <div class="card-header">
        <h3>Records</h3>
        <form class="search-bar" method="GET">
            <input type="hidden" name="page" value="doctor_medical_history">
            <input type="text" name="q" placeholder="Search..." value="<?= e($_GET['q']??'') ?>">
            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i></button>
        </form>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead><tr><th>Date</th><th>Patient</th><th>Type</th><th>Title</th><th>Actions</th></tr></thead>
            <tbody>
            <?php if (empty($records)): ?>
                <tr><td colspan="5" class="empty-state">No records.</td></tr>
            <?php else: foreach ($records as $r): ?>
                <tr>
                    <td><?= e($r['record_date']) ?></td>
                    <td><?= e($r['patient_name']) ?></td>
                    <td><?= e(ucfirst($r['record_type'])) ?></td>
                    <td><?= e($r['title']) ?></td>
                    <td class="actions">
                        <a href="index.php?page=doctor_medical_history&id=<?= (int)$r['id'] ?>" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i></a>
                        <form method="POST" style="display:inline;" data-confirm="Delete record?">
                            <?= csrf_field() ?>
                            <input type="hidden" name="form_action" value="delete">
                            <input type="hidden" name="history_id" value="<?= (int)$r['id'] ?>">
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
