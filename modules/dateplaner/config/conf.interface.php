<?php
/** Modul Einstellungen für den Terminplaner: / Modul porperties fpr the dateplaner
*	Bitte kommentieren Sie folgende 2 Zeilen aus nachdem sie die Einstellungen vorgenommen haben !
*	please coment out these 2 lines after editing the file 
*/
echo ("Die ist das Terminplaner Modul. <br> Bitte nehmen sie die Einstellunegn in der Konfigurationsdatei vor.<br><b> 'modules/dateplaner/config/conf.interface.php'</b><BR><BR>This is the dateplaner modul <BR> please set the properties in file <br> <B>'modules/dateplaner/config/conf.interface.php'</B>");
exit;

	/**
	* ilias-root-directory (including the whole path beginning from system-root!)
	* @ var string
	* @ access private
	*/
	$iliasRootDir = "/srv/ilias/public_html/ilias";

	/** 
	* relative path of the ilias dir
	* @ var string
	* @ access private
	*/
	$actualIliasDir = "../..";
	
	/** 
	* relative path of the cscw dir
	* @ var string
	* @ access private
	*/
	$relCSCWDir = "";	

	/**
	* name of ilias database
	* @var string
	* @access private
	*/
	$dbaseIlias  = "ilias";
	
	/**
	* name of cscw database
	* @var string
	* @access private
	*/
	$dbaseCscw  = "ilias";
	
	/**
	* hostname
	* @var string
	* @access private
	*/
	$host   = "localhost";
	
	/**
	* username
	* @var string
	* @access private
	*/
	$mysqlUser   = "root";
	
	/**
	* password
	* @var string
	* @access private
	*/
	$mysqlPass   = "";
	
	/**
	* group_ID for all users
	* @var int
	* @access private
	*/
	$allUserId   = '-1';

	/**
	* if the Modul run in a Frame
	* 1 = using Frames (standard in Ilias3 )
	* 0 = not using Frames (standard in Ilias2 )
	* -1 = detect using Frames ( not now implemented in Ilias3 and Ilias2 )
	* @var int
	* @access private
	*/
	$usingFrames   = '1';
?>