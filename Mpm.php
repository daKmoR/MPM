<?php
	require_once 'Classes/class.MpmGui.php';
	
	$MpmGuiConfig = '';
	if( is_file(dirname(__FILE__) . '/Configuration/MpmGuiConfig.php') )
		include_once dirname(__FILE__) . '/Configuration/MpmGuiConfig.php';
	
	$MpmGui = new MpmGui( $MpmGuiConfig );
	$MpmGui->render();
?>