<?php
require_once __DIR__ . '/../config/database.php';
function wheelchair_create($data){
    $pid=(int)$data['patient_id']; $aid=!empty($data['appointment_id'])?(int)$data['appointment_id']:'NULL';
    $date=db_escape($data['request_date']??date('Y-m-d')); $loc=db_escape($data['location']??'');
    $notes=db_escape($data['notes']??''); $by=!empty($data['created_by'])?(int)$data['created_by']:'NULL';
    return db_query("INSERT INTO wheelchair_requests(patient_id,appointment_id,request_date,location,notes,created_by)
                     VALUES($pid,$aid,'$date','$loc','$notes',$by)")?db_insert_id():false;
}
function wheelchair_update($id,$data){
    $sets=[]; if(isset($data['request_date']))$sets[]="request_date='".db_escape($data['request_date'])."'";
    if(isset($data['location']))$sets[]="location='".db_escape($data['location'])."'";
    if(isset($data['notes']))$sets[]="notes='".db_escape($data['notes'])."'";
    if(isset($data['status']))$sets[]="status='".db_escape($data['status'])."'";
    if(isset($data['assigned_to']))$sets[]="assigned_to=".(int)$data['assigned_to'];
    if(!$sets)return false; return db_query("UPDATE wheelchair_requests SET ".implode(',',$sets)." WHERE id=".(int)$id);
}
function wheelchair_delete($id){return db_query("DELETE FROM wheelchair_requests WHERE id=".(int)$id);}
function wheelchair_search($filters=[]){
    $w=["1=1"]; if(!empty($filters['patient_id']))$w[]="wr.patient_id=".(int)$filters['patient_id'];
    if(!empty($filters['status']))$w[]="wr.status='".db_escape($filters['status'])."'";
    if(!empty($filters['keyword'])){$k=db_escape($filters['keyword']);$w[]="(u.full_name LIKE '%$k%' OR wr.location LIKE '%$k%' OR wr.notes LIKE '%$k%')";}
    $r=db_query("SELECT wr.*,u.full_name patient_name,u.phone patient_phone
                 FROM wheelchair_requests wr JOIN users u ON wr.patient_id=u.id WHERE ".implode(' AND ',$w)." ORDER BY wr.request_date DESC,wr.created_at DESC");
    $rows=[];if($r)while($x=mysqli_fetch_assoc($r))$rows[]=$x;return $rows;
}
