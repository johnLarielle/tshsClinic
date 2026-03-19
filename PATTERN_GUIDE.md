# 📚 Model-Controller Pattern Guide

This guide shows you how to create models and controllers for the remaining tables using the **PatientRecord** example as a template.

---

## 📁 Project Structure

```
app/
├── Config/
│   ├── Config.php           # Autoloader (already configured)
│   └── Database.php         # Database connection
├── Controllers/
│   └── PatientRecordController.php  ✅ Example controller
└── Models/
    ├── Patient.php          # Your old model
    └── PatientRecord.php    ✅ Example model

routes/
└── patient_record_api.php   ✅ Example API route file
```

---

## 🎯 Tables You Need to Create

Based on your `record_management.sql`, create these:

1. ✅ **PatientRecord** - DONE (example)
2. ⬜ **PatientModel** - Patient base table
3. ⬜ **Medicine** - Medicine catalog
4. ⬜ **MedicineStock** - Inventory tracking
5. ⬜ **Admin** - User authentication
6. ⬜ **StudentDetails** - Student-specific info
7. ⬜ **FacultyDetails** - Faculty-specific info

---

## 📝 Step-by-Step Pattern

### STEP 1: Create Model (Example: Medicine)

**File:** `app/Models/Medicine.php`

```php
<?php

class Medicine {
    private $conn;
    private $table = 'medicine';  // ← Change table name

    // ← Add your table columns as properties
    public $medicine_id;
    public $medicine_name;
    public $description;
    public $current_stock;
    public $dateCreated;
    public $dateDeleted;

    public function __construct($db) {
        $this->conn = $db;
    }

    // CREATE
    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                  (medicine_name, description, current_stock) 
                  VALUES (:medicine_name, :description, :current_stock)";

        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->medicine_name = htmlspecialchars(strip_tags($this->medicine_name));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->current_stock = htmlspecialchars(strip_tags($this->current_stock));

        // Bind
        $stmt->bindParam(':medicine_name', $this->medicine_name);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':current_stock', $this->current_stock);

        return $stmt->execute();
    }

    // READ ALL
    public function readAll() {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE dateDeleted IS NULL OR dateDeleted = '0000-00-00 00:00:00'
                  ORDER BY medicine_name ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // READ ONE
    public function readOne() {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE medicine_id = :medicine_id 
                  AND (dateDeleted IS NULL OR dateDeleted = '0000-00-00 00:00:00')
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':medicine_id', $this->medicine_id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $this->medicine_name = $row['medicine_name'];
            $this->description = $row['description'];
            $this->current_stock = $row['current_stock'];
            return $row;
        }
        return false;
    }

    // UPDATE
    public function update() {
        $query = "UPDATE " . $this->table . " 
                  SET medicine_name = :medicine_name,
                      description = :description,
                      current_stock = :current_stock
                  WHERE medicine_id = :medicine_id";

        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->medicine_name = htmlspecialchars(strip_tags($this->medicine_name));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->current_stock = htmlspecialchars(strip_tags($this->current_stock));
        $this->medicine_id = htmlspecialchars(strip_tags($this->medicine_id));

        // Bind
        $stmt->bindParam(':medicine_name', $this->medicine_name);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':current_stock', $this->current_stock);
        $stmt->bindParam(':medicine_id', $this->medicine_id);

        return $stmt->execute();
    }

    // SOFT DELETE
    public function delete() {
        $query = "UPDATE " . $this->table . " 
                  SET dateDeleted = CURRENT_TIMESTAMP 
                  WHERE medicine_id = :medicine_id";
        
        $stmt = $this->conn->prepare($query);
        $this->medicine_id = htmlspecialchars(strip_tags($this->medicine_id));
        $stmt->bindParam(':medicine_id', $this->medicine_id);
        return $stmt->execute();
    }
}
```

---

### STEP 2: Create Controller

**File:** `app/Controllers/MedicineController.php`

```php
<?php

class MedicineController {
    private $db;
    private $medicine;

    public function __construct($database) {
        $this->db = $database;
        $this->medicine = new Medicine($this->db);  // ← Change model name
    }

    // GET all
    public function index() {
        try {
            $stmt = $this->medicine->readAll();
            $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return $this->jsonResponse([
                'success' => true,
                'data' => $records,
                'count' => count($records)
            ], 200);
        } catch (Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'error' => 'Failed to fetch medicines: ' . $e->getMessage()
            ], 500);
        }
    }

    // GET single
    public function show($id) {
        if (empty($id)) {
            return $this->jsonResponse([
                'success' => false,
                'error' => 'Medicine ID is required'
            ], 400);
        }

        $this->medicine->medicine_id = $id;  // ← Change ID field
        $record = $this->medicine->readOne();

        if ($record) {
            return $this->jsonResponse([
                'success' => true,
                'data' => $record
            ], 200);
        } else {
            return $this->jsonResponse([
                'success' => false,
                'error' => 'Medicine not found'
            ], 404);
        }
    }

    // POST create
    public function store($data) {
        // ← Change required fields for your table
        $required = ['medicine_name', 'description', 'current_stock'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return $this->jsonResponse([
                    'success' => false,
                    'error' => "Field '{$field}' is required"
                ], 400);
            }
        }

        // ← Set model properties
        $this->medicine->medicine_name = $data['medicine_name'];
        $this->medicine->description = $data['description'];
        $this->medicine->current_stock = $data['current_stock'];

        if ($this->medicine->create()) {
            return $this->jsonResponse([
                'success' => true,
                'message' => 'Medicine created successfully'
            ], 201);
        } else {
            return $this->jsonResponse([
                'success' => false,
                'error' => 'Failed to create medicine'
            ], 500);
        }
    }

    // PUT/POST update
    public function update($id, $data) {
        if (empty($id)) {
            return $this->jsonResponse([
                'success' => false,
                'error' => 'Medicine ID is required'
            ], 400);
        }

        $required = ['medicine_name', 'description', 'current_stock'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return $this->jsonResponse([
                    'success' => false,
                    'error' => "Field '{$field}' is required"
                ], 400);
            }
        }

        $this->medicine->medicine_id = $id;
        $this->medicine->medicine_name = $data['medicine_name'];
        $this->medicine->description = $data['description'];
        $this->medicine->current_stock = $data['current_stock'];

        if ($this->medicine->update()) {
            return $this->jsonResponse([
                'success' => true,
                'message' => 'Medicine updated successfully'
            ], 200);
        } else {
            return $this->jsonResponse([
                'success' => false,
                'error' => 'Failed to update medicine'
            ], 500);
        }
    }

    // DELETE
    public function destroy($id) {
        if (empty($id)) {
            return $this->jsonResponse([
                'success' => false,
                'error' => 'Medicine ID is required'
            ], 400);
        }

        $this->medicine->medicine_id = $id;

        if ($this->medicine->delete()) {
            return $this->jsonResponse([
                'success' => true,
                'message' => 'Medicine deleted successfully'
            ], 200);
        } else {
            return $this->jsonResponse([
                'success' => false,
                'error' => 'Failed to delete medicine'
            ], 500);
        }
    }

    // Helper
    private function jsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        return json_encode($data);
    }
}
```

---

### STEP 3: Create API Route File

**File:** `routes/medicine_api.php`

```php
<?php

require_once __DIR__ . '/../app/Config/Config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    $database = new Database();
    $db = $database->connect();
    $controller = new MedicineController($db);  // ← Change controller
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';
$id = isset($_GET['id']) ? $_GET['id'] : null;

$data = [];
if ($method === 'POST' || $method === 'PUT') {
    $json = file_get_contents("php://input");
    $data = json_decode($json, true);
    if (!$data) $data = $_POST;
}

switch ($action) {
    case 'index':
    case 'list':
        if ($method === 'GET') {
            echo $controller->index();
        }
        break;

    case 'show':
        if ($method === 'GET' && $id) {
            echo $controller->show($id);
        }
        break;

    case 'create':
        if ($method === 'POST') {
            echo $controller->store($data);
        }
        break;

    case 'update':
        if (($method === 'POST' || $method === 'PUT') && $id) {
            echo $controller->update($id, $data);
        }
        break;

    case 'delete':
        if ($method === 'POST' || $method === 'DELETE') {
            echo $controller->destroy($id);
        }
        break;

    default:
        http_response_code(404);
        echo json_encode(['error' => 'Invalid action']);
        break;
}
```

---

## 🎯 Quick Reference for Each Table

### 1. Patient Model
- **Table:** `patient`
- **Primary Key:** `patient_id`
- **Fields:** fullname, patient_type, contact_no, dateCreated, dateDeleted

### 2. Medicine Model ✅ (Example above)
- **Table:** `medicine`
- **Primary Key:** `medicine_id`
- **Fields:** medicine_name, description, current_stock

### 3. MedicineStock Model
- **Table:** `medicine_stock`
- **Primary Key:** `stock_log`
- **Foreign Keys:** admin_id, medicine_id
- **Fields:** stock_date, quantity_added, expiration_date
- **JOIN:** Join with `admin` and `medicine` tables in readAll()

### 4. Admin Model
- **Table:** `admin`
- **Primary Key:** `admin_id`
- **Fields:** username, password, firstname, lastname, middlename, name_extension, contact_no, email
- **Special:** Hash password with `password_hash()` before storing

### 5. StudentDetails Model
- **Table:** `student_details`
- **Primary Key:** `student_id`
- **Foreign Key:** patient_id
- **Fields:** grade_lvl, strand, section
- **JOIN:** Join with `patient` table

### 6. FacultyDetails Model
- **Table:** `faculty_details`
- **Primary Key:** `faculty_id`
- **Foreign Key:** patient_id
- **Fields:** department
- **JOIN:** Join with `patient` table

---

## 🔑 Key Patterns to Follow

### ✅ Soft Delete Pattern
```php
public function delete() {
    $query = "UPDATE " . $this->table . " 
              SET dateDeleted = CURRENT_TIMESTAMP 
              WHERE {primary_key} = :{primary_key}";
    // ...
}
```

### ✅ JOIN Pattern (for tables with foreign keys)
```php
public function readAll() {
    $query = "SELECT 
                t1.*,
                t2.column_name as alias_name
              FROM " . $this->table . " t1
              LEFT JOIN other_table t2 ON t1.foreign_key = t2.primary_key
              WHERE t1.dateDeleted IS NULL
              ORDER BY t1.dateCreated DESC";
    // ...
}
```

### ✅ Validation Pattern
```php
$required = ['field1', 'field2', 'field3'];
foreach ($required as $field) {
    if (empty($data[$field])) {
        return $this->jsonResponse([
            'success' => false,
            'error' => "Field '{$field}' is required"
        ], 400);
    }
}
```

---

## 🧪 Testing Your API

### Test with Browser:
```
http://localhost/Projects/recordManagement/routes/patient_record_api.php?action=list
```

### Test with JavaScript:
```javascript
// Get all records
fetch('routes/patient_record_api.php?action=list')
    .then(res => res.json())
    .then(data => console.log(data));

// Create record
fetch('routes/patient_record_api.php?action=create', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({
        patient_id: 1,
        medicine_id: 1,
        quantity: 2,
        reason: 'Headache',
        date_given: '2026-02-12'
    })
}).then(res => res.json()).then(data => console.log(data));
```

---

## 📋 Checklist for Each Table

For each remaining table, create:

- [ ] Model file: `app/Models/[TableName].php`
- [ ] Controller file: `app/Controllers/[TableName]Controller.php`
- [ ] API route file: `routes/[table_name]_api.php`
- [ ] Test the API endpoints

---

## 🎓 Pro Tips

1. **Copy-paste is your friend** - Use the PatientRecord files as templates
2. **Change names carefully** - Table name, class name, primary key, fields
3. **Add JOINs where needed** - For tables with foreign keys
4. **Test incrementally** - Create model → test → create controller → test → create routes → test
5. **Use soft deletes** - Your schema has `dateDeleted`, so use UPDATE instead of DELETE

---

## 🚀 Next Steps

1. Start with **Patient** model (simple, no foreign keys)
2. Then **Medicine** model (also simple)
3. Then **Admin** model (add password hashing)
4. Then **StudentDetails** and **FacultyDetails** (have foreign keys)
5. Finally **MedicineStock** (most complex, multiple foreign keys)

---

**You've got this! The pattern is clear - just replicate it for each table. 💪**