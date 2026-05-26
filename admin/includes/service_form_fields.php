<?php
function renderServiceFields(array $s, array $providers): void
{
    $s = array_merge([
        'name' => '', 'category' => 'General', 'description' => '',
        'rate' => '', 'cost_rate' => '', 'min_quantity' => 100, 'max_quantity' => 100000,
        'provider_id' => '', 'provider_service_id' => '', 'status' => 'active',
    ], $s);
    ?>
    <div class="row g-3">
        <div class="col-md-8">
            <label class="form-label">Name</label>
            <input type="text" name="name" class="form-control" value="<?= sanitize($s['name']) ?>" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Category</label>
            <input type="text" name="category" class="form-control" value="<?= sanitize($s['category']) ?>" required>
        </div>
        <div class="col-12">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="2"><?= sanitize($s['description']) ?></textarea>
        </div>
        <div class="col-md-4">
            <label class="form-label">Rate per 1K ($)</label>
            <input type="number" name="rate" class="form-control" step="0.0001" value="<?= $s['rate'] ?>" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Cost per 1K ($)</label>
            <input type="number" name="cost_rate" class="form-control" step="0.0001" value="<?= $s['cost_rate'] ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="active" <?= $s['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                <option value="inactive" <?= $s['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Min Quantity</label>
            <input type="number" name="min_quantity" class="form-control" value="<?= $s['min_quantity'] ?>" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Max Quantity</label>
            <input type="number" name="max_quantity" class="form-control" value="<?= $s['max_quantity'] ?>" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Provider</label>
            <select name="provider_id" class="form-select">
                <option value="">None</option>
                <?php foreach ($providers as $p): ?>
                <option value="<?= $p['id'] ?>" <?= (string)$s['provider_id'] === (string)$p['id'] ? 'selected' : '' ?>><?= sanitize($p['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Provider Service ID</label>
            <input type="text" name="provider_service_id" class="form-control" value="<?= sanitize($s['provider_service_id'] ?? '') ?>" placeholder="e.g. 1234">
        </div>
    </div>
    <?php
}
