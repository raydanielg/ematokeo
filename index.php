<?php
// Simple front controller to redirect all requests to Laravel's public/index.php
// Useful when your hosting document root points to the project root instead of /public.

// If you want to hard-code a subdirectory, adjust this path accordingly.
require __DIR__ . '/public/index.php';
