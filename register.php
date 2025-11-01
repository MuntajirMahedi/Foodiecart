<?php
require_once 'config.php';
require_once 'includes/functions.php';

if (isLoggedIn()) {
    redirect('index.php');
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    $phone = sanitize($_POST['phone'] ?? '');
    $address = sanitize($_POST['address'] ?? '');

    // ---- BASIC VALIDATIONS ----
    if (!$name || !$email || !$password || !$confirm || !$phone || !$address) {
        $errors[] = 'All fields are required. Please fill in every field.';
    }

    // Gmail-only validation
    if ($email && !preg_match('/^[a-zA-Z0-9._%+-]+@gmail\.com$/i', trim($email))) {
    $errors[] = 'Only Gmail addresses are allowed (must end with @gmail.com).';
    }

    // Password validations
    if ($password && strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    }

    if ($password !== $confirm) {
        $errors[] = 'Passwords do not match.';
    }

    // ---- PHONE VALIDATION ----
    if ($phone) {
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);

        // Remove leading 0 or 91 if present
        if (preg_match('/^(0|91)/', $cleanPhone)) {
            $cleanPhone = preg_replace('/^(0|91)/', '', $cleanPhone);
        }

        // Must be exactly 10 digits
        if (strlen($cleanPhone) !== 10) {
            $errors[] = 'Phone number must be exactly 10 digits.';
        } else {
            $phone = '+91' . $cleanPhone;
        }
    }

    // ---- PROCESS IF VALID ----
    if (empty($errors)) {
        $result = registerUser($name, $email, $password, $phone, $address);
        if ($result['success']) {
            loginUser($email, $password);
            redirect('index.php');
        } else {
            $errors[] = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .auth-card{background:#fff;padding:24px;border-radius:12px;box-shadow:0 4px 12px rgba(0,0,0,.08)}
        .auth-field{width:100%;margin-top:6px}
        .alert{background:#ffe8e8;color:#b00020;padding:12px 16px;border-radius:8px;margin-bottom:1rem}
        .auth-wrap{max-width:600px;padding:40px 0;margin:auto;}
        label{font-weight:600;}
        .mb-3{margin-bottom:1rem;}
        .phone-wrap{display:flex;align-items:center;gap:8px;}
        .phone-prefix{background:#f1f1f1;padding:8px 12px;border-radius:8px;}
    </style>
</head>
<body>
    <div class="container auth-wrap">
        <h2 class="section-title" style="margin-bottom:1rem;">Create your account</h2>
        <p class="text-center" style="margin-bottom:1.5rem;">Join <?php echo SITE_NAME; ?> today</p>

        <?php if (!empty($errors)): ?>
            <div class="alert">
                <?php echo implode('<br>', $errors); ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="auth-card" id="registerForm">
            <div class="mb-3">
                <label for="name">Full Name</label>
                <input type="text" name="name" id="name" class="search-input auth-field" 
                       value="<?php echo htmlspecialchars($name ?? ''); ?>" required>
            </div>
            <div class="mb-3">
                <label for="email">Email (Gmail only)</label>
                <input type="email" name="email" id="email" class="search-input auth-field" 
                       value="<?php echo htmlspecialchars($email ?? ''); ?>" 
                       pattern="^[a-zA-Z0-9._%+-]+@gmail\.com$"
                       title="Please enter a valid Gmail address (must end with @gmail.com)" required>
            </div>
            <div class="mb-3">
                <label for="phone">Phone (India only)</label>
                <div class="phone-wrap">
                    <span class="phone-prefix">+91</span>
                    <input type="text" name="phone" id="phone" class="search-input auth-field" 
                           maxlength="10" pattern="[0-9]{10}" 
                           placeholder="Enter 10-digit mobile number"
                           oninput="this.value=this.value.replace(/[^0-9]/g,'')" 
                           value="<?php echo htmlspecialchars(preg_replace('/^\+91/', '', $phone ?? '')); ?>" required>
                </div>
            </div>
            <div class="mb-3">
                <label for="address">Address</label>
                <textarea name="address" id="address" class="search-input auth-field" style="height:100px;" required><?php echo htmlspecialchars($address ?? ''); ?></textarea>
            </div>
            <div class="mb-3">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" class="search-input auth-field" required>
            </div>
            <div class="mb-3">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" name="confirm_password" id="confirm_password" class="search-input auth-field" required>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;">Create Account</button>
            <p class="text-center mt-2">Already have an account? <a href="login.php">Login</a></p>
        </form>
    </div>

    <script>
    // Frontend validation to block incomplete forms
    document.getElementById('registerForm').addEventListener('submit', function(e) {
        const fields = ['name', 'email', 'phone', 'address', 'password', 'confirm_password'];
        let valid = true;
        for (let id of fields) {
            const field = document.getElementById(id);
            if (!field.value.trim()) {
                valid = false;
                field.style.border = '2px solid red';
            } else {
                field.style.border = '';
            }
        }

        const phone = document.getElementById('phone').value.trim();
        if (phone.length !== 10) {
            valid = false;
            alert('Please enter a valid 10-digit phone number.');
        }

        if (!valid) {
            e.preventDefault();
            alert('Please fill all fields correctly before submitting.');
        }
    });
    </script>
</body>
</html>
