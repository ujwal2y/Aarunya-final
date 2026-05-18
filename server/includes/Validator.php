<?php
/**
 * Comprehensive Validation Class
 * Handles all input validation with security best practices
 */

class Validator {
    
    private $errors = [];
    
    /**
     * Validate phone number - EXACTLY 10 digits
     * 
     * @param string $phone Phone number to validate
     * @param string $fieldName Field name for error message
     * @return bool
     */
    public function validatePhone($phone, $fieldName = 'Phone') {
        // Remove any whitespace
        $phone = trim($phone);
        
        // Check if empty
        if (empty($phone)) {
            $this->errors[$fieldName] = "{$fieldName} is required";
            return false;
        }
        
        // Check if contains only digits
        if (!ctype_digit($phone)) {
            $this->errors[$fieldName] = "{$fieldName} must contain only numbers";
            return false;
        }
        
        // Check if exactly 10 digits
        if (strlen($phone) !== 10) {
            $this->errors[$fieldName] = "{$fieldName} must be exactly 10 digits";
            return false;
        }
        
        return true;
    }
    
    /**
     * Validate email based on user role
     * 
     * @param string $email Email to validate
     * @param string $role User role (user/patient, admin, doctor)
     * @param string $fieldName Field name for error message
     * @return bool
     */
    public function validateEmail($email, $role = 'user', $fieldName = 'Email') {
        // Remove whitespace and convert to lowercase
        $email = trim(strtolower($email));
        
        // Check if empty
        if (empty($email)) {
            $this->errors[$fieldName] = "{$fieldName} is required";
            return false;
        }
        
        // Basic email format validation
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$fieldName] = "Invalid {$fieldName} format";
            return false;
        }
        
        // Role-based domain validation
        if ($role === 'user' || $role === 'patient') {
            // Patients must use @gmail.com
            if (!preg_match('/@gmail\.com$/i', $email)) {
                $this->errors[$fieldName] = "Patients must use a Gmail address (@gmail.com)";
                return false;
            }
        } elseif ($role === 'admin' || $role === 'doctor') {
            // Admin and doctors can use any professional domain
            // Just ensure it has a valid domain
            $domain = substr(strrchr($email, "@"), 1);
            if (empty($domain) || !strpos($domain, '.')) {
                $this->errors[$fieldName] = "Invalid email domain";
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Validate password strength
     * 
     * @param string $password Password to validate
     * @param string $fieldName Field name for error message
     * @return bool
     */
    public function validatePassword($password, $fieldName = 'Password') {
        if (empty($password)) {
            $this->errors[$fieldName] = "{$fieldName} is required";
            return false;
        }
        
        if (strlen($password) < 8) {
            $this->errors[$fieldName] = "{$fieldName} must be at least 8 characters";
            return false;
        }
        
        if (!preg_match('/[A-Z]/', $password)) {
            $this->errors[$fieldName] = "{$fieldName} must contain at least one uppercase letter";
            return false;
        }
        
        if (!preg_match('/[a-z]/', $password)) {
            $this->errors[$fieldName] = "{$fieldName} must contain at least one lowercase letter";
            return false;
        }
        
        if (!preg_match('/[0-9]/', $password)) {
            $this->errors[$fieldName] = "{$fieldName} must contain at least one number";
            return false;
        }
        
        if (!preg_match('/[@$!%*?&#]/', $password)) {
            $this->errors[$fieldName] = "{$fieldName} must contain at least one special character (@$!%*?&#)";
            return false;
        }
        
        return true;
    }
    
    /**
     * Validate required field
     * 
     * @param mixed $value Value to validate
     * @param string $fieldName Field name for error message
     * @return bool
     */
    public function required($value, $fieldName) {
        if (empty(trim($value))) {
            $this->errors[$fieldName] = "{$fieldName} is required";
            return false;
        }
        return true;
    }
    
    /**
     * Validate string length
     * 
     * @param string $value Value to validate
     * @param int $min Minimum length
     * @param int $max Maximum length
     * @param string $fieldName Field name for error message
     * @return bool
     */
    public function length($value, $min, $max, $fieldName) {
        $length = strlen(trim($value));
        
        if ($length < $min) {
            $this->errors[$fieldName] = "{$fieldName} must be at least {$min} characters";
            return false;
        }
        
        if ($length > $max) {
            $this->errors[$fieldName] = "{$fieldName} must not exceed {$max} characters";
            return false;
        }
        
        return true;
    }
    
    /**
     * Validate numeric value
     * 
     * @param mixed $value Value to validate
     * @param string $fieldName Field name for error message
     * @return bool
     */
    public function numeric($value, $fieldName) {
        if (!is_numeric($value)) {
            $this->errors[$fieldName] = "{$fieldName} must be a number";
            return false;
        }
        return true;
    }
    
    /**
     * Validate date format
     * 
     * @param string $date Date to validate
     * @param string $format Expected format (default: Y-m-d)
     * @param string $fieldName Field name for error message
     * @return bool
     */
    public function date($date, $format = 'Y-m-d', $fieldName = 'Date') {
        $d = DateTime::createFromFormat($format, $date);
        if (!$d || $d->format($format) !== $date) {
            $this->errors[$fieldName] = "Invalid {$fieldName} format";
            return false;
        }
        return true;
    }
    
    /**
     * Sanitize input to prevent XSS
     * 
     * @param string $input Input to sanitize
     * @return string
     */
    public function sanitize($input) {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Sanitize array of inputs
     * 
     * @param array $inputs Inputs to sanitize
     * @return array
     */
    public function sanitizeArray($inputs) {
        $sanitized = [];
        foreach ($inputs as $key => $value) {
            if (is_array($value)) {
                $sanitized[$key] = $this->sanitizeArray($value);
            } else {
                $sanitized[$key] = $this->sanitize($value);
            }
        }
        return $sanitized;
    }
    
    /**
     * Check if validation has errors
     * 
     * @return bool
     */
    public function hasErrors() {
        return !empty($this->errors);
    }
    
    /**
     * Get all validation errors
     * 
     * @return array
     */
    public function getErrors() {
        return $this->errors;
    }
    
    /**
     * Get first error message
     * 
     * @return string|null
     */
    public function getFirstError() {
        return !empty($this->errors) ? reset($this->errors) : null;
    }
    
    /**
     * Clear all errors
     */
    public function clearErrors() {
        $this->errors = [];
    }
    
    /**
     * Add custom error
     * 
     * @param string $fieldName Field name
     * @param string $message Error message
     */
    public function addError($fieldName, $message) {
        $this->errors[$fieldName] = $message;
    }
}
?>
