<?php

if (!function_exists("wfProfileIn"))
{
	function wfProfileIn($dummy)
	{
	}
}
if (!function_exists("wfProfileOut"))
{
	function wfProfileOut($dummy)
	{
	}
}

if (!function_exists('codepointToUtf8'))
{
	// From include/normal/UtfNormalUtil.php
	function codepointToUtf8( $codepoint ) {
		if($codepoint <		0x80) return chr($codepoint);
		if($codepoint <    0x800) return chr($codepoint >>	6 & 0x3f | 0xc0) .
										 chr($codepoint		  & 0x3f | 0x80);
		if($codepoint <  0x10000) return chr($codepoint >> 12 & 0x0f | 0xe0) .
										 chr($codepoint >>	6 & 0x3f | 0x80) .
										 chr($codepoint		  & 0x3f | 0x80);
		if($codepoint < 0x110000) return chr($codepoint >> 18 & 0x07 | 0xf0) .
										 chr($codepoint >> 12 & 0x3f | 0x80) .
										 chr($codepoint >>	6 & 0x3f | 0x80) .
										 chr($codepoint		  & 0x3f | 0x80);
	
		echo "Asked for code outside of range ($codepoint)\n";
		die( -1 );
	}
}

// From includes/normal/UtfNormal.php
define( 'UTF8_REPLACEMENT', "\xef\xbf\xbd" /*codepointToUtf8( UNICODE_REPLACEMENT )*/ );

/**
 * Returns a regular expression of url protocols
 *
 * @return string
 */
function wfUrlProtocols() {
	global $wgUrlProtocols;
	
	$wgUrlProtocols = array(
		'http://',
		'https://',
		'ftp://',
		'irc://',
		'gopher://',
		'telnet://', // Well if we're going to support the above.. -ævar
		'nntp://', // @bug 3808 RFC 1738
		'worldwind://',
		'mailto:',
		'news:'
		);


	// Support old-style $wgUrlProtocols strings, for backwards compatibility
	// with LocalSettings files from 1.5
	if ( is_array( $wgUrlProtocols ) ) {
		$protocols = array();
		foreach ($wgUrlProtocols as $protocol)
			$protocols[] = preg_quote( $protocol, '/' );

		return implode( '|', $protocols );
	} else {
		return $wgUrlProtocols;
	}
}

include_once("./Services/Utilities/classes/Parser.php");
include_once("./Services/Utilities/classes/Sanitizer.php");

class ilMWFakery
{
	function getSkin() { return $this;}
	
	// from Linker.php
	function makeExternalLink( $url, $text, $escape = true, $linktype = '', $ns = null )
	{
		//$style = $this->getExternalLinkAttributes( $url, $text, 'external ' . $linktype );
		//global $wgNoFollowLinks, $wgNoFollowNsExceptions;
		//if( $wgNoFollowLinks && !(isset($ns) && in_array($ns, $wgNoFollowNsExceptions)) ) {
		//	$style .= ' rel="nofollow"';
		//}
		$url = htmlspecialchars( $url );
		if( $escape ) {
			$text = htmlspecialchars( $text );
		}
		
		// handle images
		$urlpath = parse_url($url, PHP_URL_PATH);
		$pi = pathinfo($urlpath);
		if (in_array(strtolower($pi["extension"]), array("jpg", "jpeg", "gif", "png")))
		{
			return '<img src="'.$url.'" border="0" />';
		}
		else
		{
			return '<a href="'.$url.'" target="_blank">'.$text.'</a>';
		}
		//return '<a href="'.$url.'"'.$style.'>'.$text.'</a>';
	}
	
	function markNoConversion($text, $noParse=false) {return $text;}
	
	function addExternalLink() {}
	
	public function getNamespace() { return null;}
}

$GLOBALS["wgContLang"] = new ilMWFakery();

class ilMWParserAdapter extends Parser
{
	function __construct()
	{
		parent::__construct();
		$this->mOptions = new ilMWFakery();
		$this->mTitle = new ilMWFakery();
		$this->mOutput = new ilMWFakery();
	}
	
	function maybeMakeExternalImage( $url ) { return false;}
}

?>
