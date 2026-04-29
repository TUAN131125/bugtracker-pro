<?php
/**
 * @var string $field
 * @var array $errors
 */
// Dùng như: <?= include APP_PATH . '/Views/components/form_error.php'; ?>
// với $field và $errors truyền vào
if (!empty($errors[$field] ?? '')) {
    echo '<div class="invalid-feedback d-block">
    <i class="fa fa-times-circle me-1"></i>' . e($errors[$field]) . '
</div>';
}