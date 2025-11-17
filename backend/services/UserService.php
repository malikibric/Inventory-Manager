<?php
require_once __DIR__ . '/../dao/UserDao.php';

class UserService {
    private $dao;

    public function __construct() {
        $this->dao = new UserDao();
    }

    public function getAll() {
        try {
            $users = $this->dao->getAll();
            // Map database 'name' to 'username' for API response
            foreach ($users as &$user) {
                unset($user['password_hash']);
                if (isset($user['name'])) {
                    $user['username'] = $user['name'];
                    unset($user['name']);
                }
            }
            return $users;
        } catch (Exception $e) {
            throw new Exception("Error fetching users: " . $e->getMessage());
        }
    }

    public function getById($id) {
        if (empty($id)) {
            throw new Exception("User ID is required");
        }
        try {
            $user = $this->dao->getById($id);
            if (!$user) {
                throw new Exception("User not found");
            }
            unset($user['password_hash']);
            // Map database 'name' to 'username' for API response
            if (isset($user['name'])) {
                $user['username'] = $user['name'];
                unset($user['name']);
            }
            return $user;
        } catch (Exception $e) {
            throw new Exception("Error fetching user: " . $e->getMessage());
        }
    }

    public function create($data) {
        if (!isset($data['username']) || trim($data['username']) === '') {
            throw new Exception("Username is required");
        }
        if (!isset($data['email']) || trim($data['email']) === '') {
            throw new Exception("Email is required");
        }
        if (!isset($data['password']) || trim($data['password']) === '') {
            throw new Exception("Password is required");
        }
        if (!isset($data['role']) || trim($data['role']) === '') {
            throw new Exception("Role is required");
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format");
        }

        $validRoles = ['admin', 'user', 'manager'];
        if (!in_array(strtolower($data['role']), $validRoles)) {
            throw new Exception("Invalid role. Must be one of: " . implode(', ', $validRoles));
        }

        $existingEmail = $this->dao->getByEmail($data['email']);
        if ($existingEmail) {
            throw new Exception("User with this email already exists");
        }

        // Map Swagger field 'username' to database column 'name'
        // Map Swagger field 'password' to database column 'password_hash' (hash it)
        $data['name'] = $data['username'];
        unset($data['username']);
        $data['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
        unset($data['password']);

        try {
            $lastId = $this->dao->insert($data);
            if ($lastId) {
                $user = $this->dao->getById($lastId);
                unset($user['password_hash']);
                // Map database 'name' back to 'username' for API response
                if (isset($user['name'])) {
                    $user['username'] = $user['name'];
                    unset($user['name']);
                }
                return $user;
            }
            throw new Exception("Failed to create user");
        } catch (PDOException $e) {
            throw new Exception("Database error: " . $e->getMessage());
        }
    }

    public function update($id, $data) {
        if (empty($id)) {
            throw new Exception("User ID is required");
        }

        $existing = $this->dao->getById($id);
        if (!$existing) {
            throw new Exception("User not found");
        }

        unset($data['user_id']);

        // Map Swagger field 'username' to database column 'name'
        if (isset($data['username'])) {
            $data['name'] = $data['username'];
            unset($data['username']);
        }

        // Map Swagger field 'password' to database column 'password_hash' (hash it)
        if (isset($data['password']) && trim($data['password']) !== '') {
            $data['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
            unset($data['password']);
        }

        if (isset($data['email']) && trim($data['email']) !== '') {
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                throw new Exception("Invalid email format");
            }
            if ($data['email'] !== $existing['email']) {
                $existingEmail = $this->dao->getByEmail($data['email']);
                if ($existingEmail) {
                    throw new Exception("User with this email already exists");
                }
            }
        }

        if (isset($data['role']) && trim($data['role']) !== '') {
            $validRoles = ['admin', 'user', 'manager'];
            if (!in_array(strtolower($data['role']), $validRoles)) {
                throw new Exception("Invalid role. Must be one of: " . implode(', ', $validRoles));
            }
        }

        try {
            $result = $this->dao->update($id, $data);
            if ($result) {
                $user = $this->dao->getById($id);
                unset($user['password_hash']);
                // Map database 'name' back to 'username' for API response
                if (isset($user['name'])) {
                    $user['username'] = $user['name'];
                    unset($user['name']);
                }
                return $user;
            }
            throw new Exception("Failed to update user");
        } catch (PDOException $e) {
            throw new Exception("Database error: " . $e->getMessage());
        }
    }

    public function delete($id) {
        if (empty($id)) {
            throw new Exception("User ID is required");
        }

        $existing = $this->dao->getById($id);
        if (!$existing) {
            throw new Exception("User not found");
        }

        try {
            return $this->dao->delete($id);
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'foreign key') !== false) {
                throw new Exception("Cannot delete user: it is being used in orders");
            }
            throw new Exception("Database error: " . $e->getMessage());
        }
    }
}
?>


