<?php if (!defined('CSS_CACHEER')) { header('Location:/'); }

/**
 * ConstantsPlugin class
 *
 * @package Cacheer
 **/
class Constants extends Plugins
{

	function process($css)
	{ 		
		// Add some default constants
		$constants = array(
			"const(asset_path)" => ASSETPATH
		);
		
		if (preg_match_all('#@constants\s*\{\s*([^\}]+)\s*\}\s*#i', $css, $matches))
		{
			foreach ($matches[0] as $i => $constant)
			{
				$css = str_replace($constant, '', $css);
				preg_match_all('#([_a-z0-9]+)\s*:\s*([^;]+);#i', $matches[1][$i], $vars);
				foreach ($vars[1] as $var => $name)
				{
					$constants["const($name)"] = $vars[2][$var];
				}
			}
		}
		
		// Override any constants with our XML constants
		$xml = load(ASSETPATH . "/xml/constants.xml");
		
		// Turn it into an xml object
		$xml = simplexml_load_string($xml);
		
		// Replace the constants in the array with the XML constants		
		foreach($xml->constant as $key => $constant)
		{
			$constants["const(".$constant->name.")"] = (int) $constant->val;
		}
	
		if (!empty($constants))
		{
			$css = str_replace(array_keys($constants), array_values($constants), $css);
		} 
		
		// Clean up
		unset($constants);
		
		return $css;
	}

} // END ConstantsPlugin