<?php

require_once 'class.Options.php';

/**
 * it allows you to easily find out what MooTools file you need an creates a costum version for you.
 *
 * @version $Id:
 * @copyright Copyright belongs to the respective authors
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class MPR extends Options {

	public $options = array(
		'base' => '',
		'pathToMpr' => '../mpr/',
		'pathPreFix' => '',
		'exclude' => array('mprjs.php', 'jsspec.js', 'jquery', 'diffmatchpatch.js', 'mprfullcore.js'),
		'cssMprIsUsed' => true,
		'externalFiles' => true,
		'cache' => true,
		'cachePath' => 'Data/MprCache/',
		'jsMinPath' => 'class.JsMin.php',
		'compressJs' => 'minify', //[none, minify]
		'compressCss' => 'minify' //[none, minify]
	);
	
	/**
	 * A array for simple caching
	 *
	 * @var array
	 */
	protected $cache = array();

	public function __construct($options = null) {
		$this->setOptions($options);
		$this->options->cachePath = $this->options->cachePath;
	}
	
	/**
	 * returns the full js and css code with script tag; either as inline js or as a src to an external file (also cache)
	 *
	 * @param string $url
	 * @return string
	 * @author Thomas Allmer <at@delusionworld.com>
	 */	
	public function getScriptTagInlineCss($text) {
		$code = $this->prepareContent($text, 'jsInlineCss', &$name);
		if ($code === '') return false;

		if( $this->options->externalFiles === true )
			return '<script type="text/javascript" src="' . $this->options->cachePath . 'jsInlineCss/' . $name . '"></script>';

		return '
			<script type="text/javascript"><!--
				' . $code .	'
			--></script>';
	}

	/**
	 * finds all need files and save them in an array['js'] and array['css']
	 *
	 * @param string/array $scripts either string or an array where to look for $require
	 * @return array
	 * @author Thomas Allmer <at@delusionworld.com>
	 */
	public function getFileList($scripts, $mode = 'loadRequire') {
		$regularExpressionRequire = '#\$require\(\'(.*?)\'\)#';
		
		$fileList = array('js' => array(), 'css' => array());

		$scripts = array($scripts);
		for ($i = 0; $i < count($scripts); $i++) {
			preg_match_all($regularExpressionRequire, $scripts[$i], $results, PREG_SET_ORDER);
			$results = array_reverse($results);
			foreach($results as $result) {
				//$result = preg_replace( array( '#\s*?MPR\.path\s*?\+\s*#', '#\'#' ), array( $this->options->pathToMpr, '' ), $result[1]);	// MPR.path + '[...]'
				$result = $result[1];
				$resultInfo = pathinfo($result);
				if ( $resultInfo['extension'] === 'js' ) {
					if( $mode == 'loadRequire' )
						$scripts[] = $this->loadUrl( $this->options->pathPreFix . $this->options->pathToMpr . $result);
					$fileList['js'][] = $result;
				}

				if ( $resultInfo['extension'] === 'css' )
					$fileList['css'][] = $result;
			}
		}
		
		$fileList['js'] = array_unique( array_reverse($fileList['js']) );
		$fileList['css'] = array_unique( array_reverse($fileList['css']) );
		
		return $fileList;
	}	
	
	/**
	 * before we return anything we want to do some common workup.
	 *
	 * @param string $input
	 * @return void
	 * @author Thomas Allmer <at@delusionworld.com>
	 */
	private function prepareContent($jsCode, $what = 'jsInlineCss', &$name = null) {
		if( !is_dir($this->options->pathPreFix . $this->options->pathToMpr) ) {
			$regularExpressionsMprPath = '#MPR\.path\s*=\s*["|\'](.*)["|\']\s*;#';
			preg_match( $regularExpressionsMprPath, $jsCode, $match );
			if( count($match) )
				$this->options->pathToMpr = $match[1];
		}
		
		if( $this->options->externalFiles === true ) {
			$siteRequire = $this->getFileList( $jsCode, 'noLoad' );
			$requireString = ($what === 'js') ? implode(' ', $siteRequire['js']) : implode(' ', $siteRequire['js']) . ' ' . implode(' ', $siteRequire['css']);
			$name = md5( $requireString );
			$name .= ($what !== 'css') ? '.js' : '.css';
		
			//if a cache is found for these required files it's returned
			if( is_file($this->options->cachePath . $what . '/' . $name) && $this->options->cache === true )
				return file_get_contents($this->options->cachePath . $what . '/' . $name);
		}
		
		$fileList = $this->getFileList( $jsCode );
		$content = '';
		$js = (count($fileList['js']) != 0 AND count($fileList['css'] != 0) )  ? file_get_contents( $this->options->pathPreFix . $this->options->pathToMpr . 'Tools/Mpr/Mpr.js') : '';
		if ($what === 'js' || $what === 'jsInlineCss') {
			if ($this->options->cssMprIsUsed === true)
				foreach($fileList['css'] as $file)
					$js .= 'MPR.files[MPR.path + \'' . $file . '\'] = 1;' . PHP_EOL;
				
			foreach( $fileList['js'] as $file ) {
				if( is_file($this->options->pathPreFix . $this->options->pathToMpr . $file) ) {
					$js .= file_get_contents($this->options->pathPreFix . $this->options->pathToMpr . $file) . PHP_EOL;
					$js .= 'MPR.files[MPR.path + \'' . $file . '\'] = 1;' . PHP_EOL;
				} else
					$js .= 'alert("The file ' . $file . ' couldn\'t loaded!");';
			}
			if ( $this->options->compressJs === 'minify' ) {
				require_once $this->options->jsMinPath;
				$js = JsMin::minify($js);
			}
			$content .= $js;
		}
		
		if($content !== '' && $what === 'jsInlineCss') {
			$content .= file_get_contents( $this->options->pathPreFix . $this->options->pathToMpr . 'Tools/Browser.Styles/Browser.Styles.js');
		}
		
		$css = '';
		if ($what === 'css' || $what === 'jsInlineCss') {
			foreach( $fileList['css'] as $file ) {
				$raw = file_get_contents($this->options->pathPreFix . $this->options->pathToMpr . $file);
				$raw = preg_replace("#url\s*?\('*(.*?)'*\)#", "url('" . $this->options->pathToMpr . dirname($file) . "/$1')", $raw); //prepend local files
				$css .= $raw . PHP_EOL;
			}
			if ( $this->options->compressCss === 'minify' ) {
				require_once 'class.CssMin.php';
				$css = CssMin::minify($css);
			}
			if ($what === 'jsInlineCss' && $css !== '')
				$content .= PHP_EOL . 'Browser.styles(\'' . addslashes($css) . '\');';
			else
				$content .= $css;
		}
		
		if( $this->options->externalFiles === true ) {
			//save cache
			
			if( !is_dir($this->options->pathPreFix . $this->options->cachePath) )
				mkdir( $this->options->pathPreFix . $this->options->cachePath, 0777, true );
			if( !is_dir($this->options->pathPreFix . $this->options->cachePath . $what . '/') )
				mkdir( $this->options->pathPreFix . $this->options->cachePath . $what . '/', 0777, true );
			file_put_contents( $this->options->pathPreFix . $this->options->cachePath . $what . '/' . $name, $content );
		}
		
		return $content;
	}
	
	/**
	 * returns the full js code you need
	 *
	 * @param string $url
	 * @return string
	 * @author Thomas Allmer <at@delusionworld.com>
	 */
	public function getScript($url) {
		return $this->prepareContent($url, 'js');
	}
	
	//alias for $this->getScript()
	public function getJs($url) { return $this->getScript($url); }
	
	/**
	 * returns the full js code for a given url
	 *
	 * @param string $url - the url you want to get the requirements from
	 * @return string
	 * @author Thomas Allmer <at@delusionworld.com>
	 */
	public function getJsInlineCss($url) {
		$urlInfo = parse_url($url);
		if ($this->options->base === '')
			$this->options->base = $urlInfo['scheme'] . '://' . $urlInfo['host'] . dirname($urlInfo['path']) . '/';
			
		$siteScript = $this->loadUrl($url);
	
		return $this->prepareContent($siteScript);
	}
	
	/**
	 * returns the full css code you need
	 *
	 * @param string $url
	 * @return string
	 * @author Thomas Allmer <at@delusionworld.com>
	 */
	public function getCss($url) {
		return $this->prepareContent($url, 'css');
	}
	
	/**
	 * returns the content of a page via curl
	 *
	 * @param string $url
	 * @return string
	 * @author Thomas Allmer <at@delusionworld.com>
	 */
	private function getUrlContent($url) {
		$ch = curl_init();
		curl_setopt ($ch, CURLOPT_URL, $url);
		curl_setopt ($ch, CURLOPT_HEADER, 0);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
		$str = curl_exec ($ch);
		curl_close ($ch);
		return $str;
	}
	
	/**
	 * extracts all script tags from a given url and loads the external script sources
	 *
	 * @param string $text
	 * @return string
	 * @author Thomas Allmer <at@delusionworld.com>
	 */
	public function getSiteScripts($text) {
		$scripts = '';
		$regularExpressionScriptTags = 	'#<script.+?src=["|\'](.+?)["|\']|<script.+?>(.|\s)*?</script>#'; //<script src="[...]" !AND! <script>[...]</script>
		preg_match_all($regularExpressionScriptTags, $text, $results, PREG_SET_ORDER);
		
		foreach($results as $result) {
		  if ( ($result[1] !== '') && ( in_array( basename(strtolower($result[1])), (array) $this->options->exclude ) === false) )
				$scripts .= $this->getUrlContent( $this->options->base . $result[1] ) . PHP_EOL;
			if ( ($result[1] === '') && ($result[0] !== '') )
				$scripts .= $result[0] . PHP_EOL;
		}
		
		return $scripts;
	}
	
	/**
	 * DESCRIPTION
	 *
	 * @param string $url
	 * @return string
	 * @author Thomas Allmer <at@delusionworld.com>
	 */
	private function loadUrl($url) {
		if( isset($this->cache[$url]) )
			return $this->cache[$url];
			
		$urlInfo = parse_url($url);
		$pathinfo = pathinfo( $urlInfo['path'] );
		$isSiteScript = true;
		if( isset($pathinfo['extension']) )
			$isSiteScript = ( ($pathinfo['extension'] === 'js') || ($pathinfo['extension'] === 'css') ) ? false : true;
		
		$scripts = '';
		if (!$isSiteScript) {
			if ( isset($urlInfo['scheme']) && $urlInfo['scheme'] === 'http')
				$scripts = $this->getUrlContent($url);
			elseif ( is_file($url) )
				$scripts = file_get_contents($url);
		} else {
			$scripts = $this->getSiteScripts( $this->getUrlContent($url) );
		}
		
		$this->cache[$url] = $scripts;
		return $scripts;
	}	
	
}

?>