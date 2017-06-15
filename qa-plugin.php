<?php
/*
	Plugin Name: q2apro Tag Experts
	Plugin URI: https://github.com/q2apro/q2apro-tagexperts
	Plugin Description: Choose a Tag and get the best users for this tag listed 
	Plugin Version: 0.1
	Plugin Date: 2017-06-15
	Plugin Author: q2apro.com
	Plugin Author URI: https://www.q2apro.com/
	Plugin License: GPLv3
	Plugin Minimum Question2Answer Version: 1.5
	Plugin Update Check URI: 

	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	More about this license: http://www.gnu.org/licenses/gpl.html
	
*/

if ( !defined('QA_VERSION') )
{
	header('Location: ../../');
	exit;
}

// page
qa_register_plugin_module('page', 'q2apro-tagexperts.php', 'q2apro_tagexperts', 'q2apro Tag Experts');



/*
	Omit PHP closing tag to help avoid accidental output
*/