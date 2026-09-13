<?php
$page_title='Review Doctors';
require __DIR__.'/../partials/header.php'; require __DIR__.'/../partials/navbar.php';
?>
<h1 class="page-title"><i class="fas fa-star"></i> Review Doctors</h1>
<div class="card"><form class="search-bar" method="GET"><input type="hidden" name="page" value="patient_reviews"><input name="q" placeholder="Search doctor/review..." value="<?=e($_GET['q']??'')?>"><button class="btn btn-primary btn-sm">Search</button></form></div>
<div class="form-panel"><h3>Submit a Doctor Review</h3>
<form method="POST" data-validate><?=csrf_field()?><input type="hidden" name="form_action" value="create">
<div class="form-row"><div class="form-group"><label>Doctor *</label><select name="doctor_id" required class="form-control"><option value="">-- Select --</option><?php foreach($doctors as $d):?><option value="<?=(int)$d['id']?>"><?=e($d['full_name'])?> (<?=number_format((float)$d['rating']['avg_rating'],1)?>/5)</option><?php endforeach;?></select></div>
<div class="form-group"><label>Completed Appointment</label><select name="appointment_id" class="form-control"><option value="">-- Optional --</option><?php foreach($appointments as $a):?><option value="<?=(int)$a['id']?>"><?=e($a['appointment_date'].' '.$a['doctor_name'])?></option><?php endforeach;?></select></div>
<div class="form-group"><label>Rating *</label><select name="rating" class="form-control" required><option value="5">5 - Excellent</option><option value="4">4 - Very Good</option><option value="3">3 - Good</option><option value="2">2 - Fair</option><option value="1">1 - Poor</option></select></div></div>
<div class="form-group"><label>Review</label><textarea name="review" class="form-control" rows="3"></textarea></div><button class="btn btn-primary">Submit Review</button></form></div>
<div class="card"><div class="card-header"><h3>Doctor Ratings</h3></div><div class="table-responsive"><table class="data-table"><thead><tr><th>Doctor</th><th>Specialization</th><th>Rating</th><th>Reviews</th></tr></thead><tbody>
<?php foreach($doctors as $d):?><tr><td><?=e($d['full_name'])?></td><td><?=e($d['specialization']??'General')?></td><td>⭐ <?=number_format((float)$d['rating']['avg_rating'],1)?>/5</td><td><?=(int)$d['rating']['cnt']?></td></tr><?php endforeach;?></tbody></table></div></div>
<?php require __DIR__.'/../partials/footer.php'; ?>