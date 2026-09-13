<?php
require_once __DIR__ . '/../config/database.php';

function review_create($data) {
    $doctor_id=(int)$data['doctor_id']; $patient_id=(int)$data['patient_id'];
    $appointment_id=!empty($data['appointment_id'])?(int)$data['appointment_id']:'NULL';
    $rating=(int)$data['rating']; $review=db_escape($data['review']??'');
    if ($rating<1 || $rating>5) return false;
    $sql="INSERT INTO doctor_reviews (doctor_id,patient_id,appointment_id,rating,review)
          VALUES ($doctor_id,$patient_id,$appointment_id,$rating,'$review')";
    return db_query($sql)?db_insert_id():false;
}
function review_update($id,$data) {
    $id=(int)$id; $sets=[];
    if(isset($data['rating'])) $sets[]="rating=".(int)$data['rating'];
    if(isset($data['review'])) $sets[]="review='".db_escape($data['review'])."'";
    if(isset($data['status'])) $sets[]="status='".db_escape($data['status'])."'";
    if(!$sets)return false;
    return db_query("UPDATE doctor_reviews SET ".implode(',',$sets)." WHERE id=$id");
}
function review_delete($id){ return db_query("DELETE FROM doctor_reviews WHERE id=".(int)$id); }
function review_find($id){
    $r=db_query("SELECT r.*,d.full_name AS doctor_name,p.full_name AS patient_name
                 FROM doctor_reviews r JOIN users d ON r.doctor_id=d.id JOIN users p ON r.patient_id=p.id
                 WHERE r.id=".(int)$id." LIMIT 1");
    return $r?mysqli_fetch_assoc($r):null;
}
function review_search($doctor_id=0,$keyword='',$limit=100){
    $where=["r.status='visible'"];
    if($doctor_id)$where[]="r.doctor_id=".(int)$doctor_id;
    if($keyword){$k=db_escape($keyword);$where[]="(d.full_name LIKE '%$k%' OR p.full_name LIKE '%$k%' OR r.review LIKE '%$k%')";}
    $r=db_query("SELECT r.*,d.full_name AS doctor_name,p.full_name AS patient_name
                 FROM doctor_reviews r JOIN users d ON r.doctor_id=d.id JOIN users p ON r.patient_id=p.id
                 WHERE ".implode(' AND ',$where)." ORDER BY r.created_at DESC LIMIT ".(int)$limit);
    $rows=[]; if($r)while($x=mysqli_fetch_assoc($r))$rows[]=$x; return $rows;
}
function review_average($doctor_id){
    $r=db_query("SELECT COALESCE(AVG(rating),0) avg_rating, COUNT(*) cnt FROM doctor_reviews WHERE doctor_id=".(int)$doctor_id." AND status='visible'");
    return $r?mysqli_fetch_assoc($r):['avg_rating'=>0,'cnt'=>0];
}
