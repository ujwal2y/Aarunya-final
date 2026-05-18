<?php
/**
 * Aarunya Healthcare - Centralized Validation Library
 * Provides comprehensive server-side validation
 */

class AarunyaValidator {
    
    // Validation patterns
    private static $patterns = [
        'email' => '/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',  // Standard email format
        'phone' => '/^[0-9]{10}$/',
        'name' => '/^[a-zA-Z\s]+$/',
        'password' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#])[A-Za-z\d@$!%*?&#]{8,}$/'
    ];
    
    // Error messages
    private static $messages = [
        'email' => [
            'required' => 'Email address is required',
            'invalid' => 'Please enter a valid email address'
        ],
        'phone' => [
            'required' => 'Phone number is required',
            'invalid' => 'Phone number must be exactly 10 digits',
            'numeric' => 'Only numeric values are allowed'
        ],
        'password' => [
            'required' => 'Password is required',
            'minLength' => 'Password must be at least 8 characters long',
            'weak' => 'Password must contain uppercase, lowercase, number, and special character'
        ],
        'name' => [
            'required' => 'Name is required',
            'invalid' => 'Please enter a valid name (letters and spaces only)'
        ]
    ];
    
    /**
     * Sanitize input to prevent XSS
     */
    public static function sanitize($input) {
        if (is_array($input)) {
            return array_map([self::class, 'sanitize'], $input);
        }
        
        $input = trim($input);
        $input = stripslashes($input);
        $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
        return $input;
    }
    
    /**
     * Validate Email
     */
    public static function validateEmail($email) {
        $email = trim($email);
        
        if (empty($email)) {
            return ['valid' => false, 'message' => self::$messages['email']['required']];
        }
        
        if (!preg_match(self::$patterns['email'], $email)) {
            return ['valid' => false, 'message' => self::$messages['email']['invalid']];
        }
        
        return ['valid' => true, 'message' => ''];
    }
    
    /**
     * Validate Phone Number
     */
    public static function validatePhone($phone) {
        $phone = trim($phone);
        
        if (empty($phone)) {
            return ['valid' => false, 'message' => self::$messages['phone']['required']];
        }
        
        // Remove spaces and special characters
        $cleanPhone = preg_replace('/\s+/', '', $phone);
        
        // Check if contains only numbers
        if (!ctype_digit($cleanPhone)) {
            return ['valid' => false, 'message' => self::$messages['phone']['numeric']];
        }
        
        // Check if exactly 10 digits
        if (!preg_match(self::$patterns['phone'], $cleanPhone)) {
            return ['valid' => false, 'message' => self::$messages['phone']['invalid']];
        }
        
        return ['valid' => true, 'message' => '', 'cleaned' => $cleanPhone];
    }
    
    /**
     * Validate Password
     */
    public static function validatePassword($password) {
        if (empty($password)) {
            return ['valid' => false, 'message' => self::$messages['password']['required']];
        }
        
        if (strlen($password) < 8) {
            return ['valid' => false, 'message' => self::$messages['password']['minLength']];
        }
        
        if (!preg_match(self::$patterns['password'], $password)) {
            return ['valid' => false, 'message' => self::$messages['password']['weak']];
        }
        
        return ['valid' => true, 'message' => ''];
    }
    
    /**
     * Validate Name
     */
    public static function validateName($name) {
        $name = trim($name);
        
        if (empty($name)) {
            return ['valid' => false, 'message' => self::$messages['name']['required']];
        }
        
        if (!preg_match(self::$patterns['name'], $name)) {
            return ['valid' => false, 'message' => self::$messages['name']['invalid']];
        }
        
        return ['valid' => true, 'message' => ''];
    }
    
    /**
     * Capitalize Name Properly
     */
    public static function capitalizeName($name) {
        $name = trim($name);
        $words = explode(' ', $name);
        $capitalizedWords = array_map(function($word) {
            return ucfirst(strtolower($word));
        }, $words);
        return implode(' ', $capitalizedWords);
    }
    
    /**
     * Hash Password Securely
     */
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }
    
    /**
     * Verify Password
     */
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    /**
     * Validate Required Field
     */
    public static function validateRequired($value, $fieldName = 'This field') {
        $value = trim($value);
        
        if (empty($value)) {
            return ['valid' => false, 'message' => $fieldName . ' is required'];
        }
        
        return ['valid' => true, 'message' => ''];
    }
    
    /**
     * Validate Age
     */
    public static function validateAge($age, $min = 18, $max = 100) {
        if (!is_numeric($age)) {
            return ['valid' => false, 'message' => 'Age must be a number'];
        }
        
        $age = intval($age);
        
        if ($age < $min || $age > $max) {
            return ['valid' => false, 'message' => "Age must be between $min and $max"];
        }
        
        return ['valid' => true, 'message' => ''];
    }
    
    /**
     * Validate Date
     */
    public static function validateDate($date, $format = 'Y-m-d') {
        if (empty($date)) {
            return ['valid' => false, 'message' => 'Date is required'];
        }
        
        $d = DateTime::createFromFormat($format, $date);
        
        if (!$d || $d->format($format) !== $date) {
            return ['valid' => false, 'message' => 'Invalid date format'];
        }
        
        return ['valid' => true, 'message' => ''];
    }
    
    /**
     * Check if Email Exists in Database
     */
    public static function emailExists($pdo, $email, $excludeId = null) {
        $sql = "SELECT id FROM users WHERE email = ?";
        $params = [$email];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetch() !== false;
    }
    
    /**
     * Check if Phone Exists in Database
     */
    public static function phoneExists($pdo, $phone, $excludeId = null) {
        $sql = "SELECT id FROM users WHERE phone = ?";
        $params = [$phone];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetch() !== false;
    }
    
    /**
     * Validate Registration Data
     */
    public static function validateRegistration($data, $pdo = null) {
        $errors = [];
        
        // Validate name
        $nameResult = self::validateName($data['name'] ?? '');
        if (!$nameResult['valid']) {
            $errors['name'] = $nameResult['message'];
        }
        
        // Validate email
        $emailResult = self::validateEmail($data['email'] ?? '');
        if (!$emailResult['valid']) {
            $errors['email'] = $emailResult['message'];
        } elseif ($pdo && self::emailExists($pdo, $data['email'])) {
            $errors['email'] = 'Email address is already registered';
        }
        
        // Validate phone (if provided)
        if (!empty($data['phone'])) {
            $phoneResult = self::validatePhone($data['phone']);
            if (!$phoneResult['valid']) {
                $errors['phone'] = $phoneResult['message'];
            } elseif ($pdo && self::phoneExists($pdo, $phoneResult['cleaned'])) {
                $errors['phone'] = 'Phone number is already registered';
            }
        }
        
        // Validate password
        $passwordResult = self::validatePassword($data['password'] ?? '');
        if (!$passwordResult['valid']) {
            $errors['password'] = $passwordResult['message'];
        }
        
        // Validate age (if provided)
        if (!empty($data['age'])) {
            $ageResult = self::validateAge($data['age']);
            if (!$ageResult['valid']) {
                $errors['age'] = $ageResult['message'];
            }
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Validate Login Data
     */
    public static function validateLogin($data) {
        $errors = [];
        
        // Validate email or phone
        $identifier = trim($data['email'] ?? $data['identifier'] ?? '');
        
        if (empty($identifier)) {
            $errors['identifier'] = 'Email or phone number is required';
        } else {
            // Check if it's email or phone
            if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
                $emailResult = self::validateEmail($identifier);
                if (!$emailResult['valid']) {
                    $errors['identifier'] = $emailResult['message'];
                }
            } elseif (ctype_digit($identifier)) {
                $phoneResult = self::validatePhone($identifier);
                if (!$phoneResult['valid']) {
                    $errors['identifier'] = $phoneResult['message'];
                }
            } else {
                $errors['identifier'] = 'Please enter a valid email or phone number';
            }
        }
        
        // Validate password
        if (empty($data['password'])) {
            $errors['password'] = 'Password is required';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Generate JSON Response
     */
    public static function jsonResponse($success, $message = '', $data = [], $errors = []) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => $success,
            'message' => $message,
            'data' => $data,
            'errors' => $errors
        ]);
        exit;
    }
}
?>
