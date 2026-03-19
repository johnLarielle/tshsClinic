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
    <title>Patient Records - Admin</title>

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
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }

        h1 {
            color: #333;
            margin-bottom: 30px;
            text-align: center;
            font-size: 2.5em;
        }

        .form-container {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .form-title {
            font-size: 1.5em;
            color: #1e3a8a;
            margin-bottom: 20px;
            font-weight: bold;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        label {
            font-weight: 600;
            margin-bottom: 5px;
            color: #555;
            font-size: 0.95em;
        }

        input, select, textarea {
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 1em;
            transition: all 0.3s ease;
            font-family: inherit;
        }

        select option:disabled {
            color: #999 !important;
            font-style: italic;
        }

        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #1e3a8a;
            box-shadow: 0 0 0 3px rgba(30, 58, 138, 0.1);
        }

        textarea {
            resize: vertical;
            min-height: 80px;
        }

        .form-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 20px;
        }

        button {
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-size: 1em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: inherit;
        }

        .btn-primary {
            background: #1e3a8a;
            color: white;
        }

        .btn-primary:hover {
            background: #1e40af;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(30, 58, 138, 0.4);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
        }

        .table-container {
            overflow-x: auto;
            margin-top: 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }

        th {
            background: #1e3a8a;
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85em;
            letter-spacing: 0.5px;
        }

        td {
            padding: 15px;
            border-bottom: 1px solid #eee;
        }

        tr:hover {
            background-color: #f8f9fa;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .btn-edit, .btn-delete {
            padding: 6px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.85em;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-edit {
            background: #f59e0b;
            color: #000;
        }

        .btn-edit:hover {
            background: #d97706;
        }

        .btn-delete {
            background: #dc2626;
            color: white;
        }

        .btn-delete:hover {
            background: #b91c1c;
        }

        .message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
            display: none;
        }

        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .loading {
            text-align: center;
            padding: 20px;
            color: #1e3a8a;
            font-weight: 600;
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: #6c757d;
        }

        .empty-state svg {
            width: 100px;
            height: 100px;
            margin-bottom: 20px;
            opacity: 0.5;
        }

        #editId {
            display: none;
        }

        .required {
            color: #dc3545;
            font-weight: bold;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .header h1 {
            margin-bottom: 0;
        }

        .nav-links {
            display: flex;
            gap: 15px;
        }

        .nav-links a {
            padding: 10px 20px;
            background: #1e3a8a;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            transition: background 0.3s;
            font-weight: 600;
        }

        .nav-links a:hover {
            background: #1e40af;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Patient Records Management</h1>
            <div class="nav-links">
                <a href="dashboard.php">Dashboard</a>
                <a href="medicine.php">Medicines</a>
                <a href="../../routes/auth_api.php?action=logout" style="background: #dc2626;">Logout</a>
            </div>
        </div>

        <div id="message" class="message"></div>

        <div class="form-container">
            <div class="form-title" id="formTitle">Add New Patient Record</div>
            <form id="patientForm">
                <input type="hidden" id="editId" name="id">
                <input type="hidden" id="patient_id" name="patient_id">
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="name">Patient Name <span class="required">*</span></label>
                        <input type="text" id="name" name="name" required>
                    </div>

                    <div class="form-group">
                        <label for="patient_type">Patient Type <span class="required">*</span></label>
                        <select id="patient_type" name="patient_type" required>
                            <option value="">Select Type</option>
                            <option value="Student">Student</option>
                            <option value="Faculty">Faculty</option>
                            <option value="Staff">Staff</option>
                            <option value="Visitor">Visitor</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="contact_no">Contact Number <span class="required">*</span></label>
                        <input type="number" id="contact_no" name="contact_no" placeholder="0961-549-6134" required>
                    </div>

                    <div class="form-group">
                        <label for="medicine">Medicine <span class="required">*</span></label>
                        <select id="medicine" name="medicine" required>
                            <option value="">Select Medicine</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="quantity">Quantity <span class="required">*</span></label>
                        <input type="number" id="quantity" name="quantity" min="1" required>
                    </div>

                    <div class="form-group">
                        <label for="date">Date <span class="required">*</span></label>
                        <input type="date" id="date" name="date" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="reason">Reason / Symptoms <span class="required">*</span></label>
                    <textarea id="reason" name="reason" required></textarea>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn-secondary" id="cancelBtn" style="display: none;">Cancel</button>
                    <button type="submit" class="btn-primary" id="submitBtn">Add Record</button>
                </div>
            </form>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Patient Type</th>
                        <th>Contact No.</th>
                        <th>Medicine</th>
                        <th>Quantity</th>
                        <th>Reason</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="patientTableBody">
                    <tr>
                        <td colspan="9" class="loading">Loading records...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- External JavaScript - Admin version with full CRUD -->
    <script src="../js/admin-records.js"></script>
</body>
</html>