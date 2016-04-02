<?php

// troubleshooting
// error_reporting(E_ALL);
// ini_set('display_errors', '1');

require('markdown_processor.php');

if (!empty($article->title)) print ('#' . myPrintTextRunAsMarkdown($article->title, 'title') . "\n\n");
myPrintArticleMarkdown($article);

?>