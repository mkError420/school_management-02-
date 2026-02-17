<?php
/**
 * Input Validation Class
 * School Management System
 */

class Validator {
    private $errors = [];
    private $data;
    private $rules;
    
    public function __construct($data = [], $rules = []) {
        $this->data = $data;
        $this->rules = $rules;
    }
    
    /**
     * Validate data against rules
     * @param array $data
     * @param array $rules
     * @return bool
     */
    public function validate($data = [], $rules = []) {
        if (!empty($data)) $this->data = $data;
        if (!empty($rules)) $this->rules = $rules;
        
        $this->errors = [];
        
        foreach ($this->rules as $field => $fieldRules) {
            $value = $this->data[$field] ?? null;
            $rules = explode('|', $fieldRules);
            
            foreach ($rules as $rule) {
                $this->validateField($field, $value, $rule);
            }
        }
        
        return empty($this->errors);
    }
    
    /**
     * Validate individual field
     * @param string $field
     * @param mixed $value
     * @param string $rule
     */
    private function validateField($field, $value, $rule) {
        $params = [];
        
        // Extract parameters from rule
        if (strpos($rule, ':') !== false) {
            list($rule, $paramString) = explode(':', $rule, 2);
            $params = explode(',', $paramString);
        }
        
        switch ($rule) {
            case 'required':
                if (empty($value) && $value !== '0') {
                    $this->addError($field, 'This field is required');
                }
                break;
                
            case 'email':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->addError($field, 'Please enter a valid email address');
                }
                break;
                
            case 'numeric':
                if (!empty($value) && !is_numeric($value)) {
                    $this->addError($field, 'This field must be a number');
                }
                break;
                
            case 'integer':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_INT)) {
                    $this->addError($field, 'This field must be an integer');
                }
                break;
                
            case 'alpha':
                if (!empty($value) && !preg_match('/^[a-zA-Z]+$/', $value)) {
                    $this->addError($field, 'This field must contain only letters');
                }
                break;
                
            case 'alpha_num':
                if (!empty($value) && !preg_match('/^[a-zA-Z0-9]+$/', $value)) {
                    $this->addError($field, 'This field must contain only letters and numbers');
                }
                break;
                
            case 'alpha_dash':
                if (!empty($value) && !preg_match('/^[a-zA-Z0-9_-]+$/', $value)) {
                    $this->addError($field, 'This field must contain only letters, numbers, dashes and underscores');
                }
                break;
                
            case 'min':
                $min = $params[0] ?? 0;
                if (strlen($value) < $min) {
                    $this->addError($field, "This field must be at least {$min} characters");
                }
                break;
                
            case 'max':
                $max = $params[0] ?? 255;
                if (strlen($value) > $max) {
                    $this->addError($field, "This field must not exceed {$max} characters");
                }
                break;
                
            case 'between':
                $min = $params[0] ?? 0;
                $max = $params[1] ?? 255;
                if (strlen($value) < $min || strlen($value) > $max) {
                    $this->addError($field, "This field must be between {$min} and {$max} characters");
                }
                break;
                
            case 'min_value':
                $min = $params[0] ?? 0;
                if (is_numeric($value) && $value < $min) {
                    $this->addError($field, "This field must be at least {$min}");
                }
                break;
                
            case 'max_value':
                $max = $params[0] ?? 100;
                if (is_numeric($value) && $value > $max) {
                    $this->addError($field, "This field must not exceed {$max}");
                }
                break;
                
            case 'between_value':
                $min = $params[0] ?? 0;
                $max = $params[1] ?? 100;
                if (is_numeric($value) && ($value < $min || $value > $max)) {
                    $this->addError($field, "This field must be between {$min} and {$max}");
                }
                break;
                
            case 'date':
                if (!empty($value) && !strtotime($value)) {
                    $this->addError($field, 'Please enter a valid date');
                }
                break;
                
            case 'date_format':
                $format = $params[0] ?? 'Y-m-d';
                if (!empty($value)) {
                    $date = DateTime::createFromFormat($format, $value);
                    if (!$date || $date->format($format) !== $value) {
                        $this->addError($field, "Please enter a valid date in {$format} format");
                    }
                }
                break;
                
            case 'phone':
                if (!empty($value) && !preg_match('/^[+]?[0-9\s\-\(\)]+$/', $value)) {
                    $this->addError($field, 'Please enter a valid phone number');
                }
                break;
                
            case 'url':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_URL)) {
                    $this->addError($field, 'Please enter a valid URL');
                }
                break;
                
            case 'regex':
                $pattern = $params[0] ?? '';
                if (!empty($value) && !preg_match($pattern, $value)) {
                    $this->addError($field, 'This field format is invalid');
                }
                break;
                
            case 'unique':
                $table = $params[0] ?? '';
                $column = $params[1] ?? $field;
                $excludeId = $params[2] ?? null;
                
                if (!empty($value) && !$this->isUnique($table, $column, $value, $excludeId)) {
                    $this->addError($field, 'This value is already taken');
                }
                break;
                
            case 'exists':
                $table = $params[0] ?? '';
                $column = $params[1] ?? $field;
                
                if (!empty($value) && !$this->exists($table, $column, $value)) {
                    $this->addError($field, 'This value does not exist');
                }
                break;
                
            case 'confirmed':
                $confirmationField = $field . '_confirmation';
                $confirmationValue = $this->data[$confirmationField] ?? '';
                
                if ($value !== $confirmationValue) {
                    $this->addError($field, 'Field confirmation does not match');
                }
                break;
                
            case 'same':
                $otherField = $params[0] ?? '';
                $otherValue = $this->data[$otherField] ?? '';
                
                if ($value !== $otherValue) {
                    $this->addError($field, "This field must match {$otherField}");
                }
                break;
                
            case 'different':
                $otherField = $params[0] ?? '';
                $otherValue = $this->data[$otherField] ?? '';
                
                if ($value === $otherValue) {
                    $this->addError($field, "This field must be different from {$otherField}");
                }
                break;
                
            case 'in':
                $allowedValues = $params;
                if (!empty($value) && !in_array($value, $allowedValues)) {
                    $this->addError($field, 'This value is not allowed');
                }
                break;
                
            case 'not_in':
                $disallowedValues = $params;
                if (!empty($value) && in_array($value, $disallowedValues)) {
                    $this->addError($field, 'This value is not allowed');
                }
                break;
                
            case 'file':
                if (!empty($value) && !is_uploaded_file($value['tmp_name'] ?? '')) {
                    $this->addError($field, 'Please upload a valid file');
                }
                break;
                
            case 'image':
                if (!empty($value) && !empty($value['tmp_name'])) {
                    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                    if (!in_array($value['type'], $allowedTypes)) {
                        $this->addError($field, 'Please upload a valid image file');
                    }
                }
                break;
                
            case 'mimes':
                $allowedMimes = $params;
                if (!empty($value) && !empty($value['tmp_name'])) {
                    if (!in_array($value['type'], $allowedMimes)) {
                        $this->addError($field, 'This file type is not allowed');
                    }
                }
                break;
                
            case 'max_file_size':
                $maxSize = $params[0] ?? 5242880; // 5MB default
                if (!empty($value) && !empty($value['size']) && $value['size'] > $maxSize) {
                    $maxSizeMB = $maxSize / 1048576;
                    $this->addError($field, "File size must not exceed {$maxSizeMB}MB");
                }
                break;
        }
    }
    
    /**
     * Check if value is unique in database
     * @param string $table
     * @param string $column
     * @param mixed $value
     * @param int|null $excludeId
     * @return bool
     */
    private function isUnique($table, $column, $value, $excludeId = null) {
        try {
            $db = new QueryBuilder($table);
            $db->where($column, $value);
            
            if ($excludeId) {
                $db->andWhere('id', '!=', $excludeId);
            }
            
            return $db->count() === 0;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Check if value exists in database
     * @param string $table
     * @param string $column
     * @param mixed $value
     * @return bool
     */
    private function exists($table, $column, $value) {
        try {
            $db = new QueryBuilder($table);
            $db->where($column, $value);
            
            return $db->count() > 0;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Add validation error
     * @param string $field
     * @param string $message
     */
    private function addError($field, $message) {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        $this->errors[$field][] = $message;
    }
    
    /**
     * Get all validation errors
     * @return array
     */
    public function getErrors() {
        return $this->errors;
    }
    
    /**
     * Get errors for specific field
     * @param string $field
     * @return array
     */
    public function getErrorsByField($field) {
        return $this->errors[$field] ?? [];
    }
    
    /**
     * Get first error for specific field
     * @param string $field
     * @return string|null
     */
    public function getFirstError($field) {
        $errors = $this->getErrorsByField($field);
        return $errors[0] ?? null;
    }
    
    /**
     * Check if there are any errors
     * @return bool
     */
    public function hasErrors() {
        return !empty($this->errors);
    }
    
    /**
     * Get all errors as string
     * @param string $separator
     * @return string
     */
    public function getErrorsAsString($separator = ', ') {
        $allErrors = [];
        foreach ($this->errors as $fieldErrors) {
            $allErrors = array_merge($allErrors, $fieldErrors);
        }
        return implode($separator, $allErrors);
    }
    
    /**
     * Sanitize input data
     * @param array $data
     * @return array
     */
    public static function sanitize($data) {
        $sanitized = [];
        
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $sanitized[$key] = self::sanitize($value);
            } else {
                $sanitized[$key] = htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
            }
        }
        
        return $sanitized;
    }
    
    /**
     * Clean input for database
     * @param mixed $value
     * @return mixed
     */
    public static function clean($value) {
        if (is_array($value)) {
            return array_map([self::class, 'clean'], $value);
        }
        
        return trim(strip_tags($value));
    }
}
