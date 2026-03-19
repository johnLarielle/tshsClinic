# 📁 Folder Structure Explained

Understanding what goes where in your Record Management System.

---

## 🗂️ Project Structure Overview

```
recordManagement/
├── app/
│   ├── Config/          # Configuration files
│   ├── Controllers/     # Business logic & request handling
│   └── Models/          # Database operations
├── database/            # SQL schema files
├── public/              # Frontend files (user sees this)
└── routes/              # API endpoints (connects frontend to backend)
```

---

## 📂 1. `app/` Folder - Backend Application Code

This is where all your **PHP backend logic** lives. Think of it as the "brain" of your application.

### 📂 `app/Config/` - Configuration Files

**Purpose:** Settings and initialization that the entire app needs.

**What goes here:**
- Database connection setup
- Environment settings
- Autoloader (automatically loads classes)
- Global constants
- Timezone, error reporting settings

**Examples:**
```
app/Config/
├── Config.php          # Main config + autoloader
└── Database.php        # Database connection class
```

**When to add files here:**
- You need to configure something globally (email settings, API keys, etc.)
- You're adding a utility class used by many parts of the app
- You need to set up third-party integrations

**Example usage:**
```php
// Config.php - Used by everyone
date_default_timezone_set('Asia/Manila');
define('BASE_PATH', dirname(__DIR__));

// Database.php - Used by all models
$db = new Database();
$connection = $db->connect();
```

---

### 📂 `app/Models/` - Database Operations (Data Layer)

**Purpose:** Talk to the database. Each model represents **ONE table**.

**What goes here:**
- One class file per database table
- CRUD operations (Create, Read, Update, Delete)
- Database queries
- Data validation at database level

**The Rule:** One Model = One Table

```
app/Models/
├── Patient.php              # Handles 'patient' table
├── PatientRecord.php        # Handles 'patient_record' table
├── Medicine.php             # Handles 'medicine' table
├── MedicineStock.php        # Handles 'medicine_stock' table
├── Admin.php                # Handles 'admin' table
├── StudentDetails.php       # Handles 'student_details' table
└── FacultyDetails.php       # Handles 'faculty_details' table
```

**What a Model does:**
```php
class Medicine {
    private $conn;      // Database connection
    private $table = 'medicine';  // Table name
    
    // Properties = Table columns
    public $medicine_id;
    public $medicine_name;
    public $current_stock;
    
    // Methods = Database operations
    public function create() { /* INSERT query */ }
    public function readAll() { /* SELECT all query */ }
    public function readOne() { /* SELECT one query */ }
    public function update() { /* UPDATE query */ }
    public function delete() { /* DELETE/soft delete query */ }
}
```

**When to add files here:**
- You create a new database table
- You need custom queries (like "get all medicines with low stock")
- You need to JOIN tables together

**Model DON'T handle:**
- ❌ User input validation
- ❌ HTTP responses (JSON formatting)
- ❌ Business logic decisions
- ❌ HTML/Frontend

**Model ONLY handles:**
- ✅ SQL queries
- ✅ Database connections
- ✅ Data sanitization for database
- ✅ Returning raw data

---

### 📂 `app/Controllers/` - Business Logic (Control Layer)

**Purpose:** Handle requests, make decisions, coordinate between models and API.

**What goes here:**
- One controller per model (usually)
- Request validation
- Business logic (decisions)
- Coordinating multiple models
- Formatting responses

**The Rule:** One Controller = One Model (usually)

```
app/Controllers/
├── PatientController.php
├── PatientRecordController.php
├── MedicineController.php
├── MedicineStockController.php
├── AdminController.php
├── StudentDetailsController.php
└── FacultyDetailsController.php
```

**What a Controller does:**
```php
class MedicineController {
    private $medicine;  // Uses the Medicine model
    
    // index() - Handle "get all medicines" request
    public function index() {
        // 1. Call model to get data
        $medicines = $this->medicine->readAll();
        
        // 2. Format response as JSON
        return json_encode([
            'success' => true,
            'data' => $medicines
        ]);
    }
    
    // store() - Handle "create medicine" request
    public function store($data) {
        // 1. Validate input
        if (empty($data['medicine_name'])) {
            return error('Name required');
        }
        
        // 2. Business logic
        if ($data['current_stock'] < 0) {
            return error('Stock cannot be negative');
        }
        
        // 3. Call model to save
        $this->medicine->medicine_name = $data['medicine_name'];
        $this->medicine->create();
        
        // 4. Return response
        return json_encode(['success' => true]);
    }
}
```

**When to add files here:**
- You created a new model
- You need to coordinate multiple models (e.g., when creating a patient record, also update medicine stock)
- You need complex validation logic
- You need to format data before sending to frontend

**Controller DON'T handle:**
- ❌ Direct SQL queries (use models)
- ❌ Database connections (use models)
- ❌ HTML rendering
- ❌ Routing (use routes folder)

**Controller ONLY handles:**
- ✅ Validating user input
- ✅ Calling model methods
- ✅ Business rules ("if stock < 5, send alert")
- ✅ Formatting JSON responses
- ✅ Error handling

---

## 📂 2. `database/` Folder - Database Schema

**Purpose:** SQL files to create/manage your database structure.

**What goes here:**
- Database creation scripts
- Table schemas
- Sample/seed data
- Migration files

```
database/
├── init.sql                    # Simple schema (old)
└── record_management.sql       # Full schema (your current one)
```

**When to add files here:**
- You export database from phpMyAdmin
- You create migration scripts
- You want to version control your database structure
- You need seed data for testing

**Example:**
```sql
-- record_management.sql
CREATE TABLE medicine (
    medicine_id INT AUTO_INCREMENT PRIMARY KEY,
    medicine_name VARCHAR(255) NOT NULL,
    current_stock INT NOT NULL
);

INSERT INTO medicine (medicine_name, current_stock) VALUES
('Biogesic', 100),
('Paracetamol', 50);
```

---

## 📂 3. `public/` Folder - Frontend (User Interface)

**Purpose:** What users see and interact with. The "face" of your application.

**What goes here:**
- HTML pages
- CSS stylesheets
- JavaScript files
- Images, fonts, icons
- Entry point (index.php)

```
public/
├── index.php           # Main page
├── css/
│   └── style.css      # Stylesheets
├── js/
│   └── app.js         # JavaScript
└── assets/
    ├── images/
    └── fonts/
```

**What frontend does:**
```javascript
// JavaScript in public/index.php
// 1. Get data from API
fetch('/routes/medicine_api.php?action=list')
    .then(response => response.json())
    .then(data => {
        // 2. Display in HTML
        displayMedicines(data.medicines);
    });

// 3. Send data to API
function createMedicine() {
    const formData = {
        medicine_name: document.getElementById('name').value,
        current_stock: document.getElementById('stock').value
    };
    
    fetch('/routes/medicine_api.php?action=create', {
        method: 'POST',
        body: JSON.stringify(formData)
    });
}
```

**When to add files here:**
- Creating new pages
- Adding CSS for styling
- Adding JavaScript for interactivity
- Adding images/assets

**Frontend DON'T handle:**
- ❌ Database queries
- ❌ Server-side validation
- ❌ Business logic
- ❌ Sensitive data storage

**Frontend ONLY handles:**
- ✅ Displaying data
- ✅ User input forms
- ✅ Sending requests to API
- ✅ Visual styling
- ✅ Client-side interactions

---

## 📂 4. `routes/` Folder - API Endpoints (Router Layer)

**Purpose:** Connect frontend to backend. The "traffic controller" of your app.

**What goes here:**
- API endpoint files
- Route definitions
- Request routing to controllers
- HTTP method handling (GET, POST, PUT, DELETE)

```
routes/
├── api.php                     # Old simple API (deprecated)
├── patient_record_api.php      # Patient records API
├── medicine_api.php            # Medicine API
├── admin_api.php               # Admin API
└── ...                         # One file per resource
```

**What a route file does:**
```php
// medicine_api.php

// 1. Initialize controller
$controller = new MedicineController($db);

// 2. Get action from URL
$action = $_GET['action'];  // ?action=list
$method = $_SERVER['REQUEST_METHOD'];  // GET, POST, etc.

// 3. Route to correct controller method
switch ($action) {
    case 'list':
        if ($method === 'GET') {
            echo $controller->index();  // Get all
        }
        break;
        
    case 'create':
        if ($method === 'POST') {
            $data = json_decode(file_get_contents("php://input"));
            echo $controller->store($data);  // Create new
        }
        break;
        
    case 'update':
        if ($method === 'POST') {
            $id = $_GET['id'];
            $data = json_decode(file_get_contents("php://input"));
            echo $controller->update($id, $data);  // Update
        }
        break;
}
```

**When to add files here:**
- You created a new controller
- You need a new API endpoint
- You want to separate API logic by resource

**Routes DON'T handle:**
- ❌ Business logic (use controllers)
- ❌ Database queries (use models)
- ❌ Data validation (use controllers)

**Routes ONLY handle:**
- ✅ Receiving HTTP requests
- ✅ Parsing request data (GET, POST, JSON)
- ✅ Calling the right controller method
- ✅ Setting HTTP headers (CORS, Content-Type)
- ✅ Returning controller's response

---

## 🔄 How They All Work Together (Request Flow)

Let's trace a **"Create Medicine"** request:

### Step-by-Step Flow:

```
1. USER ACTION (Frontend)
   📍 public/index.php
   User fills form and clicks "Add Medicine"
   ↓
   JavaScript sends POST request:
   fetch('/routes/medicine_api.php?action=create', {
       method: 'POST',
       body: JSON.stringify({medicine_name: 'Aspirin', stock: 100})
   })

2. ROUTING (API Endpoint)
   📍 routes/medicine_api.php
   - Receives POST request
   - Parses JSON data
   - Sees action=create
   - Calls controller: $controller->store($data)
   ↓

3. CONTROLLER (Business Logic)
   📍 app/Controllers/MedicineController.php
   - Validates: is medicine_name empty?
   - Checks business rules: is stock >= 0?
   - Sets model properties
   - Calls model: $this->medicine->create()
   ↓

4. MODEL (Database)
   📍 app/Models/Medicine.php
   - Prepares SQL: INSERT INTO medicine...
   - Sanitizes data
   - Executes query
   - Returns success/failure
   ↑

5. CONTROLLER (Response)
   📍 app/Controllers/MedicineController.php
   - Gets result from model
   - Formats JSON: {success: true, message: "Created"}
   - Returns JSON to route
   ↑

6. ROUTING (Send Response)
   📍 routes/medicine_api.php
   - Echoes controller's JSON
   - Sends to frontend
   ↑

7. FRONTEND (Display)
   📍 public/index.php
   - Receives JSON response
   - Shows success message
   - Refreshes medicine list
   - User sees new medicine in table
```

---

## 📊 Visual Diagram

```
┌─────────────────────────────────────────────────────┐
│  USER (Browser)                                     │
│  - Sees HTML                                        │
│  - Clicks buttons                                   │
│  - Fills forms                                      │
└──────────────────┬──────────────────────────────────┘
                   │
                   ↓
┌─────────────────────────────────────────────────────┐
│  PUBLIC/ (Frontend - View Layer)                    │
│  - index.php (HTML + JavaScript)                    │
│  - Displays data                                    │
│  - Sends AJAX requests                              │
└──────────────────┬──────────────────────────────────┘
                   │
                   ↓
┌─────────────────────────────────────────────────────┐
│  ROUTES/ (API Layer - Router)                       │
│  - medicine_api.php                                 │
│  - Receives HTTP requests                           │
│  - Routes to controllers                            │
└──────────────────┬──────────────────────────────────┘
                   │
                   ↓
┌─────────────────────────────────────────────────────┐
│  APP/CONTROLLERS/ (Controller Layer - Logic)        │
│  - MedicineController.php                           │
│  - Validates input                                  │
│  - Business rules                                   │
│  - Calls models                                     │
└──────────────────┬──────────────────────────────────┘
                   │
                   ↓
┌─────────────────────────────────────────────────────┐
│  APP/MODELS/ (Model Layer - Data)                   │
│  - Medicine.php                                     │
│  - SQL queries                                      │
│  - Database operations                              │
└──────────────────┬──────────────────────────────────┘
                   │
                   ↓
┌─────────────────────────────────────────────────────┐
│  APP/CONFIG/ (Configuration)                        │
│  - Database.php (connection)                        │
│  - Config.php (settings)                            │
└──────────────────┬──────────────────────────────────┘
                   │
                   ↓
┌─────────────────────────────────────────────────────┐
│  DATABASE/ (MySQL)                                  │
│  - record_management database                       │
│  - Tables: patient, medicine, etc.                  │
└─────────────────────────────────────────────────────┘
```

---

## 🎯 Quick Decision Guide

### "Where should I put this code?"

**I need to show something to the user:**
→ `public/index.php` (or create new page in public/)

**I need to style something:**
→ Add CSS in `public/index.php` or create `public/css/style.css`

**I need to make it interactive (buttons, forms):**
→ Add JavaScript in `public/index.php` or create `public/js/app.js`

**I need to create a new API endpoint:**
→ Create `routes/[resource]_api.php`

**I need to add business logic or validation:**
→ Create `app/Controllers/[Resource]Controller.php`

**I need to query the database:**
→ Create `app/Models/[TableName].php`

**I need to configure something globally:**
→ Add to `app/Config/Config.php`

**I need to set up database structure:**
→ Create SQL file in `database/`

---

## 📝 Example: Adding a "Medicine" Feature

Let's say you want to add full medicine management:

### 1. Database First
```
database/record_management.sql
✅ Already has medicine table
```

### 2. Create Model
```
app/Models/Medicine.php
- Create medicine class
- Add create(), readAll(), update(), delete() methods
```

### 3. Create Controller
```
app/Controllers/MedicineController.php
- Create controller class
- Add index(), store(), update(), destroy() methods
- Add validation logic
```

### 4. Create API Route
```
routes/medicine_api.php
- Set up routing
- Handle GET/POST/PUT/DELETE
- Call controller methods
```

### 5. Update Frontend
```
public/medicines.php (new page)
- Create HTML table
- Create form
- Add JavaScript for AJAX
- Call medicine_api.php endpoints
```

---

## 🎓 Key Principles

### Separation of Concerns
Each folder has **ONE job**:
- Models = Database
- Controllers = Logic
- Routes = Traffic
- Public = Display

### Don't Mix Responsibilities
❌ **WRONG:**
```php
// In public/index.php - BAD!
$sql = "SELECT * FROM medicine";  // Database query in frontend!
$result = mysqli_query($conn, $sql);
```

✅ **CORRECT:**
```php
// In public/index.php - GOOD!
fetch('/routes/medicine_api.php?action=list')
    .then(data => displayData(data));
```

### File Naming Conventions
- Models: `PascalCase.php` → `Medicine.php`, `PatientRecord.php`
- Controllers: `PascalCaseController.php` → `MedicineController.php`
- Routes: `snake_case_api.php` → `medicine_api.php`
- Frontend: `lowercase.php` → `index.php`, `medicines.php`

---

## 🚀 Summary Cheat Sheet

| Folder | Purpose | File Type | Example |
|--------|---------|-----------|---------|
| `app/Config/` | Settings & Setup | Configuration | `Database.php` |
| `app/Models/` | Database Operations | One per table | `Medicine.php` |
| `app/Controllers/` | Business Logic | One per model | `MedicineController.php` |
| `routes/` | API Endpoints | One per resource | `medicine_api.php` |
| `public/` | User Interface | HTML/CSS/JS | `index.php` |
| `database/` | SQL Schema | SQL files | `record_management.sql` |

---

Now you know exactly what goes where! 🎉