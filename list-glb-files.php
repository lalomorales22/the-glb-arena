<?php
// Get all GLB files from the Insert-GLBS folder
$directory = 'Insert-GLBS/';
$files = glob($directory . '*.glb');

// Remove directory prefix from filenames for easier loading
$files = array_map(function($file) use ($directory) {
    return $directory . basename($file);
}, $files);

// Sort alphabetically
sort($files);

// Return as JSON
header('Content-Type: application/json');
echo json_encode($files);
?>
