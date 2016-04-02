<?php

// troubleshooting
// error_reporting(E_ALL);
// ini_set('display_errors', '1');

require('markdown_processor.php');

// front-matter
print ('---' . PHP_EOL);

if (!empty($article->title)) print ('title: ' . myPrintTextRunAsMarkdown($article->title, 'title') . PHP_EOL);
//if (!empty($article->tag_list)) print ('tags: ' . printHexoAttributesForArticle($article->tags) . PHP_EOL);
print printHexoAttributesForArticle($article->tags,'tags');
//print printHexoAttributesForArticle($article->categories,'categories');

print ('---' . PHP_EOL);

myPrintArticleMarkdown($article);

?>