<?php

$config['offset'] = -13;

# Here you can create special flags for different times of the day, week, month or year.
$config['flags'] = array
(

	# Morning is the name of the flag
	'morning' => array
	(
		# Then we can set date, day, hour, month, week or year
		'hour' => array
		(
			'from' => '5',
			'to'   => '11'
		)
	),

	'black_friday' => array
	(
		'date' => 13,
		'day' => 'Friday'
	),
	
	'halloween' => array
	(
		'date' => 31,
		'month' => 'October'
	),
	
	'beeroclock' => array
	(
		'hour' => 17,
		'day' => 'Friday'
	),
	
	'christmas' => array
	(
		'date' => '25',
		'month' => 'December'
	),
	
	'last_week' => array
	(
		'week' => 52
	),
	
	'december' => array
	(
		'week' => array
		(
			'from' => 48,
			'to'   => 52
		)
	),
	
	'first_month' => array
	(
		'month' => 12	
	),
	
	'summer' => array
	(
		'month' => array
		(
			'from' => 'December',
			'to'   => 'February'
		)
	),
	
	'millenium' => array
	(
		'year' => 2000,
		'date' => 1,
		'month' => 'January'
	)
);