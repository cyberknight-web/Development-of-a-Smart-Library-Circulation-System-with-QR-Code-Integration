<?php

declare(strict_types=1);

function sl_get_pending_borrow_requests(PDO $pdo): array
{
    $stmt = $pdo->prepare(
        "SELECT br.*, s.name AS student_name, s.student_id AS student_code, s.course, s.section, s.email,
                (SELECT GROUP_CONCAT(b.title ORDER BY b.title SEPARATOR ', ')
                 FROM borrow_request_items bri
                 JOIN books b ON b.id = bri.book_id
                 WHERE bri.borrow_request_id = br.id) AS book_titles,
                (SELECT GROUP_CONCAT(
                            DISTINCT COALESCE(NULLIF(TRIM(b.category), ''), 'Uncategorized')
                            ORDER BY COALESCE(NULLIF(TRIM(b.category), ''), 'Uncategorized') SEPARATOR ', '
                        )
                 FROM borrow_request_items bri
                 JOIN books b ON b.id = bri.book_id
                 WHERE bri.borrow_request_id = br.id) AS book_categories,
                (SELECT COALESCE(SUM(bri.quantity), 0)
                 FROM borrow_request_items bri
                 WHERE bri.borrow_request_id = br.id) AS total_copies,
                (SELECT COALESCE(SUM(bri.quantity), 0)
                 FROM borrow_requests br2
                 JOIN borrow_request_items bri ON bri.borrow_request_id = br2.id
                 WHERE br2.student_id = s.id AND br2.status IN ('pending', 'approved', 'claimed')) AS active_borrows
         FROM borrow_requests br
         JOIN students s ON s.id = br.student_id
         WHERE br.status = 'pending'
         ORDER BY br.requested_at ASC"
    );

    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function sl_count_pending_borrow_requests(PDO $pdo): int
{
    return (int)$pdo->query("SELECT COUNT(*) FROM borrow_requests WHERE status = 'pending'")->fetchColumn();
}

function sl_get_request_book_availability(PDO $pdo, array $requests): array
{
    if (!$requests) {
        return [];
    }

    $request_ids = array_map('intval', array_column($requests, 'id'));
    $placeholders = implode(',', array_fill(0, count($request_ids), '?'));

    $book_stmt = $pdo->prepare(
        "SELECT bri.borrow_request_id, b.title, b.copies_available
         FROM borrow_request_items bri
         JOIN books b ON b.id = bri.book_id
         WHERE bri.borrow_request_id IN ($placeholders)
         ORDER BY b.title ASC"
    );

    $book_stmt->execute($request_ids);

    $request_book_availability = [];
    foreach ($book_stmt->fetchAll(PDO::FETCH_ASSOC) as $book_row) {
        $request_id = (int)$book_row['borrow_request_id'];
        $request_book_availability[$request_id][] = $book_row;
    }

    return $request_book_availability;
}

function sl_render_borrow_request_rows(array $requests, array $request_book_availability): void
{
    if (!$requests) {
        ?>
        <tr>
            <td colspan="13" class="text-center text-muted">
                No pending borrow requests.
            </td>
        </tr>
        <?php
        return;
    }

    foreach ($requests as $r) {
        ?>
        <tr>
            <td data-label="Name">
                <?php echo htmlspecialchars($r['student_name'], ENT_QUOTES, 'UTF-8'); ?>
            </td>

            <td data-label="Student ID">
                <?php echo htmlspecialchars($r['student_code'], ENT_QUOTES, 'UTF-8'); ?>
            </td>

            <td data-label="Course">
                <?php echo htmlspecialchars($r['course'], ENT_QUOTES, 'UTF-8'); ?>
            </td>

            <td data-label="Year / Section">
                <?php echo htmlspecialchars($r['section'], ENT_QUOTES, 'UTF-8'); ?>
            </td>

            <td data-label="Active Borrows">
                <?php
                $active = (int)($r['active_borrows'] ?? 0);
                echo $active . ' / 3';
                ?>
            </td>

            <td data-label="Email">
                <?php echo htmlspecialchars($r['email'], ENT_QUOTES, 'UTF-8'); ?>
            </td>

            <td data-label="Requested Books" class="text-break">
                <?php $availability_books = $request_book_availability[(int)$r['id']] ?? []; ?>

                <?php if ($availability_books): ?>
                    <div class="sl-book-availability-list">
                        <?php foreach ($availability_books as $book_info): ?>
                            <?php
                            $available_copies = (int)($book_info['copies_available'] ?? 0);
                            $availability_status = $available_copies > 0 ? 'Available' : 'Not Available';
                            ?>

                            <div class="sl-book-availability-item">
                                <div>
                                    <?php echo htmlspecialchars($book_info['title'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                </div>

                                <div class="sl-book-availability-meta">
                                    Available Copies: <?php echo $available_copies; ?>
                                </div>

                                <div class="sl-book-availability-meta">
                                    Status: <?php echo htmlspecialchars($availability_status, ENT_QUOTES, 'UTF-8'); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <?php echo htmlspecialchars($r['book_titles'] ?? 'No books listed', ENT_QUOTES, 'UTF-8'); ?>
                <?php endif; ?>
            </td>

            <td data-label="Category" class="text-break">
                <?php echo htmlspecialchars($r['book_categories'] ?? 'Uncategorized', ENT_QUOTES, 'UTF-8'); ?>
            </td>

            <td data-label="Copies" class="text-center">
                <?php echo (int)($r['total_copies'] ?? 0); ?>
            </td>

            <td data-label="Notes">
                <?php if ($r['notes']): ?>
                    <span class="badge bg-info" title="<?php echo htmlspecialchars($r['notes'], ENT_QUOTES, 'UTF-8'); ?>">
                        Has note
                    </span>

                    <div class="small text-muted mt-1" style="max-width: 150px; word-wrap: break-word;">
                        <?php echo htmlspecialchars(substr($r['notes'], 0, 100), ENT_QUOTES, 'UTF-8'); ?>
                        <?php if (strlen($r['notes']) > 100): ?>
                            ...
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <span class="text-muted">&mdash;</span>
                <?php endif; ?>
            </td>

            <td data-label="QR Token">
                <?php $qr_token = $r['qr_token'] ?? ''; ?>

                <div class="sl-qr-token-cell">
                    <code
                        class="js-qr-token sl-qr-token-value"
                        data-token="<?php echo htmlspecialchars($qr_token, ENT_QUOTES, 'UTF-8'); ?>"
                    >
                        <?php echo htmlspecialchars($qr_token, ENT_QUOTES, 'UTF-8'); ?>
                    </code>

                    <button type="button" class="btn btn-outline-secondary btn-sm js-qr-copy" aria-label="Copy QR token">
                        Copy
                    </button>
                </div>

                <span class="sl-copy-message js-copy-message" aria-live="polite"></span>
            </td>

            <td data-label="Requested At">
                <?php echo htmlspecialchars($r['requested_at'], ENT_QUOTES, 'UTF-8'); ?>
            </td>

            <td data-label="Actions" class="text-center sl-borrow-actions">
                <form action="<?php echo BASE_URL; ?>/admin/borrow_request_action.php" method="post" class="d-inline">
                    <input type="hidden" name="request_id" value="<?php echo (int)$r['id']; ?>">
                    <input type="hidden" name="action" value="approve">

                    <button type="submit" class="btn btn-success btn-sm">
                        Available
                    </button>
                </form>

                <form action="<?php echo BASE_URL; ?>/admin/borrow_request_action.php" method="post" class="d-inline ms-1">
                    <input type="hidden" name="request_id" value="<?php echo (int)$r['id']; ?>">
                    <input type="hidden" name="action" value="reject">

                    <button type="submit" class="btn btn-secondary btn-sm">
                        Not Available
                    </button>
                </form>
            </td>
        </tr>
        <?php
    }
}

function sl_render_borrow_request_rows_html(array $requests, array $request_book_availability): string
{
    ob_start();
    sl_render_borrow_request_rows($requests, $request_book_availability);

    return (string)ob_get_clean();
}
