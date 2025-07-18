<?php

// A simple script to find potential issues with Filament resource page registrations
// without loading the full Laravel application

$resourcesDir = __DIR__ . '/app/Filament/Resources';
$issues = [];

// List all resource files
$resourceFiles = glob($resourcesDir . '/*.php');
foreach ($resourceFiles as $file) {
    $content = file_get_contents($file);
    $className = basename($file, '.php');
    
    // Check if the file has pages but uses ::class instead of ::route()
    if (strpos($content, 'getPages()') !== false && 
        strpos($content, '::class') !== false &&
        strpos($content, 'getPages') !== false) {
        
        // Extract the getPages method content
        preg_match('/public\s+static\s+function\s+getPages\s*\(\s*\)\s*:\s*array\s*\{(.*?)\}/s', $content, $matches);
        
        if (isset($matches[1]) && strpos($matches[1], '::class') !== false) {
            $issues[] = [
                'file' => $file,
                'class' => $className,
                'issue' => 'Uses ::class instead of ::route() in getPages()',
                'content' => trim($matches[1])
            ];
        }
    }
}

// Print issues found
echo "Found " . count($issues) . " potential issues:\n\n";

foreach ($issues as $issue) {
    echo "File: " . $issue['file'] . "\n";
    echo "Class: " . $issue['class'] . "\n";
    echo "Issue: " . $issue['issue'] . "\n";
    echo "Content:\n" . $issue['content'] . "\n";
    echo "\n-----------------------------------\n\n";
}

if (count($issues) === 0) {
    echo "No issues found with page registrations format.\n";
    echo "The error might be caused by a different problem.\n";
}
