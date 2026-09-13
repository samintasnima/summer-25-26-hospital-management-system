<?php
$page_title = 'Payments & Invoices';
require __DIR__ . '/../partials/header.php';
require __DIR__ . '/../partials/navbar.php';
?>
<h1 class="page-title"><i class="fas fa-credit-card"></i> Payments & Invoices</h1>

<div class="card">
    <div class="card-header"><h3>My Invoices</h3></div>
    <div class="table-responsive">
        <table class="data-table">
            <thead><tr><th>Invoice #</th><th>Amount</th><th>Total</th><th>Status</th><th>Due</th><th>Pay</th></tr></thead>
            <tbody>
            <?php if (empty($invoices)): ?>
                <tr><td colspan="6" class="empty-state">No invoices.</td></tr>
            <?php else: foreach ($invoices as $inv): ?>
                <tr>
                    <td><?= e($inv['invoice_number']) ?></td>
                    <td><?= format_money($inv['amount']) ?></td>
                    <td><?= format_money($inv['total_amount']) ?></td>
                    <td><span class="badge badge-<?= e($inv['status']) ?>"><?= e($inv['status']) ?></span></td>
                    <td><?= e($inv['due_date']??'-') ?></td>
                    <td>
                        <?php if (in_array($inv['status'], ['unpaid','partial'])): ?>
                        <details>
                            <summary class="btn btn-sm btn-success" style="cursor:pointer;list-style:none;">Pay Now</summary>
                            <form method="POST" style="margin-top:0.5rem;" data-validate>
                                <?= csrf_field() ?>
                                <input type="hidden" name="form_action" value="pay">
                                <input type="hidden" name="invoice_id" value="<?= (int)$inv['id'] ?>">
                                <input type="number" name="amount" class="form-control" step="0.01" min="0.01" 
                                       value="<?= number_format((float)$inv['total_amount'],2,'.','') ?>" required style="width:120px;display:inline-block;">
                                <select name="payment_method" class="form-control" style="width:auto;display:inline-block;">
                                    <option value="online">Online</option>
                                    <option value="card">Card</option>
                                    <option value="cash">Cash</option>
                                </select>
                                <button type="submit" class="btn btn-sm btn-primary">Confirm</button>
                            </form>
                        </details>
                        <?php else: ?>-<?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <div class="card-header"><h3>Payment History</h3></div>
    <div class="table-responsive">
        <table class="data-table">
            <thead><tr><th>Date</th><th>Invoice</th><th>Amount</th><th>Method</th><th>Ref</th></tr></thead>
            <tbody>
            <?php if (empty($payments)): ?>
                <tr><td colspan="5" class="empty-state">No payments yet.</td></tr>
            <?php else: foreach ($payments as $py): ?>
                <tr>
                    <td><?= e(date('d M Y H:i', strtotime($py['payment_date']))) ?></td>
                    <td><?= e($py['invoice_number']) ?></td>
                    <td><?= format_money($py['amount']) ?></td>
                    <td><?= e(ucfirst($py['payment_method'])) ?></td>
                    <td><?= e($py['transaction_ref']??'-') ?></td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require __DIR__ . '/../partials/footer.php'; ?>
