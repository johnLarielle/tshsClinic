<?php

class AdminController {
    private $db;
    private $admin;

    public function __construct($db) {
        $this->db = $db;
        $this->admin = new Admin($db);
    }

    /**
     * Admin login
     */
    public function login($data) {
        if (empty($data['username']) || empty($data['password'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Username and password are required'
            ]);
            return;
        }

        try {
            $result = $this->admin->login($data['username'], $data['password']);
            
            if ($result) {
                // Start session if not already started
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                
                // Store admin info in session
                $_SESSION['admin_id'] = $result['admin_id'];
                $_SESSION['username'] = $result['username'];
                $_SESSION['fullname'] = $result['firstname'] . ' ' . $result['lastname'];
                $_SESSION['logged_in'] = true;
                
                http_response_code(200);
                echo json_encode([
                    'success' => true,
                    'message' => 'Login successful',
                    'data' => [
                        'admin_id' => $result['admin_id'],
                        'username' => $result['username'],
                        'firstname' => $result['firstname'],
                        'lastname' => $result['lastname'],
                        'email' => $result['email']
                    ]
                ]);
            } else {
                http_response_code(401);
                echo json_encode([
                    'success' => false,
                    'error' => 'Invalid username or password'
                ]);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Admin logout
     */
    public function logout() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        session_unset();
        session_destroy();
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }

    /**
     * Check if admin is logged in
     */
    public function checkAuth() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'authenticated' => true,
                'admin' => [
                    'admin_id' => $_SESSION['admin_id'],
                    'username' => $_SESSION['username'],
                    'fullname' => $_SESSION['fullname']
                ]
            ]);
        } else {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'authenticated' => false
            ]);
        }
    }

    /**
     * Get all admins
     */
    public function index() {
        try {
            $stmt = $this->admin->readAll();
            $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Remove passwords from response
            foreach ($admins as &$admin) {
                unset($admin['password']);
            }
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'data' => $admins
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Failed to fetch admins: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get single admin
     */
    public function show($id) {
        if (empty($id)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Admin ID is required'
            ]);
            return;
        }

        try {
            $this->admin->admin_id = $id;
            $result = $this->admin->readOne();
            
            if ($result) {
                // Remove password from response
                unset($result['password']);
                
                http_response_code(200);
                echo json_encode([
                    'success' => true,
                    'data' => $result
                ]);
            } else {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'error' => 'Admin not found'
                ]);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Create new admin
     */
    public function store($data) {
        // Validate required fields
        $requiredFields = ['username', 'password', 'firstname', 'lastname', 'email'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => "Field '$field' is required"
                ]);
                return;
            }
        }

        try {
            // Check if username already exists
            if ($this->admin->usernameExists($data['username'])) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => 'Username already exists'
                ]);
                return;
            }
            
            $this->admin->username = $data['username'];
            $this->admin->password = $data['password']; // Will be hashed in model
            $this->admin->firstname = $data['firstname'];
            $this->admin->lastname = $data['lastname'];
            $this->admin->middlename = $data['middlename'] ?? '';
            $this->admin->name_extension = $data['name_extension'] ?? '';
            $this->admin->contact_no = $data['contact_no'] ?? '';
            $this->admin->email = $data['email'];
            
            if ($this->admin->create()) {
                http_response_code(201);
                echo json_encode([
                    'success' => true,
                    'message' => 'Admin created successfully'
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'error' => 'Failed to create admin'
                ]);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Update admin
     */
    public function update($data) {
        if (empty($data['admin_id'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Admin ID is required'
            ]);
            return;
        }

        try {
            $this->admin->admin_id = $data['admin_id'];
            $this->admin->username = $data['username'];
            $this->admin->password = $data['password']; // Will be hashed in model
            $this->admin->firstname = $data['firstname'];
            $this->admin->lastname = $data['lastname'];
            $this->admin->middlename = $data['middlename'] ?? '';
            $this->admin->name_extension = $data['name_extension'] ?? '';
            $this->admin->contact_no = $data['contact_no'] ?? '';
            $this->admin->email = $data['email'];
            
            if ($this->admin->update()) {
                http_response_code(200);
                echo json_encode([
                    'success' => true,
                    'message' => 'Admin updated successfully'
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'error' => 'Failed to update admin'
                ]);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Delete admin
     */
    public function destroy($id) {
        if (empty($id)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Admin ID is required'
            ]);
            return;
        }

        try {
            $this->admin->admin_id = $id;
            
            if ($this->admin->delete()) {
                http_response_code(200);
                echo json_encode([
                    'success' => true,
                    'message' => 'Admin deleted successfully'
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'error' => 'Failed to delete admin'
                ]);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
}

?>
