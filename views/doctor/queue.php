<?php
$page_title='Patient Queue';
require __DIR__.'/../partials/header.php'; require __DIR__.'/../partials/navbar.php';
?>
<h1 class="page-title"><i class="fas fa-list-ol"></i> Patient Queue</h1>
<div class="card"><div class="card-header"><h3>Today's / Selected Queue</h3>
<form class="search-bar" method="GET"><input type="hidden" name="page" value="doctor_queue"><input type="date" name="date" value="<?=e($date)?>"><input name="q" placeholder="Search patient..." value="<?=e($_GET['q']??'')?>"><button class="btn btn-primary btn-sm">Search</button></form>
</div><div class="table-responsive"><table class="data-table"><thead><tr><th>#</th><th>Patient</th><th>Phone</th><th>Priority</th><th>Status</th><th>Update</th></tr></thead><tbody>
<?php if(empty($queue)):?><tr><td colspan="6" class="empty-state">No patients in your queue.</td></tr><?php else:foreach($queue as $q):?><tr>
<td><strong><?= (int)$q['queue_number']?></strong></td><td><?=e($q['patient_name'])?></td><td><?=e($q['patient_phone']??'-')?></td><td><?=e($q['priority'])?></td><td><?=e($q['status'])?></td>
<td><form method="POST"><?=csrf_field()?><input type="hidden" name="form_action" value="update_status"><input type="hidden" name="queue_id" value="<?=(int)$q['id']?>"><select name="status" onchange="this.form.submit()"><?php foreach(['waiting','called','in-progress','completed','skipped'] as $st):?><option <?=$q['status']===$st?'selected':''?>><?=$st?></option><?php endforeach;?></select></form></td>
</tr><?php endforeach;endif;?></tbody></table></div></div>
<?php require __DIR__.'/../partials/footer.php'; ?>