<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register &mdash; Hospital Management System</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-body">

<div class="auth-shell">
    <div class="auth-side">
        <div class="logo-big">🏥</div>
        <h1>Create account</h1>
        <p>Register as a patient, doctor or receptionist to use HospitalMS.</p>
        <ul class="feature-list">
            <li>✓ <strong>Patient</strong> — book doctors, pay invoices & view history</li>
            <li>✓ <strong>Doctor</strong> — manage appointments, prescriptions & queue</li>
            <li>✓ <strong>Receptionist</strong> — patients, invoices, queue & billing</li>
        </ul>
        <p class="side-note">Administrator accounts are created by an existing admin only.</p>
    </div>

    <div class="auth-form-wrap">
        <div class="auth-card">
            <h2>Sign up</h2>
            <p class="muted">It takes less than a minute</p>

            <?php $flash = get_flash(); if ($flash): ?>
                <div class="alert alert-<?= ($flash['type']==='danger'||$flash['type']==='error') ? 'error' : esc($flash['type']) ?>">
                    <?= esc($flash['message']) ?>
                </div>
            <?php endif; ?>

            <?php
            // Defaults when page is first loaded (GET)
            if (!isset($old) || !is_array($old)) {
                $old = [
                    'role' => 'patient',
                    'full_name' => '',
                    'username' => '',
                    'email' => '',
                    'phone' => '',
                    'gender' => '',
                    'date_of_birth' => '',
                    'specialization' => '',
                    'blood_group' => '',
                    'emergency_contact' => ''
                ];
            }
            $role = $old['role'] ?? 'patient';
            ?>

            <form method="POST" action="index.php?page=register" class="form"
                  novalidate onsubmit="return validateForm(this);">
                <?= csrf_field() ?>

                <div class="field">
                    <label for="role">Account type</label>
                    <select id="role" name="role" data-label="Account type" required>
                        <option value="patient" <?= $role === 'patient' ? 'selected' : '' ?>>Patient</option>
                        <option value="doctor" <?= $role === 'doctor' ? 'selected' : '' ?>>Doctor</option>
                        <option value="receptionist" <?= $role === 'receptionist' ? 'selected' : '' ?>>Receptionist</option>
                    </select>
                </div>

                <div class="field">
                    <label for="full_name">Full name</label>
                    <input type="text" id="full_name" name="full_name" data-label="Full name" data-min="3"
                           value="<?= esc($old['full_name'] ?? '') ?>" placeholder="e.g. Dr. Rafiq Hasan" required>
                </div>

                <div class="field-row">
                    <div class="field">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" data-label="Username" data-min="3"
                               value="<?= esc($old['username'] ?? '') ?>" placeholder="At least 3 characters" required>
                        <span id="usernameNote" class="field-note"></span>
                    </div>
                    <div class="field">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" data-label="Email"
                               value="<?= esc($old['email'] ?? '') ?>" placeholder="you@example.com" required>
                    </div>
                </div>

                <div class="field-row">
                    <div class="field">
                        <label for="phone">Phone</label>
                        <input type="text" id="phone" name="phone" data-label="Phone"
                               value="<?= esc($old['phone'] ?? '') ?>" placeholder="e.g. 017XXXXXXXX">
                    </div>
                    <div class="field">
                        <label for="gender">Gender</label>
                        <select id="gender" name="gender" data-label="Gender">
                            <option value="">— Select —</option>
                            <option value="male" <?= ($old['gender'] ?? '') === 'male' ? 'selected' : '' ?>>Male</option>
                            <option value="female" <?= ($old['gender'] ?? '') === 'female' ? 'selected' : '' ?>>Female</option>
                            <option value="other" <?= ($old['gender'] ?? '') === 'other' ? 'selected' : '' ?>>Other</option>
                        </select>
                    </div>
                </div>

                <div class="field">
                    <label for="date_of_birth">Date of birth</label>
                    <input type="date" id="date_of_birth" name="date_of_birth"
                           value="<?= esc($old['date_of_birth'] ?? '') ?>">
                </div>

                <!-- Doctor-only -->
                <div class="field role-extra" id="doctorFields" style="<?= $role === 'doctor' ? '' : 'display:none;' ?>">
                    <label for="specialization">Specialization</label>
                    <input type="text" id="specialization" name="specialization" data-label="Specialization"
                           value="<?= esc($old['specialization'] ?? '') ?>" placeholder="e.g. Cardiology, Medicine">
                </div>

                <!-- Patient-only -->
                <div class="field-row role-extra" id="patientFields" style="<?= $role === 'patient' ? '' : 'display:none;' ?>">
                    <div class="field">
                        <label for="blood_group">Blood group</label>
                        <select id="blood_group" name="blood_group">
                            <option value="">— Select —</option>
                            <?php
                            $bgs = ['A+','A-','B+','B-','AB+','AB-','O+','O-'];
                            $curBg = $old['blood_group'] ?? '';
                            foreach ($bgs as $bg):
                            ?>
                            <option value="<?= $bg ?>" <?= $curBg === $bg ? 'selected' : '' ?>><?= $bg ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="field">
                        <label for="emergency_contact">Emergency contact</label>
                        <input type="text" id="emergency_contact" name="emergency_contact"
                               value="<?= esc($old['emergency_contact'] ?? '') ?>" placeholder="Phone number">
                    </div>
                </div>

                <div class="field-row">
                    <div class="field">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" data-label="Password" data-min="6"
                               placeholder="At least 6 characters" required>
                    </div>
                    <div class="field">
                        <label for="confirm_password">Confirm password</label>
                        <input type="password" id="confirm_password" name="confirm_password"
                               data-label="Confirm password" data-match="password"
                               placeholder="Type it again" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Create account</button>
            </form>

            <p class="auth-switch">
                Already have an account? <a href="index.php?page=login">Sign in</a>
            </p>
        </div>
    </div>
</div>

<script src="assets/js/app.js"></script>
<script>
(function () {
    var roleSelect = document.getElementById('role');
    var doctorFields = document.getElementById('doctorFields');
    var patientFields = document.getElementById('patientFields');
    var specInput = document.getElementById('specialization');

    function toggleRoleFields() {
        var r = roleSelect.value;
        if (doctorFields) doctorFields.style.display = (r === 'doctor') ? '' : 'none';
        if (patientFields) patientFields.style.display = (r === 'patient') ? '' : 'none';
        if (specInput) {
            if (r === 'doctor') {
                specInput.setAttribute('required', 'required');
            } else {
                specInput.removeAttribute('required');
            }
        }
    }
    roleSelect.addEventListener('change', toggleRoleFields);
    toggleRoleFields();

    // Live username availability (public endpoint)
    var input = document.getElementById('username');
    var note  = document.getElementById('usernameNote');
    var timer;
    if (input && note) {
        input.addEventListener('input', function () {
            clearTimeout(timer);
            var value = input.value.trim();
            if (value === '') { note.textContent = ''; note.className = 'field-note'; return; }
            timer = setTimeout(function () {
                fetch('index.php?page=ajax&action=check_username&username=' + encodeURIComponent(value))
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        if (data.exists) {
                            note.textContent = 'Username already taken';
                            note.className = 'field-note note-bad';
                        } else if (data.success !== false) {
                            note.textContent = 'Username available';
                            note.className = 'field-note note-ok';
                        } else {
                            note.textContent = '';
                        }
                    })
                    .catch(function () { note.textContent = ''; });
            }, 300);
        });
    }
})();
</script>
</body>
</html>
