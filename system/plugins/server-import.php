<?php if (!defined('CSS_CACHEER')) { header('Location:/'); }

/**
 * The class name
 * @var string
 */
$plugin_class = 'ServerImportPlugin';

/**
 * ServerImportPlugin class
 *
 * @package Cacheer
 **/
class ServerImportPlugin extends CacheerPlugin
{
	function pre_process($css)
	{
		global $relative_file, $relative_dir, $config;
		
		$imported = array($relative_dir.$relative_file);
		
		while (preg_match_all('#@server\s+import\s+url\(([^\)]+)+\);#i', $css, $matches))
		{
			foreach($matches[1] as $i => $include)
			{
				$include = preg_replace('#^("|\')|("|\')$#', '', $include);
				
				if(is_dir($include))
				{
					$include_css .= load($include);
					$css = str_replace($matches[0][$i], $include_css, $css);
				}
				else
				{
					// import each file once, only import css
					if (!in_array($include, $imported) && substr($include, -3) == 'css')
					{
						$imported[] = $include;
						$include_css = load($include);
						$css = str_replace($matches[0][$i], $include_css, $css);
					}
				}
				
				$css = str_replace($matches[0][$i], '', $css);
			}
		}
		return $css;
	}
}