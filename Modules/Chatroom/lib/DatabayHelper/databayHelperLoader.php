<?php

if( defined( 'DATABAY_HELPER_LOADED' ) )
{
	return;
}

define( 'DATABAY_HELPER_LOADED', TRUE );

function databay_helper_autoloader($name)
{
	if( substr( strtolower( $name ), 0, 6 ) != 'ildbay' )
		return;

	$basepath = dirname( __FILE__ ) . '/';

	if( file_exists( $path = ($basepath . 'classes/class.' . $name . '.php') ) )
	{
		require_once $path;
	}
}

spl_autoload_register( 'databay_helper_autoloader' );