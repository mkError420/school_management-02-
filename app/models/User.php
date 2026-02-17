<?php
/**
 * User Model
 * School Management System
 */

class User {
    private $db;
    
    public function __construct() {
        $this->db = new QueryBuilder();
    }
    
    /**
     * Find user by username or email
     * @param string $username
     * @return object|null
     */
    public function findByUsernameOrEmail($username) {
        return $this->db->table('users')
            ->where('username', $username)
            ->orWhere('email', $username)
            ->first();
    }
    
    /**
     * Find user by ID
     * @param int $id
     * @return object|null
     */
    public function findById($id) {
        return $this->db->table('users')
            ->where('id', $id)
            ->first();
    }
    
    /**
     * Create new user
     * @param array $data
     * @return bool
     */
    public function create($data) {
        // Hash password
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        $data['created_at'] = date('Y-m-d H:i:s');
        
        return $this->db->table('users')->insert($data);
    }
    
    /**
     * Update user
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update($id, $data) {
        // Don't update password if it's not changed
        if (isset($data['password']) && empty($data['password'])) {
            unset($data['password']);
        } elseif (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        return $this->db->table('users')
            ->where('id', $id)
            ->update($data)
            ->execute();
    }
    
    /**
     * Delete user
     * @param int $id
     * @return bool
     */
    public function delete($id) {
        return $this->db->table('users')
            ->where('id', $id)
            ->delete()
            ->execute();
    }
    
    /**
     * Verify user password
     * @param string $password
     * @param string $hash
     * @return bool
     */
    public function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    /**
     * Update last login
     * @param int $id
     * @return bool
     */
    public function updateLastLogin($id) {
        return $this->db->table('users')
            ->where('id', $id)
            ->update(['last_login' => date('Y-m-d H:i:s')])
            ->execute();
    }
    
    /**
     * Get all users with pagination
     * @param int $page
     * @param int $limit
     * @param string $role
     * @return array
     */
    public function getAll($page = 1, $limit = ITEMS_PER_PAGE, $role = '') {
        $offset = ($page - 1) * $limit;
        
        $query = $this->db->table('users');
        
        if (!empty($role)) {
            $query->where('role', $role);
        }
        
        $users = $query->orderBy('created_at', 'DESC')
            ->limit($limit, $offset)
            ->get();
        
        // Get total count
        $countQuery = $this->db->table('users');
        if (!empty($role)) {
            $countQuery->where('role', $role);
        }
        $total = $countQuery->count();
        
        return [
            'users' => $users,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'total_pages' => ceil($total / $limit)
        ];
    }
    
    /**
     * Search users
     * @param string $search
     * @param string $role
     * @return array
     */
    public function search($search, $role = '') {
        $query = $this->db->table('users')
            ->where('username', 'LIKE', "%{$search}%")
            ->orWhere('email', 'LIKE', "%{$search}%");
        
        if (!empty($role)) {
            $query->andWhere('role', $role);
        }
        
        return $query->orderBy('username')->get();
    }
    
    /**
     * Change user status
     * @param int $id
     * @param string $status
     * @return bool
     */
    public function changeStatus($id, $status) {
        return $this->db->table('users')
            ->where('id', $id)
            ->update(['status' => $status, 'updated_at' => date('Y-m-d H:i:s')])
            ->execute();
    }
    
    /**
     * Check if username exists
     * @param string $username
     * @param int|null $excludeId
     * @return bool
     */
    public function usernameExists($username, $excludeId = null) {
        $query = $this->db->table('users')->where('username', $username);
        
        if ($excludeId) {
            $query->andWhere('id', '!=', $excludeId);
        }
        
        return $query->count() > 0;
    }
    
    /**
     * Check if email exists
     * @param string $email
     * @param int|null $excludeId
     * @return bool
     */
    public function emailExists($email, $excludeId = null) {
        $query = $this->db->table('users')->where('email', $email);
        
        if ($excludeId) {
            $query->andWhere('id', '!=', $excludeId);
        }
        
        return $query->count() > 0;
    }
    
    /**
     * Get user statistics
     * @return array
     */
    public function getStatistics() {
        $stats = [];
        
        // Total users
        $stats['total'] = $this->db->table('users')->count();
        
        // By role
        $stats['admin'] = $this->db->table('users')->where('role', ROLE_ADMIN)->count();
        $stats['teacher'] = $this->db->table('users')->where('role', ROLE_TEACHER)->count();
        $stats['student'] = $this->db->table('users')->where('role', ROLE_STUDENT)->count();
        
        // By status
        $stats['active'] = $this->db->table('users')->where('status', STATUS_ACTIVE)->count();
        $stats['inactive'] = $this->db->table('users')->where('status', STATUS_INACTIVE)->count();
        
        // Recent registrations (last 30 days)
        $stats['recent'] = $this->db->table('users')
            ->where('created_at', '>=', date('Y-m-d', strtotime('-30 days')))
            ->count();
        
        return $stats;
    }
    
    /**
     * Reset password
     * @param int $id
     * @param string $newPassword
     * @return bool
     */
    public function resetPassword($id, $newPassword) {
        return $this->db->table('users')
            ->where('id', $id)
            ->update([
                'password' => password_hash($newPassword, PASSWORD_DEFAULT),
                'updated_at' => date('Y-m-d H:i:s')
            ])
            ->execute();
    }
    
    /**
     * Update remember token
     * @param int $id
     * @param string $token
     * @return bool
     */
    public function updateRememberToken($id, $token) {
        return $this->db->table('users')
            ->where('id', $id)
            ->update(['remember_token' => $token])
            ->execute();
    }
    
    /**
     * Find user by remember token
     * @param string $token
     * @return object|null
     */
    public function findByRememberToken($token) {
        return $this->db->table('users')
            ->where('remember_token', $token)
            ->first();
    }
    
    /**
     * Clear remember token
     * @param int $id
     * @return bool
     */
    public function clearRememberToken($id) {
        return $this->db->table('users')
            ->where('id', $id)
            ->update(['remember_token' => null])
            ->execute();
    }
}
