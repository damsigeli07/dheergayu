<?php
$personalProducts = json_decode($consultation['personal_products'] ?? '[]', true);
if (!is_array($personalProducts)) $personalProducts = [];
$isDispatched = isset($dispatchStatuses[$consultation['id']]) && $dispatchStatuses[$consultation['id']] === 'Dispatched';
?>
<div class="order-card <?= $isDispatched ? 'row-dispatched' : '' ?>" data-consultation-id="<?= (int)$consultation['id'] ?>">
    <div class="order-header">
        <div class="order-id-section">
            <span class="order-id-label">Consultation ID</span>
            <span class="order-id-value">#<?= htmlspecialchars($consultation['id']) ?></span>
            <span class="patient-name"><?= htmlspecialchars($consultation['first_name'] . ' ' . $consultation['last_name']) ?></span>
        </div>
        <span class="order-status-badge <?= $isDispatched ? 'dispatched' : 'pending' ?>"><?= $isDispatched ? 'Dispatched' : 'Pending' ?></span>
    </div>
    <div class="order-body">
        <div class="medicines-section">
            <div class="medicines-label">Medicines Prescribed</div>
            <div class="medicines-list">
                <?php if (!empty($personalProducts)): ?>
                    <?php foreach ($personalProducts as $item): ?>
                        <?php $pName = isset($item['product']) ? $item['product'] : ''; $pQty = isset($item['qty']) ? (int)$item['qty'] : 1; if ($pName === '') continue; ?>
                        <div class="medicine-card">
                            <span class="medicine-name"><?= htmlspecialchars($pName) ?></span>
                            <span class="medicine-qty">x<?= $pQty ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-medicines">No medicines prescribed</div>
                <?php endif; ?>
            </div>
        </div>
        <div class="order-actions-section">
            <div class="total-section">
                <div class="total-label">Total Amount</div>
                <button type="button" class="total-button" onclick="calculateTotal('<?= $consultation['id'] ?>')">View Total</button>
            </div>
            <div class="dispatch-section">
                <label class="dispatch-label">
                    <input type="checkbox" class="dispense-status" <?= $isDispatched ? 'checked' : '' ?> onchange="toggleDispatch(<?= (int)$consultation['id'] ?>, this.checked)">
                    <span>Mark as Dispatched</span>
                </label>
                <?php if ($isDispatched): ?>
                <div class="dispatch-note">Stock deducted from inventory.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
