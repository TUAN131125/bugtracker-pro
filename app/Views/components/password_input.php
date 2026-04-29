<?php
/**
 * @var string $name
 * @var string $label
 * @var string $placeholder
 * @var array $errors
 */
// $name, $label, $placeholder, $errors truyền vào
$error = $errors[$name] ?? '';
?>
<div class="mb-3">
    <label class="form-label fw-500"><?= e($label) ?></label>
    <div class="input-group">
        <input type="password"
               id="<?= e($name) ?>"
               name="<?= e($name) ?>"
               class="form-control <?= $error ? 'is-invalid' : '' ?>"
               placeholder="<?= e($placeholder) ?>"
               required>
        <button class="btn btn-outline-secondary" type="button" id="togglePw" data-target="<?= e($name) ?>">
            <i class="fa fa-eye"></i>
        </button>
    </div>
    <?php if ($error): ?>
    <div class="text-danger small mt-1">
        <i class="fa fa-times-circle me-1"></i><?= e($error) ?>
    </div>
    <?php endif; ?>
</div>