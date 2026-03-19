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
    <title>Medicine Management - Admin</title>

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

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        h1 {
            color: #333;
            font-size: 2.5em;
            display: flex;
            align-items: center;
            gap: 15px;
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
        }

        .nav-links a:hover {
            background: #1e40af;
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
        }

        .btn-primary {
            background: #1e3a8a;
            color: white;
        }

        .btn-primary:hover {
            background: #1e40af;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(30, 58, 138, 0.4);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
            display: none;
        }

        .btn-secondary:hover {
            background: #5a6268;
        }

        .table-container {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: #1e3a8a;
            color: white;
        }

        th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85em;
            letter-spacing: 0.5px;
        }

        td {
            padding: 15px;
            border-bottom: 1px solid #f0f0f0;
        }

        tr:hover {
            background: #f8f9fa;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
        }

        .btn-edit, .btn-delete, .btn-stock {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9em;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-edit {
            background: #f59e0b;
            color: #000;
        }

        .btn-edit:hover {
            background: #d97706;
            transform: scale(1.05);
        }

        .btn-delete {
            background: #dc2626;
            color: white;
        }

        .btn-delete:hover {
            background: #b91c1c;
            transform: scale(1.05);
        }

        .btn-stock {
            background: #1e3a8a;
            color: white;
        }

        .btn-stock:hover {
            background: #1e40af;
            transform: scale(1.05);
        }

        .message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: none;
            font-weight: 600;
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

        .empty-state {
            text-align: center;
            padding: 40px;
            color: #999;
            font-size: 1.1em;
        }

        .stock-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 12px;
            font-size: 0.85em;
            font-weight: 600;
        }

        .stock-low {
            background: #f8d7da;
            color: #721c24;
        }

        .stock-medium {
            background: #fff3cd;
            color: #856404;
        }

        .stock-good {
            background: #d4edda;
            color: #155724;
        }

        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }

        .modal-content {
            background-color: white;
            margin: 10% auto;
            padding: 30px;
            border-radius: 15px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .modal-title {
            font-size: 1.5em;
            color: #1e3a8a;
            font-weight: bold;
        }

        .close {
            font-size: 28px;
            font-weight: bold;
            color: #aaa;
            cursor: pointer;
        }

        .close:hover {
            color: #000;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Medicine Management</h1>
            <div class="nav-links">
                <a href="dashboard.php">Dashboard</a>
                <a href="records.php">Patient Records</a>
                <a href="../../routes/auth_api.php?action=logout" style="background: #dc2626;">Logout</a>
            </div>
        </div>

        <div id="message" class="message"></div>

        <!-- Add/Edit Medicine Form -->
        <div class="form-container">
            <h2 class="form-title" id="formTitle">Add New Medicine</h2>
            <form id="medicineForm">
                <input type="hidden" id="editId" name="medicine_id">
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="medicine_name">Medicine Name *</label>
                        <input type="text" id="medicine_name" name="medicine_name" placeholder="e.g., Biogesic" required>
                    </div>

                    <div class="form-group">
                        <label for="current_stock">Current Stock *</label>
                        <input type="number" id="current_stock" name="current_stock" min="0" value="0" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" placeholder="Medicine description, dosage, etc."></textarea>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn-secondary" id="cancelBtn">Cancel</button>
                    <button type="submit" class="btn-primary" id="submitBtn">Add Medicine</button>
                </div>
            </form>
        </div>

        <!-- Medicine Table -->
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Medicine Name</th>
                        <th>Description</th>
                        <th>Current Stock</th>
                        <th>Date Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="medicineTableBody">
                    <tr>
                        <td colspan="6" class="empty-state">
                            <div> No medicines found. Add your first medicine above!</div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Stock Update Modal -->
    <div id="stockModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Update Stock</h2>
                <span class="close">&times;</span>
            </div>
            <form id="stockForm">
                <input type="hidden" id="stock_medicine_id">
                <div class="form-group" style="margin-bottom: 15px;">
                    <label>Medicine: <strong id="stock_medicine_name"></strong></label>
                    <label>Current Stock: <strong id="stock_current"></strong></label>
                </div>
                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="stock_action">Action *</label>
                    <select id="stock_action" required>
                        <option value="add">Add Stock</option>
                        <option value="subtract">Remove Stock</option>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="stock_quantity">Quantity *</label>
                    <input type="number" id="stock_quantity" min="1" required>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn-secondary" onclick="closeStockModal()">Cancel</button>
                    <button type="submit" class="btn-primary">Update Stock</button>
                </div>
            </form>
        </div>
    </div>

    <!-- External JavaScript -->
    <script src="../js/admin-medicine.js"></script>
</body>
</html>
