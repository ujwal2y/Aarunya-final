<?php
// Suppress warnings in production
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);
/**
 * Doctor Module - Database Connection
 * Uses the shared database configuration
 */

require_once __DIR__ . '/../../server/config/database.php';

// Get database connection
function getDoctorDB() {
    return getDB();
}

// Helper function to execute prepared statements safely
function executeQuery($query, $params = []) {
    $db = getDoctorDB();
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    return $stmt;
}

// Helper function to fetch single row
function fetchOne($query, $params = []) {
    $stmt = executeQuery($query, $params);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Helper function to fetch all rows
function fetchAll($query, $params = []) {
    $stmt = executeQuery($query, $params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Helper function to get last insert ID
function getLastInsertId() {
    $db = getDoctorDB();
    return $db->lastInsertId();
}
?>
