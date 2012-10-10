<?php
//header('Content-Type: application/xhtml+xml; charset=UTF-8');

// Load basic required files
require 'system/load.php';


// Start routing the request
$frontcontroller = V7F\Controller\FrontController::getInstance();
$frontcontroller->handleRequest();
