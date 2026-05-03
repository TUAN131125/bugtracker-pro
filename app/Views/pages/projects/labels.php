<?php
/** @var array  $project    */
/** @var array  $labels     */
/** @var string $csrf_token */
$labels = $labels ?? [];
?>

<div class="d-flex align-items-center gap-2 mb-4">
    <a href="<?= APP_URL ?>/projects/<?= e(strtolower($project['key'])) ?>"
       class="btn btn-sm btn-outline-secondary">← Quay lại</a>
    <h4 class="fw-bold mb-0">
        <i class="fa fa-tags me-2 text-primary"></i>
        Labels — <?= e($project['name']) ?>
    </h4>
</div>

<div class="row g-4">
    <!-- Danh sách labels -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header fw-bold" style="font-size:14px;">
                <?= count($labels) ?> labels
            </div>
            <?php if (empty($labels)): ?>
            <div class="card-body text-center text-muted py-4">
                <i class="fa fa-tags fa-2x mb-2 d-block"></i>
                Chưa có label nào. Tạo label đầu tiên!
            </div>
            <?php else: ?>
            <ul class="list-group list-group-flush">
                <?php foreach ($labels as $label): ?>
                <li class="list-group-item d-flex align-items-center gap-3 py-2">
                    <!-- Color preview -->
                    <span style="display:inline-block;width:14px;height:14px;
                                 border-radius:50%;background:<?= e($label['color']) ?>;
                                 flex-shrink:0;"></span>

                    <!-- Badge preview -->
                    <span class="badge rounded-pill px-3"
                          style="background:<?= e($label['color']) ?>22;
                                 color:<?= e($label['color']) ?>;
                                 border:1px solid <?= e($label['color']) ?>44;
                                 font-size:13px;">
                        <?= e($label['name']) ?>
                    </span>

                    <!-- Usage count -->
                    <span class="text-muted ms-auto" style="font-size:12px;">
                        <?= $label['usage_count'] ?> issues
                    </span>

                    <!-- Delete -->
                    <form method="POST"
                          action="<?= APP_URL ?>/projects/<?= e(strtolower($project['key'])) ?>/labels/<?= $label['id'] ?>/delete"
                          style="margin:0;">
                        <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
                        <button type="submit"
                                class="btn btn-sm btn-outline-danger"
                                style="font-size:11px;padding:2px 8px;"
                                data-confirm="Xóa label '<?= e($label['name']) ?>'?">
                            <i class="fa fa-trash"></i>
                        </button>
                    </form>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
        </div>
    </div>

    <!-- Form tạo label mới -->
    <div class="col-lg-4">
        <div class="card p-3">
            <h6 class="fw-bold mb-3">
                <i class="fa fa-plus me-2 text-primary"></i>Tạo Label Mới
            </h6>
            <form method="POST"
                  action="<?= APP_URL ?>/projects/<?= e(strtolower($project['key'])) ?>/labels">
                <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">

                <div class="mb-3">
                    <label class="form-label fw-500">Tên Label</label>
                    <input type="text"
                           name="name"
                           class="form-control form-control-sm"
                           placeholder="vd: frontend, critical-fix..."
                           required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-500">Màu sắc</label>
                    <div class="d-flex align-items-center gap-2">
                        <input type="color"
                               name="color"
                               value="#0078D4"
                               class="form-control form-control-color form-control-sm"
                               style="width:50px;padding:2px;">
                        <!-- Preset colors -->
                        <div class="d-flex flex-wrap gap-1">
                            <?php
                            $presets = [
                                '#DC3545','#FD7E14','#FFC107',
                                '#28A745','#0078D4','#6A1B9A',
                                '#17A2B8','#6C757D','#E91E63',
                            ];
                            foreach ($presets as $preset):
                            ?>
                            <div onclick="document.querySelector('[name=color]').value='<?= $preset ?>'"
                                 style="width:20px;height:20px;border-radius:4px;
                                        background:<?= $preset ?>;cursor:pointer;
                                        border:2px solid transparent;transition:.15s;"
                                 title="<?= $preset ?>"
                                 onmouseover="this.style.transform='scale(1.2)'"
                                 onmouseout="this.style.transform='scale(1)'">
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-sm w-100 fw-bold">
                    <i class="fa fa-plus me-1"></i>Tạo Label
                </button>
            </form>

            <!-- Preset labels nhanh -->
            <hr>
            <p class="text-muted mb-2" style="font-size:12px;">Tạo nhanh từ preset:</p>
            <div class="d-flex flex-wrap gap-1">
                <?php
                $presetLabels = [
                    ['bug',         '#DC3545'],
                    ['feature',     '#0078D4'],
                    ['hotfix',      '#E91E63'],
                    ['improvement', '#FD7E14'],
                    ['frontend',    '#17A2B8'],
                    ['backend',     '#6A1B9A'],
                    ['database',    '#28A745'],
                    ['security',    '#FF5722'],
                ];
                foreach ($presetLabels as [$pName, $pColor]):
                ?>
                <form method="POST"
                      action="<?= APP_URL ?>/projects/<?= e(strtolower($project['key'])) ?>/labels"
                      style="margin:0;">
                    <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
                    <input type="hidden" name="name"  value="<?= $pName ?>">
                    <input type="hidden" name="color" value="<?= $pColor ?>">
                    <button type="submit"
                            class="badge border-0 px-2 py-1"
                            style="background:<?= $pColor ?>22;color:<?= $pColor ?>;
                                   border:1px solid <?= $pColor ?>44 !important;
                                   cursor:pointer;font-size:12px;">
                        + <?= $pName ?>
                    </button>
                </form>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>