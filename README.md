# Hospital Management System (PHP + MySQL, MVC)

A teaching project for a 4-role hospital system: **admin, doctor, patient, receptionist**.
Written in plain PHP with procedural `mysqli` and prepared statements. No frameworks,
no Composer, no build step. Copy it into XAMPP and it runs.

---

## 1. Install (XAMPP)

1. Copy the `hospital_management` folder into `C:\xampp\htdocs\`
   so it becomes `htdocs/hospital_management/`.
2. Start **Apache** and **MySQL** in the XAMPP control panel.
3. Open `http://localhost/phpmyadmin` → **Import** → choose `database.sql` → **Go**.
4. Open `http://localhost/hospital_management/`.
5. Sign in with one of the sample accounts (see section 8).

If your MySQL uses a password, change `DB_PASS` in `config/config.php`
(or `config/database.php` depending on which file holds the credentials).

---

## 2. Folder structure

```
hospital_management/
├── index.php                  Front controller: the ONLY entry point (router)
├── database.sql               Schema + sample users, availability and notices
├── README.md
│
├── config/
│   ├── config.php             App constants, session settings
│   └── database.php           DB connection (mysqli)
│
├── helpers/
│   └── helpers.php            esc(), CSRF, login guards, flash messages
│
├── models/                    M — every SQL query lives here
│   ├── user_model.php         all 4 roles (one users table)
│   ├── admin_model.php
│   ├── doctor_model.php
│   ├── patient_model.php
│   ├── receptionist_model.php
│   ├── appointment_model.php
│   ├── prescription_model.php
│   ├── medical_history_model.php
│   ├── invoice_model.php
│   ├── payment_model.php
│   ├── notice_model.php
│   ├── log_model.php          activity log
│   ├── queue_model.php
│   ├── review_model.php
│   ├── queue_alert_model.php
│   └── wheelchair_model.php
│
├── controllers/               C — request handling, validation, decisions
│   ├── auth_controller.php    login / register / logout
│   ├── admin_controller.php
│   ├── doctor_controller.php
│   ├── patient_controller.php
│   ├── receptionist_controller.php
│   └── ajax_controller.php    all JSON endpoints
│
├── views/                     V — HTML only
│   ├── partials/              header.php, navbar.php, footer.php (shared layout)
│   ├── auth/                  login.php, register.php
│   ├── admin/                 dashboard, users, notices, activity logs, …
│   ├── doctor/                dashboard, appointments, queue, prescriptions, …
│   ├── patient/               dashboard, appointments, doctors, payments, …
│   └── receptionist/          dashboard, patients, queue, invoices, payments, …
│
└── assets/
    ├── css/style.css
    └── js/app.js              validation, escaping, live search, AJAX tables
```

**The MVC rule used throughout:** a view never runs a query, and a model never
prints HTML. The controller sits in the middle: it reads `$_POST`, validates,
calls the model, then `require`s the view.

---

## 3. How the router works

Every URL looks like this:

```
index.php?page=<page_name>
```

| URL | What happens |
| --- | --- |
| `index.php?page=login` | Login page |
| `index.php?page=register` | Signup page |
| `index.php?page=admin_dashboard` | Admin dashboard |
| `index.php?page=doctor_appointments` | Doctor’s appointment list |
| `index.php?page=patient_doctors` | Patient browses doctors |
| `index.php?page=receptionist_queue` | Receptionist manages the live queue |
| `index.php?page=ajax` | Returns JSON (live search, stats, …) |
| `index.php?page=logout` | Sign out |

`index.php` loads helpers and the database connection, checks whether the page is
public, enforces `require_login()` for everything else, then dispatches to the
matching controller function. Role checks (`require_role('doctor')` etc.) block
anyone who does not belong before the controller body runs.

---

## 4. The four roles

Each role owns its own screens and can perform **Create, Read, Update, Delete and Search**
on the records it is allowed to touch. No feature appears on two dashboards.

| Role | Manages (CRUD / main actions) | Feature 1 | Feature 2 | Feature 3 |
| --- | --- | --- | --- | --- |
| **Admin** | Users (doctors, patients, receptionists) | Activate / deactivate accounts | Notices + activity log with search | Live dashboard statistics (AJAX) |
| **Doctor** | Own appointments, queue, prescriptions, medical history | Accept / complete / cancel appointments | Set weekly availability slots | Patient search |
| **Patient** | Own appointments, reviews | Browse doctors + available times | Book / cancel appointments | View prescriptions, history & payments; rate doctors |
| **Receptionist** | Patients, queue, invoices, payments | Confirm / cancel appointments | Queue alerts (call / reminder / delay) | Wheelchair assistance requests |

### How the roles connect

- A **patient** books an appointment → the **receptionist** confirms it and adds the patient to the doctor’s queue.
- The **doctor** sees the queue, examines the patient, writes a prescription and a medical-history note, then marks the appointment completed.
- The **receptionist** generates an invoice; the patient pays at the desk; the payment is recorded.
- The **admin** can see the full activity trail of every step, post notices, and manage all user accounts.

---

## 5. Requirement checklist

| Requirement | Where to look |
| --- | --- |
| **MVC** | `models/`, `controllers/`, `views/`, routed by `index.php` |
| **DB (MySQLi procedural)** | every function in `models/` uses `mysqli_prepare` |
| **Auth (session + cookie)** | `controllers/auth_controller.php`, `helpers/helpers.php` |
| **PHP validation** | the validation blocks at the top of every controller action |
| **JS validation** | form checks in `assets/js/app.js`, called on submit |
| **AJAX / JSON** | `controllers/ajax_controller.php` + live-search helpers in `app.js` |
| **UI (HTML/CSS)** | `views/`, `assets/css/style.css` |
| **Basic web security** | see section 6 |
| **Feature completeness** | CRUD + search + specialised features per role |

---

## 6. Security, and why each piece is there

| Attack | Defence | File |
| --- | --- | --- |
| SQL injection | Prepared statements everywhere — user text is never glued into SQL | all `models/` |
| Stolen passwords | `password_hash()` on save, `password_verify()` on login | user / auth related models & controllers |
| XSS (server) | `e()` / `htmlspecialchars` wraps every value printed into HTML | `helpers.php`, all views |
| XSS (client) | escaping before any AJAX row is inserted into the DOM | `app.js` |
| CSRF | A secret token in every POST form | `helpers.php`, all views |
| Cookie theft | `httponly` + `samesite=Lax` on the session cookie | `config.php` |
| Idle machines | Session timeout constant (default 30 minutes) | `config.php` + helpers |
| Wrong role | `require_role()` / role checks before controller logic; each AJAX action re-checks | `index.php`, controllers, `ajax_controller.php` |
| URL tampering | Patients only see their own rows (`WHERE patient_id = ?` etc.) | patient / appointment / payment models |
| Username guessing | Wrong username and wrong password give the same generic message | `auth_controller.php` |

Two things worth saying out loud to students:

1. **JavaScript validation is a convenience, not a defence.** Anyone can turn
   JavaScript off. That is why every controller repeats the checks in PHP.
2. **"Remember me" (if present) only refills the username**, never the password.

---

## 7. Settings you can change

Mainly in `config/config.php` (and the DB block in `config/database.php` or `config.php`):

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');              // put a password here if MySQL has one
define('DB_NAME', 'hospital_management');

define('APP_NAME', 'HospitalMS');
define('CURRENCY', '৳');            // symbol shown next to money amounts
define('SESSION_TIMEOUT', 1800);    // idle sign-out, in seconds (30 min)
```

You can also adjust default `max_patients` for availability slots and the visual
theme colours inside `assets/css/style.css`.

---

## 8. Test accounts

| Role | Username | Password | Notes |
| --- | --- | --- | --- |
| Admin | `admin` | `password` | System Administrator |
| Doctor | `dr.smith` | `password` | Dr. John Smith – General Medicine |
| Receptionist | `reception` | `password` | Sarah Receptionist |
| Patient | `patient1` | `password` | Alice Patient |

New patients can also sign up on the register page. Doctor and receptionist
accounts are normally created by an admin. Change all default passwords after
the first login in any real deployment.

---

## 9. Database overview

The schema (`database.sql`) creates these tables:

| Table | Purpose |
| --- | --- |
| `users` | All four roles (role ENUM + profile fields) |
| `patients` | Extra patient data (blood group, allergies, emergency contact) |
| `doctor_availability` | Weekly time slots + max patients |
| `appointments` | Bookings with status (pending → confirmed → completed / cancelled) |
| `queue` | Live waiting list for a doctor on a given day |
| `prescriptions` | Medicines and instructions written by doctors |
| `medical_history` | Diagnosis / treatment notes |
| `invoices` | Bills for consultations or services |
| `payments` | Money received against invoices |
| `doctor_reviews` | 1–5 star ratings + written reviews |
| `queue_alerts` | Call / reminder / delay messages for queue entries |
| `wheelchair_requests` | Assistance requests with status tracking |
| `notices` | Announcements targeted at one role or everyone |
| `activity_logs` | Who did what, when, from which IP |

---

## 10. Quick demo path (good for viva / presentation)

1. Log in as **patient1** → search doctors → book an appointment.
2. Log in as **reception** → confirm the appointment → add patient to the queue → create an invoice.
3. Log in as **dr.smith** → open the queue → complete the appointment → write a prescription and a history note.
4. Back as **reception** → record the payment.
5. As **patient1** → view prescription & history → leave a review.
6. As **admin** → open the activity log and show the full trail.

Also demonstrate that a patient cannot open an admin URL (role guard redirects them).

---

Copyright (c) 2026. All rights reserved.
