<?php 

// get_files_from_directory("plugins/", function to do whatever with files)

function get_files_from_directory($directory, $process)
{
		
		if (is_dir($directory))
		{
			if ($dir_handle = opendir($directory)) 
			{
				while (($file = readdir($dir_handle)) !== false) 
				{
					if (substr($file, 0, 1) == '.' || substr($file, 0, 1) == '-')
					{ 
						continue; 
					}
					
					$file = $process;
				
				}
				closedir($dir_handle);
			}
		}
		
		return $css;
}



?>