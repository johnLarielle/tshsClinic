<?php

class Admin {
    private $conn;
    private $table = 'admin';

    public $admin_id;
    public $username;
    public $password;
    public $firstname;
    public $lastname;
    public $middlename;
    public $name_extension;
    public $contact_no;
    public $email;
    public $dateCreated;
    public $dateDeleted;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create admin record
    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                  (username, password, firstname, lastname, middlename, name_extension, contact_no, email) 
                  VALUES (:username, :password, :firstname, :lastname, :middlename, :name_extension, :contact_no, :email)";

        $stmt = $this->conn->prepare($query);

        // Sanitize inputs
        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->password = password_hash($this->password, PASSWORD_DEFAULT); // Hash password
        $this->firstname = htmlspecialchars(strip_tags($this->firstname));
        $this->lastname = htmlspecialchars(strip_tags($this->lastname));
        $this->middlename = htmlspecialchars(strip_tags($this->middlename));
        $this->name_extension = htmlspecialchars(strip_tags($this->name_extension));
        $this->contact_no = htmlspecialchars(strip_tags($this->contact_no));
        $this->email = htmlspecialchars(strip_tags($this->email));

        // Bind parameters
        $stmt->bindParam(':username', $this->username);
        $stmt->bindParam(':password', $this->password);
        $stmt->bindParam(':firstname', $this->firstname);
        $stmt->bindParam(':lastname', $this->lastname);
        $stmt->bindParam(':middlename', $this->middlename);
        $stmt->bindParam(':name_extension', $this->name_extension);
        $stmt->bindParam(':contact_no', $this->contact_no);
        $stmt->bindParam(':email', $this->email);

        if ($stmt->execute()) {
            return true;
        } 
        return false;
    }

    // read all Admin
    public function readAll() {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE dateDeleted IS NULL OR dateDeleted = '0000-00-00 00:00:00'
                  ORDER BY dateCreated DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;     
    }

    // read one Admin
    public function readOne() {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE admin_id = :admin_id 
                  AND (dateDeleted IS NULL OR dateDeleted = '0000-00-00 00:00:00')
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':admin_id', $this->admin_id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            $this->username = $row['username'];
            $this->firstname = $row['firstname'];
            $this->lastname = $row['lastname'];
            $this->middlename = $row['middlename'];
            $this->name_extension = $row['name_extension'];
            $this->contact_no = $row['contact_no'];
            $this->email = $row['email'];
            $this->dateCreated = $row['dateCreated'];
            return $row;
        }
        return false;
    }

    //update Admin
    public function update() {
        $query = "UPDATE " . $this->table . "
                  SET username = :username,
                      password = :password,
                      firstname = :firstname,
                      lastname = :lastname,
                      middlename = :middlename,
                      name_extension = :name_extension,
                      contact_no = :contact_no,
                      email = :email
                  WHERE admin_id = :admin_id";

        $stmt = $this->conn->prepare($query);

        // Sanitize inputs
        $this->admin_id = htmlspecialchars(strip_tags($this->admin_id));
        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->password = password_hash($this->password, PASSWORD_DEFAULT); // Hash password
        $this->firstname = htmlspecialchars(strip_tags($this->firstname));
        $this->lastname = htmlspecialchars(strip_tags($this->lastname));
        $this->middlename = htmlspecialchars(strip_tags($this->middlename));
        $this->name_extension = htmlspecialchars(strip_tags($this->name_extension));
        $this->contact_no = htmlspecialchars(strip_tags($this->contact_no));
        $this->email = htmlspecialchars(strip_tags($this->email));

        $stmt->bindParam(':admin_id', $this->admin_id);
        $stmt->bindParam(':username', $this->username);
        $stmt->bindParam(':password', $this->password);
        $stmt->bindParam(':firstname', $this->firstname);
        $stmt->bindParam(':lastname', $this->lastname);
        $stmt->bindParam(':middlename', $this->middlename);
        $stmt->bindParam(':name_extension', $this->name_extension);
        $stmt->bindParam(':contact_no', $this->contact_no);
        $stmt->bindParam(':email', $this->email);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Soft delete Admin
    public function delete() {
        $query = "UPDATE " . $this->table . " SET dateDeleted = CURRENT_TIMESTAMP where admin_id = :admin_id";

        $stmt = $this->conn->prepare($query);
        $this->admin_id = htmlspecialchars(strip_tags($this->admin_id));
        $stmt->bindParam(':admin_id', $this->admin_id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Hard delete Admin
    public function hardDelete() {
        $query = "DELETE FROM " . $this->table . " WHERE admin_id = :admin_id";
        $stmt = $this->conn->prepare($query);

        $this->admin_id = htmlspecialchars(strip_tags($this->admin_id));
        $stmt->bindParam(':admin_id', $this->admin_id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Get Admin by id
    public function getByID($admin_id) {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE admin_id = :admin_id 
                  AND (dateDeleted IS NULL OR dateDeleted = '0000-00-00 00:00:00')";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':admin_id', $admin_id);
        $stmt->execute();
        return $stmt;
    }

    // Login Admin (verify credentials)
    public function login($username, $password) {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE username = :username 
                  AND (dateDeleted IS NULL OR dateDeleted = '0000-00-00 00:00:00')
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row && password_verify($password, $row['password'])) {
            // Password is correct
            $this->admin_id = $row['admin_id'];
            $this->username = $row['username'];
            $this->firstname = $row['firstname'];
            $this->lastname = $row['lastname'];
            $this->middlename = $row['middlename'];
            $this->name_extension = $row['name_extension'];
            $this->contact_no = $row['contact_no'];
            $this->email = $row['email'];
            return $row;
        }
        return false;
    }

    // Check if username exists
    public function usernameExists($username) {
        $query = "SELECT admin_id FROM " . $this->table . " 
                  WHERE username = :username 
                  AND (dateDeleted IS NULL OR dateDeleted = '0000-00-00 00:00:00')";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }
}

?>