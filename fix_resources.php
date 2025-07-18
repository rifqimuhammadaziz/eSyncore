<?php

// Script to automatically fix Filament resource page registrations
echo "Fixing Filament resource page registrations...\n";

$resourcesDir = __DIR__ . '/app/Filament/Resources';
$resourceFiles = glob($resourcesDir . '/*.php');
$fixedCount = 0;

foreach ($resourceFiles as $file) {
    $content = file_get_contents($file);
    $className = basename($file, '.php');
    
    // Only process files that use ::class in getPages
    if (strpos($content, 'getPages()') !== false && 
        strpos($content, '::class') !== false &&
        strpos($content, 'getPages') !== false) {
        
        echo "Processing $className...\n";
        
        // Replace the ::class with ::route() pattern
        $patterns = [
            "/'index' => Pages\\\\List([^:]+)::class/" => "'index' => Pages\\\\List$1::route('/')",
            "/'create' => Pages\\\\Create([^:]+)::class/" => "'create' => Pages\\\\Create$1::route('/create')",
            "/'edit' => Pages\\\\Edit([^:]+)::class/" => "'edit' => Pages\\\\Edit$1::route('/{record}/edit')",
            "/'view' => Pages\\\\View([^:]+)::class/" => "'view' => Pages\\\\View$1::route('/{record}')",
        ];
        
        $newContent = $content;
        $changes = false;
        
        foreach ($patterns as $pattern => $replacement) {
            $count = 0;
            $newContent = preg_replace($pattern, $replacement, $newContent, -1, $count);
            if ($count > 0) {
                $changes = true;
            }
        }
        
        if ($changes) {
            file_put_contents($file, $newContent);
            echo "Fixed page registrations in $className\n";
            $fixedCount++;
        }
    }
}

echo "\nFixed $fixedCount resources.\n";
echo "Done! Try running migrations now.\n";
