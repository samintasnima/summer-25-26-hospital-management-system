<?php
/**
 * Doctor Controller
 * Unique features: 1. Availability Management  2. Prescriptions  3. Medical History
 */
require_once __DIR__ . '/../helpers/helpers.php';
require_once __DIR__ . '/../models/doctor_model.php';
require_once __DIR__ . '/../models/appointment_model.php';
require_once __DIR__ . '/../models/prescription_model.php';
require_once __DIR__ . '/../models/medical_history_model.php';
require_once __DIR__ . '/../models/user_model.php';
require_once __DIR__ . '/../models/queue_model.php';

function doctor_dashboard() {
    require_role('doctor');
    $doctor_id = current_user()['id'];
    $stats = doctor_dashboard_stats($doctor_id);
    $today_appts = doctor_get_appointments($doctor_id, '', date('Y-m-d'));
    require __DIR__ . '/../views/doctor/dashboard.php';
}

function doctor_appointments() {
    require_role('doctor');
    $doctor_id = current_user()['id'];
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!verify_csrf()) {
            set_flash('danger', 'Invalid CSRF.');
            redirect('index.php?page=doctor_appointments');
        }
        $action = $_POST['form_action'] ?? '';
        $aid = (int)($_POST['appointment_id'] ?? 0);
        
        if ($action === 'update_status') {
            $status = clean($_POST['status'] ?? '');
            if (in_array($status, ['confirmed','completed','cancelled','no-show'])) {
                appointment_update($aid, ['status' => $status, 'notes' => clean($_POST['notes'] ?? '')]);
                log_activity('appointment_update', "Doctor updated appointment #$aid to $status");
                set_flash('success', 'Appointment updated.');
            }
        }
        redirect('index.php?page=doctor_appointments');
    }
    
    $status = clean($_GET['status'] ?? '');
    $date = clean($_GET['date'] ?? '');
    $appointments = doctor_get_appointments($doctor_id, $status, $date);
    require __DIR__ . '/../views/doctor/appointments.php';
}

function doctor_patients() {
    require_role('doctor');
    $doctor_id = current_user()['id'];
    $patients = doctor_get_patients($doctor_id);
    $keyword = clean($_GET['q'] ?? '');
    if ($keyword) {
        $patients = array_filter($patients, function($p) use ($keyword) {
            return stripos($p['full_name'], $keyword) !== false || stripos($p['phone'] ?? '', $keyword) !== false;
        });
    }
    require __DIR__ . '/../views/doctor/patients.php';
}

function doctor_prescriptions() {
    require_role('doctor');
    $doctor_id = current_user()['id'];
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!verify_csrf()) {
            set_flash('danger', 'Invalid CSRF.');
            redirect('index.php?page=doctor_prescriptions');
        }
        $action = $_POST['form_action'] ?? '';
        
        if ($action === 'create') {
            $data = [
                'patient_id' => (int)$_POST['patient_id'],
                'doctor_id' => $doctor_id,
                'appointment_id' => !empty($_POST['appointment_id']) ? (int)$_POST['appointment_id'] : null,
                'diagnosis' => clean($_POST['diagnosis'] ?? ''),
                'medicines' => clean($_POST['medicines'] ?? ''),
                'dosage_instructions' => clean($_POST['dosage_instructions'] ?? ''),
                'notes' => clean($_POST['notes'] ?? ''),
                'prescribed_date' => clean($_POST['prescribed_date'] ?? date('Y-m-d'))
            ];
            if (empty($data['medicines']) || empty($data['patient_id'])) {
                set_flash('danger', 'Patient and medicines are required.');
            } else {
                $id = prescription_create($data);
                if ($id) {
                    log_activity('prescription_create', "Created prescription #$id");
                    set_flash('success', 'Prescription created.');
                } else {
                    set_flash('danger', 'Failed to create prescription.');
                }
            }
        } elseif ($action === 'update') {
            $pid = (int)$_POST['prescription_id'];
            prescription_update($pid, [
                'diagnosis' => clean($_POST['diagnosis'] ?? ''),
                'medicines' => clean($_POST['medicines'] ?? ''),
                'dosage_instructions' => clean($_POST['dosage_instructions'] ?? ''),
                'notes' => clean($_POST['notes'] ?? ''),
                'status' => clean($_POST['status'] ?? 'active')
            ]);
            log_activity('prescription_update', "Updated prescription #$pid");
            set_flash('success', 'Prescription updated.');
        } elseif ($action === 'delete') {
            $pid = (int)$_POST['prescription_id'];
            prescription_delete($pid);
            log_activity('prescription_delete', "Deleted prescription #$pid");
            set_flash('success', 'Prescription deleted.');
        }
        redirect('index.php?page=doctor_prescriptions');
    }
    
    $filters = ['doctor_id' => $doctor_id];
    if (!empty($_GET['q'])) $filters['keyword'] = clean($_GET['q']);
    if (!empty($_GET['status'])) $filters['status'] = clean($_GET['status']);
    $prescriptions = prescription_search($filters);
    $patients = doctor_get_patients($doctor_id);
    $edit = isset($_GET['id']) ? prescription_find((int)$_GET['id']) : null;
    
    require __DIR__ . '/../views/doctor/prescriptions.php';
}

function doctor_medical_history() {
    require_role('doctor');
    $doctor_id = current_user()['id'];
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!verify_csrf()) {
            set_flash('danger', 'Invalid CSRF.');
            redirect('index.php?page=doctor_medical_history');
        }
        $action = $_POST['form_action'] ?? '';
        
        if ($action === 'create') {
            $id = medical_history_create([
                'patient_id' => (int)$_POST['patient_id'],
                'doctor_id' => $doctor_id,
                'record_type' => clean($_POST['record_type'] ?? 'diagnosis'),
                'title' => clean($_POST['title'] ?? ''),
                'description' => clean($_POST['description'] ?? ''),
                'record_date' => clean($_POST['record_date'] ?? date('Y-m-d'))
            ]);
            if ($id) {
                log_activity('history_create', "Created medical history #$id");
                set_flash('success', 'Medical record added.');
            } else {
                set_flash('danger', 'Failed to add record.');
            }
        } elseif ($action === 'update') {
            $hid = (int)$_POST['history_id'];
            medical_history_update($hid, [
                'record_type' => clean($_POST['record_type'] ?? ''),
                'title' => clean($_POST['title'] ?? ''),
                'description' => clean($_POST['description'] ?? ''),
                'record_date' => clean($_POST['record_date'] ?? '')
            ]);
            log_activity('history_update', "Updated medical history #$hid");
            set_flash('success', 'Record updated.');
        } elseif ($action === 'delete') {
            $hid = (int)$_POST['history_id'];
            medical_history_delete($hid);
            log_activity('history_delete', "Deleted medical history #$hid");
            set_flash('success', 'Record deleted.');
        }
        redirect('index.php?page=doctor_medical_history');
    }
    
    $filters = ['doctor_id' => $doctor_id];
    if (!empty($_GET['q'])) $filters['keyword'] = clean($_GET['q']);
    if (!empty($_GET['patient_id'])) $filters['patient_id'] = (int)$_GET['patient_id'];
    $records = medical_history_search($filters);
    $patients = doctor_get_patients($doctor_id);
    $edit = isset($_GET['id']) ? medical_history_find((int)$_GET['id']) : null;
    
    require __DIR__ . '/../views/doctor/medical_history.php';
}

function doctor_availability() {
    require_role('doctor');
    $doctor_id = current_user()['id'];
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!verify_csrf()) {
            set_flash('danger', 'Invalid CSRF.');
            redirect('index.php?page=doctor_availability');
        }
        $action = $_POST['form_action'] ?? '';
        
        if ($action === 'create') {
            $id = doctor_add_availability([
                'doctor_id' => $doctor_id,
                'day_of_week' => clean($_POST['day_of_week'] ?? ''),
                'start_time' => clean($_POST['start_time'] ?? ''),
                'end_time' => clean($_POST['end_time'] ?? ''),
                'max_patients' => (int)($_POST['max_patients'] ?? 20),
                'is_available' => isset($_POST['is_available']) ? 1 : 0
            ]);
            if ($id) {
                log_activity('availability_add', "Added availability slot");
                set_flash('success', 'Availability added.');
            } else {
                set_flash('danger', 'Failed to add.');
            }
        } elseif ($action === 'update') {
            $aid = (int)$_POST['avail_id'];
            doctor_update_availability($aid, [
                'day_of_week' => clean($_POST['day_of_week'] ?? ''),
                'start_time' => clean($_POST['start_time'] ?? ''),
                'end_time' => clean($_POST['end_time'] ?? ''),
                'max_patients' => (int)($_POST['max_patients'] ?? 20),
                'is_available' => isset($_POST['is_available']) ? 1 : 0
            ]);
            log_activity('availability_update', "Updated availability #$aid");
            set_flash('success', 'Availability updated.');
        } elseif ($action === 'delete') {
            $aid = (int)$_POST['avail_id'];
            doctor_delete_availability($aid, $doctor_id);
            log_activity('availability_delete', "Deleted availability #$aid");
            set_flash('success', 'Slot deleted.');
        }
        redirect('index.php?page=doctor_availability');
    }
    
    $slots = doctor_get_availability($doctor_id);
    $edit = isset($_GET['id']) ? null : null; // simple list for now
    // Find edit if needed
    if (isset($_GET['id'])) {
        foreach ($slots as $s) {
            if ($s['id'] == $_GET['id']) { $edit = $s; break; }
        }
    }
    
    require __DIR__ . '/../views/doctor/availability.php';
}


function doctor_queue() {
    require_role('doctor');
    $doctor_id=current_user()['id'];
    if($_SERVER['REQUEST_METHOD']==='POST'){
        if(!verify_csrf()){set_flash('danger','Invalid CSRF.');redirect('index.php?page=doctor_queue');}
        $action=$_POST['form_action']??'';
        if($action==='update_status'){
            $qid=(int)$_POST['queue_id']; $status=clean($_POST['status']??'');
            $q=queue_find($qid);
            if($q && (int)$q['doctor_id']===(int)$doctor_id && in_array($status,['waiting','called','in-progress','completed','skipped'],true)){
                queue_update_status($qid,$status); log_activity('doctor_queue_status',"Doctor updated queue #$qid to $status");
                set_flash('success','Queue updated.');
            }
        }
        redirect('index.php?page=doctor_queue');
    }
    $date=clean($_GET['date']??date('Y-m-d')); $keyword=clean($_GET['q']??'');
    $queue=$keyword?queue_search($keyword,$date):queue_list($date,$doctor_id);
    $queue=array_values(array_filter($queue,function($q)use($doctor_id){return (int)$q['doctor_id']===(int)$doctor_id;}));
    require __DIR__ . '/../views/doctor/queue.php';
}
