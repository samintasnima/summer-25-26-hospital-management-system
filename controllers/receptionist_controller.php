<?php
/**
 * Receptionist Controller
 * Unique features: 1. Queue Management  2. Invoice Generation  3. Patient Registration + Appointment Management
 */
require_once __DIR__ . '/../helpers/helpers.php';
require_once __DIR__ . '/../models/receptionist_model.php';
require_once __DIR__ . '/../models/user_model.php';
require_once __DIR__ . '/../models/patient_model.php';
require_once __DIR__ . '/../models/appointment_model.php';
require_once __DIR__ . '/../models/invoice_model.php';
require_once __DIR__ . '/../models/payment_model.php';
require_once __DIR__ . '/../models/queue_model.php';
require_once __DIR__ . '/../models/queue_alert_model.php';
require_once __DIR__ . '/../models/wheelchair_model.php';

function receptionist_dashboard() {
    require_role('receptionist');
    $stats = receptionist_dashboard_stats();
    $today_queue = queue_list(date('Y-m-d'));
    require __DIR__ . '/../views/receptionist/dashboard.php';
}

function receptionist_patients() {
    require_role('receptionist');
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!verify_csrf()) {
            set_flash('danger', 'Invalid CSRF.');
            redirect('index.php?page=receptionist_patients');
        }
        $action = $_POST['form_action'] ?? '';
        
        if ($action === 'create') {
            $data = [
                'username' => clean($_POST['username'] ?? ''),
                'email' => clean($_POST['email'] ?? ''),
                'password' => hash_password($_POST['password'] ?? 'password123'),
                'full_name' => clean($_POST['full_name'] ?? ''),
                'role' => 'patient',
                'phone' => clean($_POST['phone'] ?? ''),
                'gender' => clean($_POST['gender'] ?? ''),
                'date_of_birth' => clean($_POST['date_of_birth'] ?? ''),
                'address' => clean($_POST['address'] ?? ''),
                'status' => 'active'
            ];
            $errors = [];
            if (strlen($data['username']) < 3) $errors[] = 'Username min 3 chars.';
            if (!is_valid_email($data['email'])) $errors[] = 'Invalid email.';
            if (empty($data['full_name'])) $errors[] = 'Name required.';
            if (user_find_by_username($data['username'])) $errors[] = 'Username taken.';
            if (user_find_by_email($data['email'])) $errors[] = 'Email taken.';
            
            if (empty($errors)) {
                $uid = user_create($data);
                if ($uid) {
                    patient_create_profile($uid, [
                        'blood_group' => clean($_POST['blood_group'] ?? ''),
                        'emergency_contact' => clean($_POST['emergency_contact'] ?? ''),
                        'allergies' => clean($_POST['allergies'] ?? '')
                    ]);
                    log_activity('patient_register', "Receptionist registered patient: {$data['username']}");
                    set_flash('success', 'Patient registered successfully.');
                } else {
                    set_flash('danger', 'Failed to register.');
                }
            } else {
                set_flash('danger', implode(' ', $errors));
            }
        } elseif ($action === 'update') {
            $uid = (int)$_POST['user_id'];
            user_update($uid, [
                'full_name' => clean($_POST['full_name'] ?? ''),
                'phone' => clean($_POST['phone'] ?? ''),
                'email' => clean($_POST['email'] ?? ''),
                'gender' => clean($_POST['gender'] ?? ''),
                'date_of_birth' => clean($_POST['date_of_birth'] ?? ''),
                'address' => clean($_POST['address'] ?? ''),
                'status' => clean($_POST['status'] ?? 'active')
            ]);
            patient_update_profile($uid, [
                'blood_group' => clean($_POST['blood_group'] ?? ''),
                'emergency_contact' => clean($_POST['emergency_contact'] ?? ''),
                'allergies' => clean($_POST['allergies'] ?? '')
            ]);
            log_activity('patient_update', "Updated patient #$uid");
            set_flash('success', 'Patient updated.');
        } elseif ($action === 'delete') {
            $uid = (int)$_POST['user_id'];
            user_delete($uid);
            log_activity('patient_delete', "Deleted patient #$uid");
            set_flash('success', 'Patient deleted.');
        }
        redirect('index.php?page=receptionist_patients');
    }
    
    $keyword = clean($_GET['q'] ?? '');
    $patients = patient_search($keyword);
    $edit = isset($_GET['id']) ? patient_get_profile((int)$_GET['id']) : null;
    
    require __DIR__ . '/../views/receptionist/patients.php';
}

function receptionist_appointments() {
    require_role('receptionist');
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!verify_csrf()) {
            set_flash('danger', 'Invalid CSRF.');
            redirect('index.php?page=receptionist_appointments');
        }
        $action = $_POST['form_action'] ?? '';
        
        if ($action === 'create') {
            $id = appointment_create([
                'patient_id' => (int)$_POST['patient_id'],
                'doctor_id' => (int)$_POST['doctor_id'],
                'appointment_date' => clean($_POST['appointment_date'] ?? ''),
                'appointment_time' => clean($_POST['appointment_time'] ?? ''),
                'reason' => clean($_POST['reason'] ?? ''),
                'status' => clean($_POST['status'] ?? 'confirmed'),
                'created_by' => current_user()['id']
            ]);
            if ($id) {
                log_activity('appointment_create', "Receptionist created appointment #$id");
                set_flash('success', 'Appointment created.');
            } else {
                set_flash('danger', 'Failed.');
            }
        } elseif ($action === 'update') {
            $aid = (int)$_POST['appointment_id'];
            appointment_update($aid, [
                'patient_id' => (int)$_POST['patient_id'],
                'doctor_id' => (int)$_POST['doctor_id'],
                'appointment_date' => clean($_POST['appointment_date'] ?? ''),
                'appointment_time' => clean($_POST['appointment_time'] ?? ''),
                'reason' => clean($_POST['reason'] ?? ''),
                'status' => clean($_POST['status'] ?? ''),
                'notes' => clean($_POST['notes'] ?? '')
            ]);
            log_activity('appointment_update', "Updated appointment #$aid");
            set_flash('success', 'Appointment updated.');
        } elseif ($action === 'delete') {
            $aid = (int)$_POST['appointment_id'];
            appointment_delete($aid);
            log_activity('appointment_delete', "Deleted appointment #$aid");
            set_flash('success', 'Appointment deleted.');
        }
        redirect('index.php?page=receptionist_appointments');
    }
    
    $filters = [];
    if (!empty($_GET['q'])) $filters['keyword'] = clean($_GET['q']);
    if (!empty($_GET['status'])) $filters['status'] = clean($_GET['status']);
    if (!empty($_GET['date'])) $filters['date'] = clean($_GET['date']);
    $appointments = appointment_search($filters);
    $patients = patient_search('', 200);
    $doctors = user_get_doctors();
    $edit = isset($_GET['id']) ? appointment_find((int)$_GET['id']) : null;
    
    require __DIR__ . '/../views/receptionist/appointments.php';
}

function receptionist_invoices() {
    require_role('receptionist');
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!verify_csrf()) {
            set_flash('danger', 'Invalid CSRF.');
            redirect('index.php?page=receptionist_invoices');
        }
        $action = $_POST['form_action'] ?? '';
        
        if ($action === 'create') {
            $amount = (float)($_POST['amount'] ?? 0);
            $tax = (float)($_POST['tax'] ?? 0);
            $discount = (float)($_POST['discount'] ?? 0);
            $id = invoice_create([
                'patient_id' => (int)$_POST['patient_id'],
                'appointment_id' => !empty($_POST['appointment_id']) ? (int)$_POST['appointment_id'] : null,
                'amount' => $amount,
                'tax' => $tax,
                'discount' => $discount,
                'description' => clean($_POST['description'] ?? ''),
                'due_date' => clean($_POST['due_date'] ?? ''),
                'created_by' => current_user()['id'],
                'status' => 'unpaid'
            ]);
            if ($id) {
                log_activity('invoice_create', "Created invoice #$id");
                set_flash('success', 'Invoice created.');
            } else {
                set_flash('danger', 'Failed to create invoice.');
            }
        } elseif ($action === 'update') {
            $iid = (int)$_POST['invoice_id'];
            invoice_update($iid, [
                'amount' => (float)$_POST['amount'],
                'tax' => (float)($_POST['tax'] ?? 0),
                'discount' => (float)($_POST['discount'] ?? 0),
                'description' => clean($_POST['description'] ?? ''),
                'status' => clean($_POST['status'] ?? ''),
                'due_date' => clean($_POST['due_date'] ?? '')
            ]);
            log_activity('invoice_update', "Updated invoice #$iid");
            set_flash('success', 'Invoice updated.');
        } elseif ($action === 'delete') {
            $iid = (int)$_POST['invoice_id'];
            invoice_delete($iid);
            log_activity('invoice_delete', "Deleted invoice #$iid");
            set_flash('success', 'Invoice deleted.');
        }
        redirect('index.php?page=receptionist_invoices');
    }
    
    $filters = [];
    if (!empty($_GET['q'])) $filters['keyword'] = clean($_GET['q']);
    if (!empty($_GET['status'])) $filters['status'] = clean($_GET['status']);
    $invoices = invoice_search($filters);
    $patients = patient_search('', 200);
    $edit = isset($_GET['id']) ? invoice_find((int)$_GET['id']) : null;
    
    require __DIR__ . '/../views/receptionist/invoices.php';
}

function receptionist_payments() {
    require_role('receptionist');
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!verify_csrf()) {
            set_flash('danger', 'Invalid CSRF.');
            redirect('index.php?page=receptionist_payments');
        }
        $action = $_POST['form_action'] ?? '';
        
        if ($action === 'create') {
            $invoice_id = (int)$_POST['invoice_id'];
            $inv = invoice_find($invoice_id);
            if (!$inv) {
                set_flash('danger', 'Invoice not found.');
                redirect('index.php?page=receptionist_payments');
            }
            $amount = (float)$_POST['amount'];
            $pid = payment_create([
                'invoice_id' => $invoice_id,
                'patient_id' => $inv['patient_id'],
                'amount' => $amount,
                'payment_method' => clean($_POST['payment_method'] ?? 'cash'),
                'transaction_ref' => clean($_POST['transaction_ref'] ?? ''),
                'received_by' => current_user()['id'],
                'notes' => clean($_POST['notes'] ?? '')
            ]);
            if ($pid) {
                log_activity('payment_receive', "Received payment #$pid amount $amount");
                set_flash('success', 'Payment recorded.');
            } else {
                set_flash('danger', 'Failed.');
            }
        } elseif ($action === 'delete') {
            $pid = (int)$_POST['payment_id'];
            payment_delete($pid);
            log_activity('payment_delete', "Deleted payment #$pid");
            set_flash('success', 'Payment deleted.');
        }
        redirect('index.php?page=receptionist_payments');
    }
    
    $filters = [];
    if (!empty($_GET['q'])) $filters['keyword'] = clean($_GET['q']);
    $payments = payment_search($filters);
    $unpaid_invoices = invoice_search(['status' => 'unpaid']);
    $partial_invoices = invoice_search(['status' => 'partial']);
    $open_invoices = array_merge($unpaid_invoices, $partial_invoices);
    
    require __DIR__ . '/../views/receptionist/payments.php';
}

function receptionist_queue() {
    require_role('receptionist');
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!verify_csrf()) {
            set_flash('danger', 'Invalid CSRF.');
            redirect('index.php?page=receptionist_queue');
        }
        $action = $_POST['form_action'] ?? '';
        
        if ($action === 'add') {
            $id = queue_add([
                'patient_id' => (int)$_POST['patient_id'],
                'doctor_id' => (int)$_POST['doctor_id'],
                'appointment_id' => !empty($_POST['appointment_id']) ? (int)$_POST['appointment_id'] : null,
                'queue_date' => clean($_POST['queue_date'] ?? date('Y-m-d')),
                'priority' => clean($_POST['priority'] ?? 'normal')
            ]);
            if ($id) {
                log_activity('queue_add', "Added to queue #$id");
                set_flash('success', 'Patient added to queue.');
            } else {
                set_flash('danger', 'Failed.');
            }
        } elseif ($action === 'update_status') {
            $qid = (int)$_POST['queue_id'];
            $status = clean($_POST['status'] ?? '');
            if (in_array($status, ['waiting','called','in-progress','completed','skipped'])) {
                queue_update_status($qid, $status);
                log_activity('queue_status', "Queue #$qid set to $status");
                set_flash('success', 'Queue status updated.');
            }
        } elseif ($action === 'delete') {
            $qid = (int)$_POST['queue_id'];
            queue_delete($qid);
            log_activity('queue_delete', "Removed from queue #$qid");
            set_flash('success', 'Removed from queue.');
        }
        redirect('index.php?page=receptionist_queue');
    }
    
    $date = clean($_GET['date'] ?? date('Y-m-d'));
    $doctor_id = !empty($_GET['doctor_id']) ? (int)$_GET['doctor_id'] : null;
    $keyword = clean($_GET['q'] ?? '');
    
    if ($keyword) {
        $queue = queue_search($keyword, $date);
    } else {
        $queue = queue_list($date, $doctor_id);
    }
    
    $patients = patient_search('', 200);
    $doctors = user_get_doctors();
    
    require __DIR__ . '/../views/receptionist/queue.php';
}


function receptionist_queue_alerts() {
    require_role('receptionist');
    if($_SERVER['REQUEST_METHOD']==='POST'){
        if(!verify_csrf()){set_flash('danger','Invalid CSRF.');redirect('index.php?page=receptionist_queue_alerts');}
        $action=$_POST['form_action']??'';
        if($action==='create'){
            $qid=(int)$_POST['queue_id']; $q=queue_find($qid);
            if($q){
                $id=queue_alert_create([
                    'queue_id'=>$qid,'patient_id'=>$q['patient_id'],'doctor_id'=>$q['doctor_id'],
                    'message'=>clean($_POST['message']??'Please proceed to the doctor queue.'),
                    'alert_type'=>clean($_POST['alert_type']??'call'),'created_by'=>current_user()['id']
                ]);
                set_flash($id?'success':'danger',$id?'Queue alert created.':'Failed to create alert.');
            }
        } elseif($action==='update'){
            queue_alert_update((int)$_POST['alert_id'],clean($_POST['status']??'new')); set_flash('success','Alert updated.');
        } elseif($action==='delete'){
            queue_alert_delete((int)$_POST['alert_id']); set_flash('success','Alert deleted.');
        }
        redirect('index.php?page=receptionist_queue_alerts');
    }
    $alerts=queue_alert_search(clean($_GET['q']??''),clean($_GET['status']??''));
    $queue=queue_list(clean($_GET['date']??date('Y-m-d')));
    require __DIR__ . '/../views/receptionist/queue_alerts.php';
}

function receptionist_wheelchair() {
    require_role('receptionist');
    if($_SERVER['REQUEST_METHOD']==='POST'){
        if(!verify_csrf()){set_flash('danger','Invalid CSRF.');redirect('index.php?page=receptionist_wheelchair');}
        $action=$_POST['form_action']??'';
        if($action==='create'){
            $id=wheelchair_create([
                'patient_id'=>(int)$_POST['patient_id'],
                'appointment_id'=>!empty($_POST['appointment_id'])?(int)$_POST['appointment_id']:null,
                'request_date'=>clean($_POST['request_date']??date('Y-m-d')),
                'location'=>clean($_POST['location']??''),
                'notes'=>clean($_POST['notes']??''),
                'created_by'=>current_user()['id']
            ]);
            set_flash($id?'success':'danger',$id?'Wheelchair request added.':'Failed to add request.');
        } elseif($action==='update'){
            wheelchair_update((int)$_POST['request_id'],[
                'request_date'=>clean($_POST['request_date']??date('Y-m-d')),
                'location'=>clean($_POST['location']??''),
                'notes'=>clean($_POST['notes']??''),
                'status'=>clean($_POST['status']??'requested'),
                'assigned_to'=>current_user()['id']
            ]);
            set_flash('success','Wheelchair request updated.');
        } elseif($action==='delete'){
            wheelchair_delete((int)$_POST['request_id']); set_flash('success','Wheelchair request deleted.');
        }
        redirect('index.php?page=receptionist_wheelchair');
    }
    $requests=wheelchair_search(['keyword'=>clean($_GET['q']??''),'status'=>clean($_GET['status']??'')]);
    $patients=patient_search('',200);
    require __DIR__ . '/../views/receptionist/wheelchair.php';
}
