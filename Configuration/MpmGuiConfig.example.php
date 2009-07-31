<?php
	$MpmGuiConfig = array(
		'MpmOptions' => array(
			'indexPath' => 'Data/MprIndex/',  // the folder where you want to save the search index [relative or absolute]
			'zipPath'   => 'Data/MprZip/',    // the folder where to save/expect full Plugins as zip files [relative or absolute]
			'path'      => '../mpr/',         // relative path to the Repository
			'linkParam' => ''
		),
		'MprOptions' => array(
			'exclude'      => array('mprjs.php', 'jsspec.js', 'jquery', 'diffmatchpatch.js', 'mprfullcore.js'),  // files that shouldn't be opened while creating the the complete script file
			'cssMprIsUsed' => true, // do you also use <link rel="stylesheet" type="text/css" href="MprCss.php" media="screen, projection" />?
			'cache'        => true, // save cache and reuse it?
			'pathToMpr'    => '../mpr/',
			'cachePath'    => 'Data/MprCache/',  // where to save the cache [relative or absolute]
			'compressJs'   => 'minify', //[none, minify] should the generated Js be minified?
			'compressCss'  => 'minify' //[none, minify] should the generated Css be minified?
		),
		'path' => '' //relative path to this script (if included in an CMS)
	);
?>