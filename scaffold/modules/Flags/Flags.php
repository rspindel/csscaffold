<?php

/**
 * Flags
 *
 * Lets you only display chunks of CSS is a particular flag is set. The modules which
 * rely on flagging depend on this module. Without this, they don't do anything.
 *
 * The @flag(name) syntax works as a wrapper for selectors and properties

	@flag(ie6)
	{
		#id
		{
			zoom:1;
		}
	}

 * 
 */
class Flags extends Scaffold_Module
{

	/**
	 * Post Process. Needs to come after the nested selectors.
	 *
	 * @author Anthony Short
	 * @param $css string
	 * @return $css string
	 */
	public static function post_process($css)
	{
		if( $found = Scaffold_CSS::find_at_group('flag', $css) )
		{
			foreach($found['groups'] as $group_key => $group)
			{
				$flags = explode(',',$found['flag'][$group_key]);
				
				# See if any of the flags are set
				foreach($flags as $flag_key => $flag)
				{
					if(CSScaffold::flag($flag))
					{
						$parse = true;
						break;
					}
					else
					{
						$parse = false;
					}
				}
				
				if($parse)
				{
					# Just remove the flag name, and it should just work.
					$css = str_replace($found['groups'][$group_key],$found['content'][$group_key],$css);
				}
				else
				{
					# Get it out of there, that flag isn't set!
					$css = str_replace($found['groups'][$group_key],'',$css);
				}
			}
			
			# Loop through the newly parsed CSS to look for more flags
			$css = self::post_process($css);
		}

		return $css;
	}
}