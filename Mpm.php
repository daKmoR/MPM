<?php
	require_once 'Classes/class.MpmGui.php';
	
	// // if there is a MprConfig.php file in the root folder include it - you can override any value there
	// if( is_file('Configuration/MpmConfig.php') )
		// include_once 'Configuration/MpmConfig.php';
	
	$MpmGui = new MpmGui();
	$MpmGui->render();
?>