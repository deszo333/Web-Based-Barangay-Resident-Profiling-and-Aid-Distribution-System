<?php
require_once __DIR__ . '/../config/db_connect.php';

if(!$conn){
    die("error");
}

$result = mysqli_query(
    $conn,
    "SELECT household_number 
     FROM registered_household 
     ORDER BY id DESC 
     LIMIT 1"
);

if($row = mysqli_fetch_assoc($result)){
    $lastNum = intval(substr($row['household_number'], 3));
    $newNum = $lastNum + 1;
} else {
    $newNum = 1;
}

$next = 'HH-' . str_pad($newNum, 5, '0', STR_PAD_LEFT);

echo $next;
