<?php

declare(strict_types=1);

function sl_get_student_active_requests(PDO $pdo, int $student_id): array
{
    $stmt = $pdo->prepare(
        "SELECT br.id, br.qr_token, br.status, br.requested_at,
                (SELECT GROUP_CONCAT(b.title ORDER BY b.title SEPARATOR ', ')
                 FROM borrow_request_items bri
                 JOIN books b ON b.id = bri.book_id
                 WHERE bri.borrow_request_id = br.id) AS book_titles
         FROM borrow_requests br
         WHERE br.student_id = :sid AND br.status IN ('pending', 'approved', 'claimed')
         ORDER BY br.requested_at DESC
         LIMIT 10"
    );
    $stmt->execute([':sid' => $student_id]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function sl_render_student_active_request_rows(array $requests): void
{
    if (!$requests) {
        ?>
        <tr>
            <td colspan="4" class="text-center text-muted py-4" data-label="">
                You have no active borrow requests right now.
            </td>
        </tr>
        <?php
        return;
    }

    foreach ($requests as $r) {
        $status = (string)($r['status'] ?? '');
        $badge_class = $status === 'pending' ? 'warning' : ($status === 'approved' ? 'info' : 'success');
        ?>
        <tr>
            <td data-label="Status">
                <span class="badge sl-status-badge bg-<?php echo $badge_class; ?>">
                    <?php echo htmlspecialchars($status, ENT_QUOTES, 'UTF-8'); ?>
                </span>
            </td>
            <td data-label="Requested">
                <?php echo htmlspecialchars($r['requested_at'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
            </td>
            <td data-label="Books Borrowed" class="text-break">
                <?php echo htmlspecialchars($r['book_titles'] ?? '-', ENT_QUOTES, 'UTF-8'); ?>
            </td>
            <td data-label="Action" class="text-end">
                <?php if (!empty($r['qr_token'])): ?>
                    <a href="<?php echo BASE_URL; ?>/student/qr_display.php?token=<?php echo urlencode($r['qr_token']); ?>" class="btn btn-sm btn-outline-primary">
                        View QR
                    </a>
                <?php else: ?>
                    <span class="text-muted small">N/A</span>
                <?php endif; ?>
            </td>
        </tr>
        <?php
    }
}

function sl_render_student_active_request_rows_html(array $requests): string
{
    ob_start();
    sl_render_student_active_request_rows($requests);

    return (string)ob_get_clean();
}
