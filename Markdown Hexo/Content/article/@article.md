<?php

// troubleshooting
// error_reporting(E_ALL);
// ini_set('display_errors', '1');

require('markdown_processor.php');

// front-matter
print ('---' . PHP_EOL);

if (!empty($article->title)) print ('title: ' . myPrintTextRunAsMarkdown($article->title, 'title') . PHP_EOL);

print ('---' . PHP_EOL);

myPrintArticleMarkdown($article);

?>