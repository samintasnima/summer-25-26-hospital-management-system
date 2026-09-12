<?php
/**
 * Patient Controller
 * Unique features: 1. Browse Doctors & Book Appointments  2. View Own Prescriptions  3. Make Payments / View Invoices
 */
require_once __DIR__ . '/../helpers/helpers.php';
require_once __DIR__ . '/../models/user_model.php';
require_once __DIR__ . '/../models/patient_model.php';
require_once __DIR__ . '/../models/appointment_model.php';
require_once __DIR__ . '/../models/prescription_model.php';
require_once __DIR__ . '/../models/medical_history_model.php';
require_once __DIR__ . '/../models/invoice_model.php';
require_once __DIR__ . '/../models/payment_model.php';
require_once __DIR__ . '/../models/doctor_model.php';
require_once __DIR__ . '/../models/review_model.php';

function patient_dashboard() {
    require_role('patient');
    $patient_id = current_user()['id'];
    $stats = patient_dashboard_stats($patient_id);
    $upcoming = appointment_search(['patient_id' => $patient_id, 'status' => 'pending'], 5);
    $confirmed = appointment_search(['patient_id' => $patient_id, 'status' => 'confirmed'], 5);
    require __DIR__ . '/../views/patient/dashboard.php';
}

function patient_doctors() {
    require_role('patient');
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form_action'] ?? '') === 'book') {
        if (!verify_csrf()) {
            set_flash('danger', 'Invalid CSRF.');
            redirect('index.php?page=patient_doctors');
        }
        $data = [
            'patient_id' => current_user()['id'],
            'doctor_id' => (int)$_POST['doctor_id'],
            'appointment_date' => clean($_POST['appointment_date'] ?? ''),
            'appointment_time' => clean($_POST['appointment_time'] ?? ''),
            'reason' => clean($_POST['reason'] ?? ''),
            'status' => 'pending',
            'created_by' => current_user()['id']
        ];
        
        $errors = [];
        if (empty($data['appointment_date']) || empty($data['appointment_time'])) $errors[] = 'Date and time required.';
        if (strtotime($data['appointment_date']) < strtotime(date('Y-m-d'))) $errors[] = 'Cannot book past dates.';
        
        if (empty($errors)) {
            $id = appointment_create($data);
            if ($id) {
                log_activity('appointment_book', "Patient booked appointment #$id");
                set_flash('success', 'Appointment request submitted. Waiting for confirmation.');
            } else {
                set_flash('danger', 'Booking failed.');
            }
        } else {
            set_flash('danger', implode(' ', $errors));
        }
        redirect('index.php?page=patient_appointments');
    }
    
    $keyword = clean($_GET['q'] ?? '');
    $doctors = user_get_doctors(true);
    if ($keyword) {
        $doctors = array_filter($doctors, function($d) use ($keyword) {
            return stripos($d['full_name'], $keyword) !== false || stripos($d['specialization'] ?? '', $keyword) !== false;
        });
    }
    
    // Attach availability and public review summary
    foreach ($doctors as &$d) {
        $d['availability'] = doctor_get_availability($d['id']);
        $d['rating'] = review_average($d['id']);
        $d['reviews'] = review_search($d['id']);
    }
    
    require __DIR__ . '/../views/patient/doctors.php';
}

function patient_appointments() {
    require_role('patient');
    $patient_id = current_user()['id'];
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!verify_csrf()) {
            set_flash('danger', 'Invalid CSRF.');
            redirect('index.php?page=patient_appointments');
        }
        $action = $_POST['form_action'] ?? '';
        $aid = (int)($_POST['appointment_id'] ?? 0);
        
        // Patient can only cancel their own pending appointments
        $appt = appointment_find($aid);
        if ($appt && $appt['patient_id'] == $patient_id) {
            if ($action === 'cancel' && in_array($appt['status'], ['pending','confirmed'])) {
                appointment_update($aid, ['status' => 'cancelled']);
                log_activity('appointment_cancel', "Patient cancelled appointment #$aid");
                set_flash('success', 'Appointment cancelled.');
            }
        }
        redirect('index.php?page=patient_appointments');
    }
    
    $status = clean($_GET['status'] ?? '');
    $filters = ['patient_id' => $patient_id];
    if ($status) $filters['status'] = $status;
    $appointments = appointment_search($filters);
    
    require __DIR__ . '/../views/patient/appointments.php';
}

function patient_prescriptions() {
    require_role('patient');
    $patient_id = current_user()['id'];
    $filters = ['patient_id' => $patient_id];
    if (!empty($_GET['q'])) $filters['keyword'] = clean($_GET['q']);
    $prescriptions = prescription_search($filters);
    require __DIR__ . '/../views/patient/prescriptions.php';
}

function patient_medical_history() {
    require_role('patient');
    $patient_id = current_user()['id'];
    $records = medical_history_by_patient($patient_id);
    require __DIR__ . '/../views/patient/medical_history.php';
}

function patient_payments() {
    require_role('patient');
    $patient_id = current_user()['id'];
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form_action'] ?? '') === 'pay') {
        if (!verify_csrf()) {
            set_flash('danger', 'Invalid CSRF.');
            redirect('index.php?page=patient_payments');
        }
        $invoice_id = (int)$_POST['invoice_id'];
        $amount = (float)$_POST['amount'];
        $method = clean($_POST['payment_method'] ?? 'online');
        
        $inv = invoice_find($invoice_id);
        if (!$inv || $inv['patient_id'] != $patient_id) {
            set_flash('danger', 'Invalid invoice.');
            redirect('index.php?page=patient_payments');
        }
        
        $already_paid = payment_total_for_invoice($invoice_id);
        $remaining = (float)$inv['total_amount'] - $already_paid;
        
        if ($amount <= 0 || $amount > $remaining + 0.01) {
            set_flash('danger', 'Invalid payment amount.');
            redirect('index.php?page=patient_payments');
        }
        
        $pid = payment_create([
            'invoice_id' => $invoice_id,
            'patient_id' => $patient_id,
            'amount' => $amount,
            'payment_method' => $method,
            'transaction_ref' => 'ONLINE-' . strtoupper(uniqid()),
            'notes' => 'Patient self-payment'
        ]);
        
        if ($pid) {
            log_activity('payment_make', "Patient paid $amount for invoice #$invoice_id");
            set_flash('success', 'Payment recorded successfully.');
        } else {
            set_flash('danger', 'Payment failed.');
        }
        redirect('index.php?page=patient_payments');
    }
    
    $invoices = invoice_search(['patient_id' => $patient_id]);
    $payments = payment_search(['patient_id' => $patient_id]);
    
    require __DIR__ . '/../views/patient/payments.php';
}


function patient_reviews() {
    require_role('patient');
    $patient_id=current_user()['id'];
    if($_SERVER['REQUEST_METHOD']==='POST'){
        if(!verify_csrf()){set_flash('danger','Invalid CSRF.');redirect('index.php?page=patient_reviews');}
        $action=$_POST['form_action']??'';
        if($action==='create'){
            $doctor_id=(int)$_POST['doctor_id'];
            $id=review_create([
                'doctor_id'=>$doctor_id,'patient_id'=>$patient_id,
                'appointment_id'=>!empty($_POST['appointment_id'])?(int)$_POST['appointment_id']:null,
                'rating'=>(int)$_POST['rating'],'review'=>clean($_POST['review']??'')
            ]);
            set_flash($id?'success':'danger',$id?'Doctor review submitted.':'Unable to submit review.');
        } elseif($action==='update'){
            $id=(int)$_POST['review_id']; $r=review_find($id);
            if($r && (int)$r['patient_id']===(int)$patient_id) {
                review_update($id,['rating'=>(int)$_POST['rating'],'review'=>clean($_POST['review']??'')]);
                set_flash('success','Review updated.');
            }
        } elseif($action==='delete'){
            $id=(int)$_POST['review_id']; $r=review_find($id);
            if($r && (int)$r['patient_id']===(int)$patient_id){review_delete($id);set_flash('success','Review deleted.');}
        }
        redirect('index.php?page=patient_reviews');
    }
    $keyword=clean($_GET['q']??'');
    $doctors=user_get_doctors(true);
    foreach($doctors as &$d){$d['reviews']=review_search($d['id']);$d['rating']=review_average($d['id']);}
    unset($d);
    $reviews=review_search(0,$keyword);
    $my_reviews=array_values(array_filter($reviews,function($r)use($patient_id){return (int)$r['patient_id']===(int)$patient_id;}));
    $appointments=appointment_search(['patient_id'=>$patient_id,'status'=>'completed']);
    require __DIR__ . '/../views/patient/reviews.php';
}
