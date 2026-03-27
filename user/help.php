<?php
// Tutorials & Setup Guide | آموزش و راهنمای اتصال
require_once '../config/db.php';
$guides = $db->query("SELECT * FROM guides ORDER BY id DESC")->fetchAll();
?>
<div class="container py-4" dir="rtl">
    <h4 class="mb-4">آموزش اتصال به سرویس</h4>
    <?php foreach($guides as $guide): ?>
        <div class="accordion mb-2" id="guideAcc">
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#g<?= $guide['id'] ?>">
                        <?= $guide['title'] ?>
                    </button>
                </h2>
                <div id="g<?= $guide['id'] ?>" class="accordion-collapse collapse" data-bs-parent="#guideAcc">
                    <div class="accordion-body text-muted">
                        <?= nl2br($guide['content']) ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>