<?php

/** 
 * \brief Prints an article as Markdown.
 *
 * \param &$article An article object with a 'flat' hierarchy.
 *
 */
function myPrintArticleMarkdown(&$article)
{
  if (is_array($article->description)) {
    $description = myPrintTextRunAsMarkdown($article->description);
	echo <<<EOT
$description
EOT;
  }

	if (is_array($article->steps))
	{
		// Empty lines between steps
		if (!empty($description))
			echo PHP_EOL . PHP_EOL;
			
		$stepContent = '';
		$imgIndex = 0;
		$mediaRef = array();
		
		foreach($article->steps as $step)
		{
		
			// Empty lines between steps
			if (!empty($stepContent))
				echo PHP_EOL . PHP_EOL;
		
		// Determine step H tag.
    $stepTag = ($step->level > 1) ? '###' : '##';
		$imageMarkup = '';
		$imageCaption = '';
		
		switch ($step->media->type)
    {
      case 'html':
        $hasImage = true;
        $imageMarkup = $step->media->html;
        break;
      
      default:
     		$hasImage = !empty($step->media->fullsize->relative_filename);
		
        if ($hasImage)
        {
          $hasThumbnail = is_object($step->media->thumbnail);
            
          // Determine urls, width, etc. based on presence of thumbnail
          if ($hasThumbnail)
          {
            $imageURL = $step->media->thumbnail->relative_filename;
            $imageWidth = $step->media->thumbnail->width;
            $imageHeight = $step->media->thumbnail->height;
            $imageLink = $step->media->fullsize->relative_filename;
          } else {
            $imageURL = basename($step->media->fullsize->relative_filename);
            $imageWidth = $step->media->fullsize->width;
            $imageHeight = $step->media->fullsize->height;
            $imageLink = '';
          }
          
          // Markdown likes relative paths without preceding ./
          if (strpos($imageURL, './') === 0) $imageURL = substr($imageURL, 2);
          $mediaAlt = myPrepareStringForMarkdown($step->media_alt);
          
          $imgIndex++;
        
          // Get HTML for step image
          ob_start();
          //![$mediaAlt]({$imageURL})
          echo <<<EOT
{% asset_img {$imageURL} %}
EOT;
          $imageMarkup = ob_get_clean();
        }
        break;
    }
		
		// Start Output
		$stepContent = '';
		$title = myPrintTextRunAsMarkdown($step->title, 'title');
		$instructions = myPrintTextRunAsMarkdown($step->instructions);
	
		// Title
		if (!empty($title))
		{
			ob_start();
			echo <<<EOT
{$stepTag} {$title}
EOT;
			$stepContent .= ob_get_clean();
		}
		
		// Spacing between title and rest of content
		if (!empty($stepContent) && (!empty($imageMarkup) || is_array($step->instructions)))
			$stepContent .= PHP_EOL . PHP_EOL; // empty line between title and rest of step content
		
		// Just instructions
		if (empty($imageMarkup) && is_array($step->instructions)) {
			ob_start();
			echo <<<EOT
$instructions
EOT;
			$stepContent .= ob_get_clean();
		}
		// Instructions and image
		elseif (!empty($imageMarkup) && is_array($step->instructions)) {
			ob_start();
			if ($step->instructions_position == 'above') {
				echo <<<EOT
{$instructions}

{$imageMarkup}
EOT;
    	} else {
    			echo <<<EOT
{$imageMarkup}

{$instructions}
EOT;
    	}
    	$stepContent .= ob_get_clean();
    	
		}
		// Just image
		elseif (!empty($imageMarkup)) {
			ob_start();
			echo <<<EOT
{$imageMarkup}
EOT;
			$stepContent .= ob_get_clean();
  	}
  	
  	echo $stepContent;
	} //foreach
	}
}


/**
 * \brief Prints a text run as Markdown.
 *
 * \param $textRun
 * \param $type 'instructions' or 'title'.
 */
function myPrintTextRunAsMarkdown($textrun, $type='instructions') {
  $output = '';
  $lastStyle = -1;
  
  if (!is_array($textrun)) return '';
  
  foreach($textrun as $para)
  {
    $closingPara = '';
    
   	// markdown needs space between preceding paragraph or it won't render
    if ($lastStyle != -1)
    {
    	if (!empty($lastStyle) && !empty($para->style->list_style))
				$output .= PHP_EOL; // list items don't need blank line between them.
    	else
    		$output .= PHP_EOL . PHP_EOL; // regular paragraphs need two returns between them.
    }
    
    /* Unused    
    $para->style->align  
    */
    if ($para->metadata->style == 'code')
    {
    	$output .= '{% codeblock %}' . PHP_EOL;
    	$output .= '     ';
    	//$closingPara = PHP_EOL;
      //$output .= PHP_EOL . '```' . PHP_EOL;
      //$closingPara = PHP_EOL . '```' . PHP_EOL;
      $closingPara = PHP_EOL . ' {% endcodeblock %}' . PHP_EOL;
      
    }
    else if (!empty($para->style->list_style))
    {      	
      switch ($para->style->list_style)
      {      	
        case 'decimal':
          if ($para->style->list_depth > 1)
	          $output .= str_repeat(' ', ($para->style->list_depth - 1) * 2);
	        $output .= '1. ' ;
          break;
        default:
        	if ($para->style->list_depth > 1)
	          $output .= str_repeat(' ', ($para->style->list_depth - 1) * 2);
	        $output .= '* ';
          break;
      }
    }
    
    // store for next loop
    $lastStyle = $para->style->list_style;
    
    if (isset($para->runs))
    {
      foreach ($para->runs as $run)
      {
        $closingRun = '';
        $prefix = '';
        $suffix = '';
        $styles = explode(',', $run->style->font_styles);
        
         if (property_exists($run->style, 'link') && is_object($run->style->link) && !empty($run->style->link->url)) {
          $prefix = '[';
          $suffix = ']';
          $closingRun  = '(' . $run->style->link->url . ')';
        }
              
        $hasBold = array_search('bold', $styles) !== FALSE;
        $hasItalic = array_search('italic', $styles) !== FALSE;
        $hasUnderline = array_search('underline', $styles) !== FALSE;
      
        if ($hasItalic) { $prefix .= '*'; $suffix = '*' . $suffix; }
        if ($hasBold) { $prefix .= '**'; $suffix = '**' . $suffix; }
      
        /* Unused
        $run->style->font_family
        $run->style->font_size
        $run->style->text_shift
        */
      
        $output .= $prefix;
        $output .= myPrepareStringForMarkdown($run->text, $para->metadata->style);
        $output .= $suffix;
        $output .= $closingRun;
      }
    }
        
    $output .= $closingPara;
  }
  
  return $output;
}

/**
 * \brief Prepares a string for output.
 *
 */
function myPrepareStringForMarkdown($str, $metadata_style='')
{
  // any vertical tabs in code need spaces
  if ($metadata_style == 'code')
		$str = str_replace("\v", PHP_EOL . '     ', $str);
	else
		$str = str_replace("\v", PHP_EOL, $str);

	return $str;
	//$escapechars = '/([' .preg_quote('\`*_{}[]()#+-.!') . '])/';
	//return preg_replace($escapechars, '\\\\${1}', $str);
}

?>