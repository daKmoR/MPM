<?php

	require_once 'Resources/Php/MpmDefaultConfig.php';
	
	$dir = dirname( realpath(__FILE__) );
	// if( $dir !== substr( realpath($_REQUEST['file']), 0, strlen($dir) ) )
		// die('you can only use files within MPR');

	require_once('Resources/Php/FirePHPCore/fb.php');
	require_once('Classes/class.Mpm.php');
	
	$path = array();
	if( isset($_REQUEST['file']) )
		$path = explode('/', $_REQUEST['file']);
	$pathPartCount = count($path);

	$Mpm = new Mpm( $MpmOptions );
	
	if( is_file('Configuration/USE_ADMIN_FUNCTIONS') )
		$Mpm->options->admin = true;
	
	if( isset($_REQUEST['mode']) ) {
		if ( $_REQUEST['mode'] === 'install' && $_REQUEST['file'] != '' ) {
			$status = $Mpm->install( $_REQUEST['file'] );
			$center = $status ? 'Install successful' : 'Install failed';
			
		} elseif ( $_REQUEST['mode'] === 'uninstall' && $_REQUEST['file'] != '' ) {
			$status = $Mpm->uninstall( $_REQUEST['file'] );
			$center = $status ? 'Uninstall successful' : 'Uninstall failed';

		} elseif ( $_REQUEST['mode'] === 'restore' && $_REQUEST['file'] != '') {
			$status = $Mpm->restore( $_REQUEST['file'] );
			$center = $status ? 'Restore successful' : 'Restore failed';
			
		} elseif ( $_REQUEST['mode'] === 'clearCache' ) {
			$Mpm->clearCache();
			
		}
	}
	
	$left = $Mpm->render();
	$js = '';
	$header = '';
	$center = '';
	
	if( isset($_REQUEST['mode']) ) {
	
		if ( $_REQUEST['mode'] === 'demo' && $_REQUEST['file'] != '' ) {
			$demoCode = file_get_contents( $_REQUEST['file'] );
			
			$center = Helper::getContent($demoCode, '<!-- ### Mpr.Html.Start ### -->', '<!-- ### Mpr.Html.End ### -->');
			$center = str_replace('"../', '"' . $Mpm->options->path . $path[$pathPartCount-4] . '/' . $path[$pathPartCount-3] . '/', $center );
			
			$codeHeader = Helper::getContent($demoCode, '<!-- ### Mpr.Header.Start ### -->', '<!-- ### Mpr.Header.End ### -->');
			if( $codeHeader ) $header .= $codeHeader;
			
			$css = Helper::getContent($demoCode, '/* ### Mpr.Css.Start ### */', '/* ### Mpr.Css.End ### */');
			if( $css ) $header .= Helper::wrap($css, '<style type="text/css">|</style>');
			
			$js = Helper::getContent($demoCode, '/* ### Mpr.Js.Start ### */', '/* ### Mpr.Js.End ### */');
			if( $js ) $header .= Helper::wrap($js, '<script type="text/javascript">|</script>');
			
			
		} elseif ( $_REQUEST['mode'] === 'docu' && $_REQUEST['file'] != '' ) {
			/*************************/
			// DOCU
			$header = '<link rel="stylesheet" href="Resources/css/docs.css" type="text/css" media="screen" />';
			$center = $Mpm->getDocu( file_get_contents($_REQUEST['file']) );
			
		} elseif ($_REQUEST['mode'] === 'spec')  {
			$header = '
				<link rel="stylesheet" href="Resources/css/specs.css" type="text/css" media="screen" />
				<script src="Resources/js/JSSpec.js" type="text/javascript"></script>
				<script src="Resources/js/DiffMatchPatch.js" type="text/javascript"></script>
				<script src="' . $_REQUEST['file'] . '" type="text/javascript"></script>
			';
			$center = '<div id="jsspec_container"></div>';
			
		} elseif ($_REQUEST['mode'] === 'indexing') {
			$Mpm->newIndex();
			
		} elseif ( $_REQUEST['mode'] === 'search' && $_REQUEST['query'] != '' ) {
			$center = $Mpm->search( $_REQUEST['query'] );

			if( $_REQUEST['ajax'] ) {
				echo $center;
				die();
			}
			
			
		} elseif ( $_REQUEST['mode'] === 'zip' && $_REQUEST['file'] != '' ) {
			$Mpm->getZip( $_REQUEST['file'] );
			
			
		} elseif ( $_REQUEST['mode'] === 'pluginDetails' && $_REQUEST['file'] != '' ) {
			$center = $Mpm->showPluginDetails( $path[$pathPartCount-3] . '/' . $path[$pathPartCount-2] );
			
			
		} elseif ( $_REQUEST['mode'] === 'source' && $_REQUEST['file'] != '' ) {
			$center = '<h1>' . $path[$pathPartCount-1] . '</h1>';
			$center .= $Mpm->highlight( file_get_contents( $_REQUEST['file'] ) );
			
		
		} elseif ( $_REQUEST['mode'] === 'admin_general' ) {
			$center .= '<div>
				<h2>Maintenance</h2>
					<a href="?mode=indexing">Recreate Search Index</a> <span class="note">This will complete erase your current search index (for Docs and Demos) and recreate it. (Might take some time)</span><br />
					<a href="?mode=clearCache">clear cache</a> <span class="note">This will clear the cache in ' . $MprOptions['cachePath'] . '.</span>
				</div>';
			$center .= '<div><h2>Install</h2><span class="note" style="display: block; margin-top: -15px; margin-bottom: 15px;">Once you installed a new Plugin you might want to update the Search index to find stuff from the new Plugin (if it has a Docu or Demos)</span>';
			
			$files = Helper::getFiles( $MpmOptions['zipPath'], 'files', 0);
			// remove an index.html file if found
			unset($files[ array_search('index.html', $files) ]);
			$install = ''; $restore = '';
			foreach( $files as $file ) {
				$fileInfo = explode('^', $file);
				if( !is_dir($Mpm->options->path . $fileInfo[0] . '/' . basename($fileInfo[1], '.zip')) )
					$install .= '<tr><td><a href="?mode=install&amp;file=' . $MpmOptions['zipPath'] . $file . '"><span>install</span></a></td><td>' . basename($fileInfo[1], '.zip') . '</td><td>' . $fileInfo[0] . '</td></tr>';
				else
					$restore .= '<tr><td><a href="?mode=restore&amp;file=' . $MpmOptions['zipPath'] . $file . '"><span>restore</span></a></td><td>' . basename($fileInfo[1], '.zip') . '</td><td>' . $fileInfo[0] . '</td></tr>';
			}
			if ($install !== '')
				$center .= Helper::wrap($install, '<table><tr><th>Action</th><th>Name</th><th>Category</th></tr>|</table>');
			else
				$center .= '<p class="notice">no Plugins to install; if you want to install a Plugin pls copy the zip file into the directory "' . $MpmOptions['zipPath'] . '". This can also just mean that you have all available Plugins installed.</p>';
			
		 $center .= '</div><div><h2>Restore</h2> <span class="note" style="display: block; margin-top: -15px; margin-bottom: 15px;">This will override the Plugin to the saved zip state (files are created every time you extract a plugin; or you can manually copy them into "' . $zipPath . '")</span>';
			if ($restore !== '')
				$center .= Helper::wrap($restore, '<table><tr><th>Action</th><th>Name</th><th>Category</th></tr>|</table>');
			else
				$center .= '<p class="notice">no Plugins to restore; pls check the directory "' . $MpmOptions['zipPath'] . '" if it contains the needed backupfiles</p>';
			$center .= '</div>';
			$center .= '<div><h2>UnInstall</h2><p class="notice">for uninstalling pls use the Uninstall Option on the left</p></div>';
			
			
		} elseif ( $_REQUEST['mode'] === 'admin_uninstall' ) {
			$files = Helper::getFiles($Mpm->options->path, 'dirs');
			unset( $files['.git'] );

			$center .= '<div><h2>UnInstall</h2>';
			$unInstall = '';
			foreach($files as $category => $subdir) {
				foreach( $subdir as $dir => $empty ) {
					$unInstall .= '<tr><td><a href="?mode=uninstall&amp;file=' . $category . '/' . $dir . '">uninstall</a></td><td>' . $dir . '</td><td>' . $category . '</td></tr>';
				}
			}
			
			if( $unInstall !== '' )
				$center .= Helper::wrap($unInstall, '<table><tr><th>Action</th><th>Name</th><th>Category</th></tr>|</table>');
			else
				$center .= '<p class="notice">nothing to UnInstall?</p>';
			$center .= '</div>';
		}
	
	} //if( isset($_REQUEST['mode']) ) {
	
	require_once 'Classes/class.Mpr.php';
	$localMPR = new MPR( $MprOptions );
	$scriptTag = $localMPR->getScriptTagInlineCss(
		file_get_contents( 'Resources/js/Mpm.js' ) . PHP_EOL . 
		$js
	);

?>

<!DOCTYPE html 
	PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de" lang="de">
	<head>
		<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
		<link rel="stylesheet" href="Resources/css/screen.css" type="text/css" media="screen" />

		<!--[if IE 7]> 
			<link rel="stylesheet" href="Resources/css/screen_ie7.css" type="text/css" media="screen" />
		<![endif]-->

		<!--[if lte IE 6]>
			<link rel="stylesheet" href="Resources/css/screen_ie6.css" type="text/css" media="screen" />
		<![endif]-->
		
		<title><?php if(isset($_REQUEST['mode'])) echo $path[$pathPartCount-3] . ' ' . ucfirst($_REQUEST['mode']) . ' - '; ?>Your Local MPR (MooTools Plugin Repository)</title>
		
		<script type="text/javascript">
			var MPR = {};
			MPR.path = '';
			
			MenuPath = '<?php 
				if( isset($path[$pathPartCount-4]) && $path[$pathPartCount-4] !== 'MPR') 
					echo $path[$pathPartCount-4]; 
				elseif ( isset($path[$pathPartCount-3]) )
					echo $path[$pathPartCount-3]; 
			?>';
		</script>
		
		<?php
			echo $scriptTag;
		?>
		
		<?php echo $header; ?>
		
		<script type="text/javascript" src="Resources/js/Mpm.js"></script>
		
	</head>
	<body>
	
		<div id="wrap">
		
			<form action="" method="get" id="searchForm">
				<div id="header">
					<h2 style="border: none; margin-bottom: 10px;"><a href="./Mpm.php">Your Local <acronym title="MooTools Package Repository">MPR</acronym></a></h2>
					<div id="search">
						<input type="text" name="query" id="searchInput" />
						<input type="hidden" name="mode" value="search" />
						<div id="searchResult">
							<h3><a href="Mpm.php?mode=doc&amp;file=./Core/Element.Style/Doc/Element.Style.md">Core / Element.Style</a></h3>
							<p>Custom Native to allow all of its methods to be used with any DOM element via the dollar function $....</p>
						</div>
					</div>
				</div>
			</form>
			
			<div class="colmask equal px240x720">
				<div class="col1">
					<div class="content" id="menu">
						<div>
							<h4>Admin<span class="right"></span></h4>
							<div class="accordionContent">
								<div>
									<p><a href="?mode=admin_general"><span>General</span></a></p>
									<p><a href="?mode=admin_uninstall"><span>UnInstall</span></a></p>
									<span class="leftBottom"/>
								</div>
							</div>
						</div>
							
						<?php echo $left; ?>
					</div>
				</div>
				<div class="col2">
					<div class="content" id="contentMain">
						<?php echo $center; ?>
					</div>
				</div>
			</div>
			
			<div id="footer">
				This documentation is released under a <a href="http://creativecommons.org/licenses/by-nc-sa/3.0/">Attribution-NonCommercial-ShareAlike 3.0</a> License. 
			</div>
			
		</div> <!-- /wrap -->
		
	</body>
</html>





