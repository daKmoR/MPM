<?php
	$useGzip = true;
	$MprOptions = array(
		'exclude'      => array('mprjs.php', 'jsspec.js', 'jquery', 'diffmatchpatch.js', 'mprfullcore.js'),  // files that shouldn't be opened while creating the the complete script file
		'cssMprIsUsed' => true, // do you also use <link rel="stylesheet" type="text/css" href="MprCss.php" media="screen, projection" />?
		'cache'        => false, // save cache and reuse it?
		'pathToMpr'    => '../../../mpr/res/MPR/',
		'cachePath'    => '../../../../../typo3temp/mpm/cache/',  // where to save the cache [relative or absolute]
		'compressJs'   => 'none', //[none, minify] should the generated Js be minified?
		'compressCss'  => 'minify' //[none, minify] should the generated Css be minified?
	);	
?>