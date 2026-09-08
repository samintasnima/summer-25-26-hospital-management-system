<?php
require_once __DIR__ . '/../config/database.php';
function queue_alert_create($data){
    $qid=(int)$data['queue_id']; $pid=(int)$data['patient_id']; $did=(int)$data['doctor_id'];
    $msg=db_escape($data['message']??'Patient is requested to attend the queue.');
    $type=db_escape($data['alert_type']??'call'); $by=!empty($data['created_by'])?(int)$data['created_by']:'NULL';
    $sql="INSERT INTO queue_alerts(queue_id,patient_id,doctor_id,message,alert_type,created_by) VALUES($qid,$pid,$did,'$msg','$type',$by)";
    return db_query($sql)?db_insert_id():false;
}
function queue_alert_update($id,$status){$status=db_escape($status);return db_query("UPDATE queue_alerts SET status='$status' WHERE id=".(int)$id);}
function queue_alert_delete($id){return db_query("DELETE FROM queue_alerts WHERE id=".(int)$id);}
function queue_alert_search($keyword='',$status=''){
    $w=["1=1"]; if($keyword){$k=db_escape($keyword);$w[]="(p.full_name LIKE '%$k%' OR d.full_name LIKE '%$k%' OR qa.message LIKE '%$k%')";}
    if($status)$w[]="qa.status='".db_escape($status)."'";
    $r=db_query("SELECT qa.*,p.full_name patient_name,d.full_name doctor_name,q.queue_number
                 FROM queue_alerts qa JOIN users p ON qa.patient_id=p.id JOIN users d ON qa.doctor_id=d.id
                 JOIN queue q ON qa.queue_id=q.id WHERE ".implode(' AND ',$w)." ORDER BY qa.created_at DESC");
    $rows=[];if($r)while($x=mysqli_fetch_assoc($r))$rows[]=$x;return $rows;
}
