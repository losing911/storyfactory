<?php

$storyContent = "Neo-Pera sokaklarında Sendika ajanları dolaşıyor. Delfin bu durumu izliyor. Şehir yorgun.";

$keywords = [
    ['slug' => 'sendika', 'title' => 'Sendika (The Syndicate)', 'pattern' => 'Sendika'],
    ['slug' => 'delfin', 'title' => 'Delfin', 'pattern' => 'Delfin'],
];

echo "Original Content: $storyContent\n\n";

foreach ($keywords as $entry) {
    // Current Pattern Logic from Story.php
    // \b is the suspect.
    $pattern = '/(?<!<a href="[^"]*">)\b(' . preg_quote($entry['pattern'], '/') . ')(?!\w)\b(?!<\/a>)/iu';
    
    echo "Testing Pattern: $pattern\n";
    
    if (preg_match($pattern, $storyContent)) {
        echo "[MATCH] Found '{$entry['pattern']}'\n";
    } else {
        echo "[FAIL] Did NOT match '{$entry['pattern']}'\n";
    }
    
    $replacement = '<a href="/database/'.$entry['slug'].'">$1</a>';
    $storyContent = preg_replace($pattern, $replacement, $storyContent, 1);
}

echo "\nProcessed Content:\n" . $storyContent . "\n";
