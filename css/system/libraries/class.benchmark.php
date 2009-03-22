<?php

class Benchmark
{
	var $time = array();
	
	function mark($label)
	{
		global $time;
		
		$time[$label] = microtime();
	}
	
	function elapsed_time($label1, $label2)
	{
		global $time; 
		
		list($sm, $ss) = explode(' ', $time[$label1]);
		list($em, $es) = explode(' ', $time[$label2]);
		
		return ($em + $es) - ($sm + $ss);
	}
	
	/**
	 * Generate benchmark reports from the css
	 *
	 * @return void
	 * @author Anthony Short
	 **/
	public function generate_report()
	{
	 	$s = "";
	 	
		// Output the benchmark text file
		foreach($filesize as $plugin_class => $css_size)
		{
			// Make the report line
			$s .= "Filesize after ".$plugin_class." => ".$css_size."\n";
		}
		
		// Create the ratio in the string
		$size_ratio = 100 - (end($filesize) / reset($filesize) * 100) ."%";
		
		$s .= "\n\n Compression Ratio = ". $size_ratio;
		$s .= "\n Final CSS Size (as file before Gzip) = ". fileSize($cached_file) ." bytes (". fileSize($cached_file) / 1024 . " kB)";
		
		// Open the file relative to /css/
		$benchmark_file = fopen(BASEPATH . "/logs/css_report.txt", "w") or die("Can't open the report.txt file");
		// Write the string to the file
		fwrite($benchmark_file, $s);
		//chmod($benchmark_file, 777);
		fclose($benchmark_file);
	}

}