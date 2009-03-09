<?php
/******************************************************************************
 Prevent direct access
 ******************************************************************************/
if (!defined('CSS_CACHEER')) { header('Location:/'); }

$plugin_class = 'ConstantsPlugin';
class ConstantsPlugin extends CacheerPlugin
{
	function process($css)
	{
		global $path;
			
		$constants = array();
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
		
		// Get the constants from the XML
		$xml = file_get_contents($path['xml'] . "/constants.xml");
		$this->DOM = new SI_Dom($xml);
		
		// Get the nodes
		$override_name =& $this->DOM->getNodesByNodeName('name');
		$override_value =& $this->DOM->getNodesByNodeName('value');
		
		// Replace the constants in the array with the XML constants		
		foreach($override_name as $key => $value)
		{
			$constants["const(".$value->cdata.")"] = $override_value[$key]->cdata;
		}
	
		if (!empty($constants))
		{
			$css = str_replace(array_keys($constants), array_values($constants), $css);
		} 
		
		return $css;
	}
}