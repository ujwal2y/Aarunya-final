<?php
/**
 * Environment Configuration Loader
 * Loads and manages environment variables from .env file
 */

class Environment {
    
    private static $loaded = false;
    private static $variables = [];
    
    /**
     * Load environment variables from .env file
     */
    public static function load($envFile = null) {
        if (self::$loaded) {
            return;
        }
        
        if ($envFile === null) {
            $envFile = __DIR__ . '/../../.env';
        }
        
        if (!file_exists($envFile)) {
            throw new Exception('.env file not found. Please copy .env.example to .env and configure it.');
        }
        
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            // Skip comments
            if (strpos(trim($line), '#') === 0) {
                continue;
            }
            
            // Parse key=value pairs
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                
                // Remove quotes if present
                if (preg_match('/^(["\'])(.*)\\1$/', $value, $matches)) {
                    $value = $matches[2];
                }
                
                // Store in class variable
                self::$variables[$key] = $value;
                
                // Also set as environment variable
                putenv("$key=$value");
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
            }
        }
        
        self::$loaded = true;
    }
    
    /**
     * Get environment variable
     * 
     * @param string $key Variable name
     * @param mixed $default Default value if not found
     * @return mixed
     */
    public static function get($key, $default = null) {
        if (!self::$loaded) {
            self::load();
        }
        
        // Check in order: class variables, $_ENV, $_SERVER, getenv()
        if (isset(self::$variables[$key])) {
            return self::$variables[$key];
        }
        
        if (isset($_ENV[$key])) {
            return $_ENV[$key];
        }
        
        if (isset($_SERVER[$key])) {
            return $_SERVER[$key];
        }
        
        $value = getenv($key);
        if ($value !== false) {
            return $value;
        }
        
        return $default;
    }
    
    /**
     * Get boolean environment variable
     * 
     * @param string $key Variable name
     * @param bool $default Default value
     * @return bool
     */
    public static function getBool($key, $default = false) {
        $value = self::get($key, $default);
        
        if (is_bool($value)) {
            return $value;
        }
        
        $value = strtolower(trim($value));
        return in_array($value, ['true', '1', 'yes', 'on'], true);
    }
    
    /**
     * Get integer environment variable
     * 
     * @param string $key Variable name
     * @param int $default Default value
     * @return int
     */
    public static function getInt($key, $default = 0) {
        return (int) self::get($key, $default);
    }
    
    /**
     * Get array environment variable (comma-separated)
     * 
     * @param string $key Variable name
     * @param array $default Default value
     * @return array
     */
    public static function getArray($key, $default = []) {
        $value = self::get($key);
        
        if (empty($value)) {
            return $default;
        }
        
        return array_map('trim', explode(',', $value));
    }
    
    /**
     * Check if environment variable exists
     * 
     * @param string $key Variable name
     * @return bool
     */
    public static function has($key) {
        if (!self::$loaded) {
            self::load();
        }
        
        return isset(self::$variables[$key]) || 
               isset($_ENV[$key]) || 
               isset($_SERVER[$key]) || 
               getenv($key) !== false;
    }
    
    /**
     * Get all environment variables
     * 
     * @return array
     */
    public static function all() {
        if (!self::$loaded) {
            self::load();
        }
        
        return self::$variables;
    }
}

// Auto-load environment variables
Environment::load();
?>
