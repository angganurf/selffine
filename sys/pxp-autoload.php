<?php 

spl_autoload_register(function($className) {
	$dirs  = array(
		'sys/app_core',
		'sys/libs/sitemap-php',
		'sys/libs/MySQL-Dump',
		'sys/server'
	);

	foreach ($dirs as $dir) {
		$path = "$dir/$className.php";
		if (file_exists($path)) {
			require_once($path);
		}
	}
});
