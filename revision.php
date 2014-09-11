<?php

/**
 * when this instance of ilias was installed by using svn checkout,
 * this script prints the currently checked out revision
 * 
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id: revision.php 37893 2012-10-26 12:20:47Z bheyser $
 */


function isSvnRevision($revision)
{
	return (bool)preg_match('/^\d+(:\d+)*[MSP]*$/', $revision);
}

$success = false;

echo '<pre>';


// determine global revision with shell_exec() and svnversion command

if( function_exists('shell_exec') && is_callable('shell_exec') )
{
	$revision = trim(shell_exec('svnversion'));
	
	if( isSvnRevision($revision) )
	{
		$success = true;
		echo "global revision:\t$revision\n";
	}
}


// determine global revision with parsing of svn file

$filename = '.svn/entries';

if( file_exists($filename) && is_file($filename) && is_readable($filename) )
{
	$svnfile = file($filename);
	$revision = $svnfile[3];
	
	if( isSvnRevision($revision) )
	{
		$success = true;
		
		echo "revision of root:\t$revision\n";
	}
}	


// print error if required

if( !$success )
{
	echo "Could not determine current svn revision!\n";
}


echo '</pre>';
