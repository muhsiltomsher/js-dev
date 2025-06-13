<?php
/**
 * Template Name: Custom Reset Password Page
 */

defined('ABSPATH') || exit;

if (is_user_logged_in()) {
    wp_redirect(home_url('/my-profile/?section=dashboard'));
    exit;
}

include get_template_directory() . '/layouts/header.php';

$login = isset($_GET['login']) ? sanitize_user($_GET['login']) : '';
$key = isset($_GET['key']) ? sanitize_text_field($_GET['key']) : '';
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_password'])) {
    $user = check_password_reset_key($key, $login);

    if (is_wp_error($user)) {
        $errors[] = $user->get_error_message();
    } else {
        $pass1 = $_POST['new_password'];
        $pass2 = $_POST['confirm_password'];

        if (empty($pass1) || empty($pass2)) {
            $errors[] = 'Please fill in all password fields.';
        } elseif ($pass1 !== $pass2) {
            $errors[] = 'Passwords do not match.';
        } else {
            reset_password($user, $pass1);
$success = 'Password reset successful. <a href="' . esc_url(site_url('/my-account/')) . '" class="underline">Login now</a>.';
        }
    }
}
?>

<div class="min-h-screen bg-gray-100 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full bg-white p-8 rounded shadow">
        <h2 class="text-center text-2xl font-bold text-gray-800">Set a New Password</h2>

        <?php if (!empty($errors)) : ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mt-4" id="reset-error">
                <?php foreach ($errors as $error) : ?>
                    <p><?php echo esc_html($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)) : ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mt-4" role="alert">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <?php if (empty($success)) : ?>
        <form class="mt-6 space-y-4" method="post" id="reset-password-form">
            <input type="hidden" name="login" value="<?php echo esc_attr($login); ?>">
            <input type="hidden" name="key" value="<?php echo esc_attr($key); ?>">

            <div>
                <label class="block text-sm font-medium text-gray-700">New Password</label>
                <input type="password" name="new_password" required     class="appearance-none rounded-md relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm" 
>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Confirm New Password</label>
                <input type="password" name="confirm_password" required     class="appearance-none rounded-md relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm" 
>
            </div>

            <button type="submit" class="w-full bg-indigo-600 text-white py-2 px-4 rounded hover:bg-indigo-700">
                Reset Password
            </button>
        </form>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('reset-password-form');
    if (!form) return;

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        const formData = new FormData(form);
        const data = new URLSearchParams(formData);

        fetch(window.location.href, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: data
        })
        .then(response => response.text())
        .then(html => {
            document.body.innerHTML = html;
        })
        .catch(() => {
            const errorBox = document.createElement('div');
            errorBox.className = 'bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mt-4';
            errorBox.innerText = 'Something went wrong. Please try again.';
            form.before(errorBox);
        });
    });
});
</script>

<?php include get_template_directory() . '/layouts/footer.php'; ?>
