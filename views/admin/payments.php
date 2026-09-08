<?php
$page_title='Payment Management';
require __DIR__.'/../partials/header.php'; require __DIR__.'/../partials/navbar.php';
?>
<h1 class="page-title"><i class="fas fa-credit-card"></i> Payment Management</h1>
<div class="form-panel"><h3>Add Payment</h3><form method="POST" data-validate><?=csrf_field()?><input type="hidden" name="form_action" value="create"><div class="form-row">
<div class="form-group"><label>Invoice *</label><select name="invoice_id" class="form-control" required><option value="">-- Select --</option><?php foreach($invoices as $i):?><option value="<?=(int)$i['id']?>"><?=e($i['invoice_number'])?> - <?=e($i['patient_name']??'Patient')?> (৳<?=number_format((float)$i['total_amount'],2)?>)</option><?php endforeach;?></select></div>
<div class="form-group"><label>Amount *</label><input type="number" step="0.01" min="0.01" name="amount" class="form-control" required></div>
<div class="form-group"><label>Method</label><select name="payment_method" class="form-control"><?php foreach(['cash','card','online','insurance'] as $m):?><option><?=$m?></option><?php endforeach;?></select></div>
<div class="form-group"><label>Transaction Ref</label><input name="transaction_ref" class="form-control"></div></div><div class="form-group"><label>Notes</label><textarea name="notes" class="form-control"></textarea></div><button class="btn btn-primary">Add Payment</button></form></div>
<div class="card">
  <div class="card-header"><h3>Search Payments</h3></div>
  <form class="search-bar" method="GET">
    <input type="hidden" name="page" value="admin_payments">
    <input name="q" placeholder="Patient, invoice or transaction ref..." value="<?=e($_GET['q']??'')?>">
    <input type="date" name="from" value="<?=e($_GET['from']??'')?>">
    <input type="date" name="to" value="<?=e($_GET['to']??'')?>">
    <button class="btn btn-primary btn-sm">Search</button>
  </form>
</div>
<?php if($edit): ?>
<div class="form-panel"><h3>Edit Payment #<?= (int)$edit['id'] ?></h3>
<form method="POST" data-validate><?=csrf_field()?><input type="hidden" name="form_action" value="update"><input type="hidden" name="payment_id" value="<?= (int)$edit['id'] ?>">
<div class="form-row">
<div class="form-group"><label>Amount *</label><input type="number" step="0.01" min="0.01" name="amount" class="form-control" required value="<?=e($edit['amount'])?>"></div>
<div class="form-group"><label>Method</label><select name="payment_method" class="form-control"><?php foreach(['cash','card','online','insurance'] as $m):?><option <?=$edit['payment_method']===$m?'selected':''?>><?=$m?></option><?php endforeach;?></select></div>
<div class="form-group"><label>Transaction Ref</label><input name="transaction_ref" class="form-control" value="<?=e($edit['transaction_ref']??'')?>"></div>
</div><div class="form-group"><label>Notes</label><textarea name="notes" class="form-control"><?=e($edit['notes']??'')?></textarea></div>
<button class="btn btn-primary">Update</button> <a class="btn btn-outline" href="index.php?page=admin_payments">Cancel</a>
</form></div>
<?php endif; ?>
<div class="card"><div class="card-header"><h3>Payments</h3></div><div class="table-responsive"><table class="data-table">
<thead><tr><th>Date</th><th>Patient</th><th>Invoice</th><th>Amount</th><th>Method</th><th>Reference</th><th>Actions</th></tr></thead><tbody>
<?php if(empty($payments)):?><tr><td colspan="7" class="empty-state">No payments found.</td></tr><?php else:foreach($payments as $p):?>
<tr><td><?=e(date('d M Y H:i',strtotime($p['payment_date'])))?></td><td><?=e($p['patient_name'])?></td><td><?=e($p['invoice_number'])?></td><td><?=format_money($p['amount'])?></td><td><?=e($p['payment_method'])?></td><td><?=e($p['transaction_ref']??'-')?></td>
<td class="actions"><a class="btn btn-sm btn-outline" href="index.php?page=admin_payments&id=<?=(int)$p['id']?>">Edit</a>
<form method="POST" style="display:inline" data-confirm="Delete this payment?"><?=csrf_field()?><input type="hidden" name="form_action" value="delete"><input type="hidden" name="payment_id" value="<?=(int)$p['id']?>"><button class="btn btn-sm btn-danger">Delete</button></form></td></tr>
<?php endforeach;endif;?></tbody></table></div></div>
<?php require __DIR__.'/../partials/footer.php'; ?>