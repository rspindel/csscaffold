<?php

/**
 * Adds the linear-gradient property allowing a quick and dirty linear gradient with no color stops
 *
 * @author Kirk Bentley
 * @param $direction - Direction of linear gradient (top,right,bottom,left)
 * @param $from - From color
 * @param $to - To color
 * @return string
 */
function Scaffold_linear_gradient($params)
{
	if(preg_match_all('/\([^)]*?,[^)]*?\)/',$params, $matches))
	{
		foreach($matches as $key => $original)
		{
			$new = str_replace(',','#COMMA#',$original);
			$params = str_replace($original,$new,$params);
		}
	}

	$params = explode(',',$params);
	
	foreach(array('direction','from','to') as $key => $name)
	{
		$$name = trim(str_replace('#COMMA#',',', array_shift($params) ));
	}
	
	$applyTo = array(
		'-moz-linear-gradient',
		'filter',
		'-webkit-gradient'
		);

	foreach($applyTo as $k){
		switch($k){
			case '-moz-linear-gradient':
				$gecko = 'background-image:'."\t\t\t".'-moz-linear-gradient('.$direction.', '.$from.', '.$to.');'."\n";
				break;
			case '-webkit-gradient':
				switch($direction){
					case 'top':
						$direction = 'left top, left bottom';
						break;
					case 'right':
						$direction = 'right top, left top';
						break;
					case 'bottom':
						$direction = 'left bottom, left top';
						break;
					case 'left':
						$direction = 'left top, right top';
						break;
				}
				$webkit = 'background-image:'."\t\t\t".'-webkit-gradient(linear, '.$direction.', from('.$from.'), to('.$to.'));';
				break;
			case 'filter':
			  switch($direction){
					case 'top':
						$GradientType = 0;
						$startColorstr = $from;
						$endColorstr = $to;
						break;
					case 'right':
						$GradientType = 1;
						$startColorstr = $to;
						$endColorstr = $from;
						break;
					case 'bottom':
					$GradientType = 0;
					$startColorstr = $to;
					$endColorstr = $from;
						break;
					case 'left':
						$GradientType = 1;
						$startColorstr = $from;
						$endColorstr = $to;
						break;
				}
				$filter = 'filter:'."\t\t\t".'progid:DXImageTransform.Microsoft.gradient(GradientType='.$GradientType.',startColorstr='.$startColorstr.', endColorstr='.$endColorstr.');'."\n";
				$filter .= '-ms-filter:'."\t\t\t".'"progid:DXImageTransform.Microsoft.gradient(GradientType='.$GradientType.',startColorstr='.$startColorstr.', endColorstr='.$endColorstr.')";'."\n";
				break;
		}
	}
	
	// Build the selector
	$properties = $gecko."\n\t".$webkit."\n\t".$filter;

	return $properties;
}