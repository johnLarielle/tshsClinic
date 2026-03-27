<?php

require_once __DIR__ . '/../app/Config/Config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Admin-only
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['user_type'] !== 'admin') {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Admin authentication required']);
    exit();
}

try {
    $database = new Database();
    $db       = $database->connect();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
    exit();
}

$action = $_GET['action'] ?? '';

switch ($action) {

    // ── Overview KPI cards ────────────────────────────────────
    case 'overview':
        $deleted = "(dateDeleted IS NULL OR dateDeleted = '0000-00-00 00:00:00')";

        $rows = $db->query("SELECT COUNT(*) FROM patient_record WHERE $deleted")->fetchColumn();
        $patients = $db->query("SELECT COUNT(*) FROM patient WHERE $deleted")->fetchColumn();
        $medicines = $db->query("SELECT COUNT(*) FROM medicine WHERE $deleted")->fetchColumn();
        $lowStock  = $db->query("SELECT COUNT(*) FROM medicine WHERE current_stock < 10 AND $deleted")->fetchColumn();
        $dispensed = $db->query("SELECT COALESCE(SUM(quantity),0) FROM patient_record WHERE $deleted")->fetchColumn();

        // Records added today
        $today = $db->query("SELECT COUNT(*) FROM patient_record WHERE DATE(dateCreated) = CURDATE() AND $deleted")->fetchColumn();

        // Records added this month
        $thisMonth = $db->query("SELECT COUNT(*) FROM patient_record WHERE YEAR(dateCreated) = YEAR(CURDATE()) AND MONTH(dateCreated) = MONTH(CURDATE()) AND $deleted")->fetchColumn();

        echo json_encode([
            'success'          => true,
            'total_records'    => (int) $rows,
            'total_patients'   => (int) $patients,
            'total_medicines'  => (int) $medicines,
            'low_stock_count'  => (int) $lowStock,
            'total_dispensed'  => (int) $dispensed,
            'records_today'    => (int) $today,
            'records_month'    => (int) $thisMonth,
        ]);
        break;

    // ── Records per day (last N days) ─────────────────────────
    case 'records_by_day':
        $days   = max(7, min(365, (int) ($_GET['days'] ?? 30)));
        $deleted = "(pr.dateDeleted IS NULL OR pr.dateDeleted = '0000-00-00 00:00:00')";

        $stmt = $db->prepare("
            SELECT
                DATE(date_given)  AS day,
                COUNT(*)          AS total_records,
                SUM(quantity)     AS total_qty
            FROM patient_record pr
            WHERE date_given >= DATE_SUB(CURDATE(), INTERVAL :days DAY)
              AND $deleted
            GROUP BY DATE(date_given)
            ORDER BY day ASC
        ");
        $stmt->bindValue(':days', $days, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Fill all days even if no records
        $map = [];
        foreach ($rows as $r) { $map[$r['day']] = $r; }

        $filled = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-$i days"));
            $filled[] = [
                'day'           => $d,
                'total_records' => isset($map[$d]) ? (int) $map[$d]['total_records'] : 0,
                'total_qty'     => isset($map[$d]) ? (int) $map[$d]['total_qty']     : 0,
            ];
        }

        echo json_encode(['success' => true, 'data' => $filled, 'days' => $days]);
        break;

    // ── Patient type breakdown ─────────────────────────────────
    case 'patient_types':
        $deleted = "(dateDeleted IS NULL OR dateDeleted = '0000-00-00 00:00:00')";
        $stmt = $db->query("
            SELECT patient_type, COUNT(*) AS total
            FROM patient
            WHERE $deleted
            GROUP BY patient_type
            ORDER BY total DESC
        ");
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
        break;

    // ── Top dispensed medicines ────────────────────────────────
    case 'top_medicines':
        $limit = max(1, min(20, (int) ($_GET['limit'] ?? 10)));
        $deleted = "(pr.dateDeleted IS NULL OR pr.dateDeleted = '0000-00-00 00:00:00')";

        $stmt = $db->prepare("
            SELECT
                m.medicine_name,
                COUNT(pr.record_id)  AS times_dispensed,
                SUM(pr.quantity)     AS total_qty
            FROM patient_record pr
            INNER JOIN medicine m ON pr.medicine_id = m.medicine_id
            WHERE $deleted
            GROUP BY pr.medicine_id, m.medicine_name
            ORDER BY total_qty DESC
            LIMIT :lim
        ");
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
        break;

    // ── Medicine stock levels ──────────────────────────────────
    case 'stock_levels':
        $deleted = "(dateDeleted IS NULL OR dateDeleted = '0000-00-00 00:00:00')";
        $stmt = $db->query("
            SELECT medicine_name, current_stock
            FROM medicine
            WHERE $deleted
            ORDER BY current_stock ASC
        ");
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
        break;

    // ── Records by patient type over time ─────────────────────
    case 'type_over_time':
        $days    = max(7, min(90, (int) ($_GET['days'] ?? 30)));
        $prDel   = "(pr.dateDeleted IS NULL OR pr.dateDeleted = '0000-00-00 00:00:00')";

        $stmt = $db->prepare("
            SELECT
                DATE(pr.date_given) AS day,
                p.patient_type,
                COUNT(*)            AS total
            FROM patient_record pr
            INNER JOIN patient p ON pr.patient_id = p.patient_id
            WHERE pr.date_given >= DATE_SUB(CURDATE(), INTERVAL :days DAY)
              AND $prDel
            GROUP BY DATE(pr.date_given), p.patient_type
            ORDER BY day ASC
        ");
        $stmt->bindValue(':days', $days, PDO::PARAM_INT);
        $stmt->execute();
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC), 'days' => $days]);
        break;

    // ── Recent records ────────────────────────────────────────
    case 'recent_records':
        $limit = max(1, min(20, (int) ($_GET['limit'] ?? 8)));
        $deleted = "(pr.dateDeleted IS NULL OR pr.dateDeleted = '0000-00-00 00:00:00')";

        $stmt = $db->prepare("
            SELECT
                pr.record_id,
                p.fullname    AS patient_name,
                p.patient_type,
                m.medicine_name,
                pr.quantity,
                pr.date_given,
                pr.reason
            FROM patient_record pr
            LEFT JOIN patient  p ON pr.patient_id  = p.patient_id
            LEFT JOIN medicine m ON pr.medicine_id = m.medicine_id
            WHERE $deleted
            ORDER BY pr.dateCreated DESC, pr.record_id DESC
            LIMIT :lim
        ");
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
        break;

    // ── Top symptoms ──────────────────────────────────────────
    case 'top_symptoms':
        $limit   = max(1, min(20, (int) ($_GET['limit'] ?? 10)));
        $deleted = "(pr.dateDeleted IS NULL OR pr.dateDeleted = '0000-00-00 00:00:00')";

        $stmt = $db->prepare("
            SELECT
                pr.reason          AS symptom,
                COUNT(*)           AS total_cases,
                SUM(pr.quantity)   AS total_qty
            FROM patient_record pr
            WHERE pr.reason IS NOT NULL
              AND pr.reason != ''
              AND $deleted
            GROUP BY pr.reason
            ORDER BY total_cases DESC
            LIMIT :lim
        ");
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
        break;

    default:
        http_response_code(404);
        echo json_encode(['error' => 'Invalid action']);
        break;
}
?>
