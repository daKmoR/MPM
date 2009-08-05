<?php

require_once('class.Options.php');
require_once('class.Helper.php');

/**
 * DESCRIPTION
 *
 * @package MPR
 * @subpackage Controller
 * @version $Id:
 * @copyright Copyright belongs to the respective authors
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class Plugin extends Options {
	
	public $options = array(
		'linkParam' => '',
		'path' => '',
		'name' => '',
		'docu' => '',
		'demo' => '',
		'download' => '',
		'display' => array('name', 'docu', 'spec', 'download'),
		'stdWrap' => '<div>|</div>',
		'itemWrap' => '',
		'version' => '0',
		'dokuItemWrap' => '<li>|</li>',
		'dokuWrap' => '<h2>Available Documentation</h2><ul>|</ul>',
		'demoItemWrap' => '<li>|</li>',
		'demoWrap' => '<h2>Available Demos</h2><ul>|</ul>',
		'sourceWrap' => '<h2>Sourcecode</h2><ul>|</ul>',
		'sourceItemWrap' => '<li>|</li>',
		'pathPreFix'   => ''
	);
	
	/**
	 * DESCRIPTION
	 *
	 * @param string $input
	 * @return void
	 * @author Thomas Allmer <at@delusionworld.com>
	 */
	public function Plugin( $name, $path, $options ) {
		$this->setOptions($options);
		$this->options->name = $name;
		$this->options->path = $path;
		
		$path = explode('/', $path);
		$pathPartCount = count($path);
		$this->options->category = $path[$pathPartCount-2];
	}
	
	/**
	 * DESCRIPTION
	 *
	 * @param string $input
	 * @return void
	 * @author Thomas Allmer <at@delusionworld.com>
	 */
	public function getData() {
		$metaPath = $this->options->path . '/Meta/Plugin.xml';
		$demoPath = $this->options->path . '/Demos/' . $this->options->name . '.html';
		$docuPath = $this->options->path . '/Docu/' . $this->options->name . '.md';
		$specPath = $this->options->path . '/Spec/' . $this->options->name . '.js';
		
		// right now I'm skipping to read the xml file
		// if( is_file($metaPath) )
			// fb( file_get_contents( $metaPath ) );
			
		//$this->options->demo = is_file($demoPath) ? '<a href="?mode=demo&amp;file=' . $demoPath . '"><span>demo</span></a>' : '';
		$this->options->download = '<a class="download" href="?mode=zip&amp;file=' . $this->options->path . '/' . $this->options->linkParam . '"><span>download</span></a>';
		$this->options->docu = is_file($docuPath) ? '<a class="docu" href="?mode=docu&amp;file=' . $docuPath . '' . $this->options->linkParam . '"><span>docu</span></a>' : '';
		$this->options->name = '<a href="?mode=pluginDetails&amp;file=' . $this->options->path . '/' . '' . $this->options->linkParam . '"><span>' . $this->options->name . '</span></a>';
		$this->options->spec = is_file($specPath) ? '<a class="spec" href="?mode=spec&amp;file=' . $specPath . '' . $this->options->linkParam . '"><span>spec</span></a>' : '';
	}
	
	/**
	 * DESCRIPTION
	 *
	 * @param string $input
	 * @return void
	 * @author Thomas Allmer <at@delusionworld.com>
	 */
	public function render() {
		$this->getData();
		
		$content = '';
		
		foreach( $this->options->display as $display )
			$content .= Helper::wrap( $this->options->$display, $this->options->itemWrap );
		
		return Helper::wrap( $content, $this->options->stdWrap );
	}
	
	public function renderDetail() {
		$content = '<h1>' . $this->options->name . '</h1>';
		
		$PluginFiles = Helper::getFiles( $this->options->pathPreFix . $this->options->path );

		if( count($PluginFiles['Demos']) ) {
			$demos = '';
			foreach( $PluginFiles['Demos'] as $demo ) {
				$demoPath = $this->options->path . '/Demos/' . $demo;
				if( is_file($this->options->pathPreFix . $demoPath) ) {
					$demos .= Helper::wrap('<a href="?mode=demo&amp;file=' . $demoPath . '' . $this->options->linkParam . '">' . $demo . '</a>', $this->options->demoItemWrap);
				}
			}
			$content .= Helper::wrap($demos, $this->options->demoWrap);
		}
		
		if( isset($PluginFiles['Docu']) && count($PluginFiles['Docu']) ) {
			foreach( $PluginFiles['Docu'] as $docu ) {
				$docuPath = $this->options->path . '/Docu/' . $docu;
				$docus .= Helper::wrap('<a href="?mode=docu&amp;file=' . $docuPath . '' . $this->options->linkParam . '">' . $docu . '</a>', $this->options->dokuItemWrap);
			}
			$content .= Helper::wrap($docus, $this->options->dokuWrap);
		}
		
		$sources = '';
		foreach( $PluginFiles as $source ) {
			if ( !is_array($source) ) {
				$sources .= Helper::wrap('<a href="?mode=source&amp;file=' . $this->options->path . '/' . $source . '' . $this->options->linkParam . '">' . $source . '</a>', $this->options->sourceItemWrap );
			}
		}
		if( $sources )
			$content .= Helper::wrap($sources, $this->options->sourceWrap);
	
		return $content;
	}
	
}

?>