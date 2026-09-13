<?php
$page_title = 'My Availability';
require __DIR__ . '/../partials/header.php';
require __DIR__ . '/../partials/navbar.php';
?>
<h1 class="page-title"><i class="fas fa-clock"></i> Availability Schedule</h1>

<div class="form-panel">
    <h3><?= $edit ? 'Edit Slot' : 'Add Availability Slot' ?></h3>
    <form method="POST" data-validate>
        <?= csrf_field() ?>
        <input type="hidden" name="form_action" value="<?= $edit ? 'update' : 'create' ?>">
        <?php if ($edit): ?><input type="hidden" name="avail_id" value="<?= (int)$edit['id'] ?>"><?php endif; ?>
        
        <div class="form-row">
            <div class="form-group">
                <label>Day *</label>
                <select name="day_of_week" class="form-control" required>
                    <?php foreach (['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'] as $d): ?>
                        <option value="<?= $d ?>" <?= ($edit['day_of_week']??'')===$d?'selected':'' ?>><?= $d ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Start Time *</label>
                <input type="time" name="start_time" class="form-control" required value="<?= e($edit['start_time']??'09:00') ?>">
            </div>
            <div class="form-group">
                <label>End Time *</label>
                <input type="time" name="end_time" class="form-control" required value="<?= e($edit['end_time']??'13:00') ?>">
            </div>
            <div class="form-group">
                <label>Max Patients</label>
                <input type="number" name="max_patients" class="form-control" min="1" value="<?= (int)($edit['max_patients']??20) ?>">
            </div>
        </div>
        <div class="form-group">
            <label><input type="checkbox" name="is_available" value="1" <?= !isset($edit) || !empty($edit['is_available']) ? 'checked' : '' ?>> Available</label>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save</button>
            <?php if ($edit): ?><a href="index.php?page=doctor_availability" class="btn btn-secondary">Cancel</a><?php endif; ?>
        </div>
    </form>
</div>

<div class="card">
    <div class="card-header"><h3>Current Schedule</h3></div>
    <div class="table-responsive">
        <table class="data-table">
            <thead><tr><th>Day</th><th>Start</th><th>End</th><th>Max Patients</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            <?php if (empty($slots)): ?>
                <tr><td colspan="6" class="empty-state">No availability slots set.</td></tr>
            <?php else: foreach ($slots as $s): ?>
                <tr>
                    <td><?= e($s['day_of_week']) ?></td>
                    <td><?= e(substr($s['start_time'],0,5)) ?></td>
                    <td><?= e(substr($s['end_time'],0,5)) ?></td>
                    <td><?= (int)$s['max_patients'] ?></td>
                    <td><span class="badge badge-<?= $s['is_available']?'active':'inactive' ?>"><?= $s['is_available']?'Available':'Unavailable' ?></span></td>
                    <td class="actions">
                        <a href="index.php?page=doctor_availability&id=<?= (int)$s['id'] ?>" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i></a>
                        <form method="POST" style="display:inline;" data-confirm="Delete this slot?">
                            <?= csrf_field() ?>
                            <input type="hidden" name="form_action" value="delete">
                            <input type="hidden" name="avail_id" value="<?= (int)$s['id'] ?>">
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
