<?php
/** DOCUMENTATION:
 * THIS API WILL BE RUNNING ON PORT 8000
 * entry point for GET requests and acts a route to see api status; eg is it online?
 * 
*/
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

header("Content-Type: application/json"); //setup to return response as josn.

if ($_SERVER["REQUEST_METHOD"] === "GET") {

    //result of visiting the port - returns status:
    echo json_encode(["message" => "PHP Email API is running!"]);
} else {

    //methods from the port alone are not allowed:
    http_response_code(405);
    echo json_encode(["error" => "Invalid request method."]);
}
