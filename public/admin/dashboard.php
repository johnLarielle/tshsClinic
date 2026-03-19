<?php
require_once __DIR__ . '/../../app/includes/check_session.php';
checkSession('admin');
$user = getSessionUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Record Management System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            padding: 20px;
            min-height: 100vh;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        h1 {
            color: #1e3a8a;
            font-size: 2.5em;
            margin-bottom: 10px;
        }

        .welcome {
            color: #666;
            font-size: 1.1em;
        }

        .logout-btn {
            float: right;
            padding: 10px 20px;
            background: #dc2626;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: background 0.3s;
        }

        .logout-btn:hover {
            background: #b91c1c;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 30px;
        }

        .dashboard-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
            text-decoration: none;
            color: inherit;
            display: block;
        }

        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }

        .card-icon {
            width: 60px;
            height: 60px;
            background: #1e3a8a;
            color: white;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2em;
            margin-bottom: 20px;
        }

        .card-title {
            font-size: 1.5em;
            color: #333;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .card-description {
            color: #666;
            line-height: 1.6;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }

        .stat-number {
            font-size: 3em;
            color: #1e3a8a;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .stat-label {
            color: #666;
            font-size: 1.1em;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <a href="../../routes/auth_api.php?action=logout" class="logout-btn">Logout</a>
            <h1>Admin Dashboard</h1>
            <p class="welcome">Welcome back, <strong><?php echo htmlspecialchars($user['fullname']); ?></strong></p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number" id="totalRecords">0</div>
                <div class="stat-label">Total Records</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="totalPatients">0</div>
                <div class="stat-label">Total Patients</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="totalMedicines">0</div>
                <div class="stat-label">Total Medicines</div>
            </div>
        </div>

        <div class="dashboard-grid">
            <a href="records.php" class="dashboard-card">
                <div class="card-icon">📋</div>
                <h2 class="card-title">Patient Records</h2>
                <p class="card-description">View, add, edit, and delete patient records. Full CRUD access to manage all medical records in the system.</p>
            </a>

            <a href="medicine.php" class="dashboard-card">
                <div class="card-icon">💊</div>
                <h2 class="card-title">Medicine Management</h2>
                <p class="card-description">Manage medicine inventory, update stock levels, add new medicines, and track medicine usage.</p>
            </a>

            <a href="../index.php" class="dashboard-card">
                <div class="card-icon">👁️</div>
                <h2 class="card-title">Public View</h2>
                <p class="card-description">View the public-facing page where users can add records. See what regular users see.</p>
            </a>
        </div>
    </div>

    <script>
        // Load statistics
        async function loadStats() {
            try {
                // Get total records
                const recordsResponse = await fetch('../../routes/api.php?action=read');
                const recordsResult = await recordsResponse.json();
                if (recordsResult.success && recordsResult.data) {
                    document.getElementById('totalRecords').textContent = recordsResult.data.length;
                }

                // Get total medicines
                const medicinesResponse = await fetch('../../routes/medicine_api.php?action=read');
                const medicinesResult = await medicinesResponse.json();
                if (medicinesResult.success && medicinesResult.data) {
                    document.getElementById('totalMedicines').textContent = medicinesResult.data.length;
                }
            } catch (error) {
                console.error('Error loading stats:', error);
            }
        }

        document.addEventListener('DOMContentLoaded', loadStats);
    </script>
</body>
</html>
