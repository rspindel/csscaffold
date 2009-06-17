<?php defined('BASEPATH') OR die('No direct access allowed.');

require BASEPATH . 'libraries/class.si_dom.php';

/**
 * NestedSelectorsPlugin class
 *
 * @package cacheer
 **/
class NestedSelectors extends Plugins
{
	
	var $DOM;
	
	function process($css)
	{
		/******************************************************************************
		* Process nested selectors
		 ******************************************************************************/
		// Transform the CSS into XML
		// does not like the data: protocol
						
		$xml = trim($css);
		$xml = preg_replace('#(/\*[^*]*\*+([^/*][^*]*\*+)*/)#', '', $xml); // Strip comments to prevent parsing errors
		$xml = str_replace('"', '#SI-CSSC-QUOTE#', $xml);
		$xml = preg_replace('/([-_A-Za-z]+)\s*:\s*([^;}{]+)(?:;)/ie', "'<property name=\"'.trim('$1').'\" value=\"'.trim('$2').'\" />'", $xml); // Transform properties
		$xml = preg_replace('/(\s*)([*#.A-Za-z@:][_@#.0-9A-Za-z*\(\)\[\]^\"\'=\$>:,\s-]*?)\{/me', "'$1<rule selector=\"'.preg_replace('/\s+/', ' ', trim(str_replace('>','&gt;','$2'))).'\">'", $xml); // Transform selectors
		$xml = preg_replace('/\!?\}/', '</rule>', $xml); // Close rules
		$xml = preg_replace('/\n/', "\r\t", $xml); // Indent everything one tab
		$xml = '<?xml version="1.0" ?'.">\r<css>\r\t$xml\r</css>\r"; // Tie it all up with a bow

		//header('Content-type: text/html');
		//echo $xml;
		//exit();
		
		/******************************************************************************
		* Parse the XML into a crawlable DOM
		 ******************************************************************************/
		$this->DOM = new SI_Dom($xml);
		$rule_nodes =& $this->DOM->getNodesByNodeName('rule');
		
		/******************************************************************************
		* Rebuild parsed CSS
		 ******************************************************************************/
		$css = '';
		$standard_nest = '';
		foreach ($rule_nodes as $node)
		{	
			if (preg_match('#^@media#', $node->selector))
			{
				$standard_nest = $node->selector;
				$css .= $node->selector.' {';
			}
			
			$properties = $node->getChildNodesByNodeName('property');
			if (!empty($properties))
			{
				$selector = str_replace('&gt;', '>', $this->parseAncestorSelectors($this->getAncestorSelectors($node)));
				
				if (!empty($standard_nest))
				{
					if (substr_count($selector, $standard_nest))
					{
						$selector = trim(str_replace($standard_nest, '', $selector));
					}
					else
					{
						$css .= '}';
						$standard_nest = '';
					}
				}
				
				$selector = str_replace('#SI-CSSC-QUOTE#', '"', $selector);
								
				$css .= $selector.'{';
				foreach($properties as $property)
				{	
					$css .= $property->name.':'.str_replace('#SI-CSSC-QUOTE#', '"', $property->value).';';
				}
				$css .= '}';
			}	
		}
		
		if (!empty($standard_nest))
		{	
			$css .= '}';
			$standard_nest = '';
		}
		
		return $css;
	}
	
	function getAncestorSelectors($node)
	{
		$selectors = array();

		if (!empty($node->selector))
		{
			$selectors[] = $node->selector;
		}
		if (!empty($node->parentNodeId))
		{
			$parentNode = $this->DOM->nodeLookUp[$node->parentNodeId];
			if (isset($parentNode->selector))
			{
				$recursiveSelectors = $this->getAncestorSelectors($parentNode);
				$selectors = array_merge($selectors, $recursiveSelectors);
			}
		}
		return $selectors;
	}

	function parseAncestorSelectors($ancestors = array())
	{
		$growth = array();
		foreach($ancestors as $selector)
		{
			$these = preg_split('/,\s*/', $selector);
			if (empty($growth))
			{
				$growth = $these;
				continue;
			}

			$fresh = array();

			foreach($these as $tSelector)
			{
				foreach($growth as $gSelector)
				{
					$fresh[] = $tSelector.(($gSelector{0} != ':')?' ':'').$gSelector;
				}
			}
			$growth = $fresh;
		}
		return implode(',', $growth);
	}
}
