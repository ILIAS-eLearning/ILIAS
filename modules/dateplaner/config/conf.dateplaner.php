<?php
/** Modul Einstellungen für den Terminplaner: / Modul porperties fpr the dateplaner
*	Bitte kommentieren Sie folgende 2 Zeilen aus nachdem sie die Einstellungen vorgenommen haben !
*	please coment out these 2 lines after editing the file 
*/
/*
echo ("Die ist das Terminplaner Modul. <br> Bitte nehmen sie die Einstellunegn in der Konfigurationsdatei vor.<br><b> 'modules/dateplaner/config/conf.interface.php'</b><BR><BR>This is the dateplaner modul <BR> please set the properties in file <br> <B>'modules/dateplaner/config/conf.interface.php'</B>");
exit;
*/

	/**
	* ilias module directory (up to the ilias root dir)
	* @ var string
	* @ access private
	*/

	$modulDir = "/modules/dateplaner";

	/** 
	* relative path of the ilias root dir
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
	* set "-1" if ilias variables should be used
 	* @var string
	* @access private
	*/
	$dbaseIlias  = "-1";
	
	/**
	* name of cscw database
	* set "-1" if ilias variables should be used
	* @var string
	* @access private
	*/
	$dbaseCscw  = "-1";
	
	/**
	* hostname
	* set "-1" if ilias variables should be used
	* @var string
	* @access private
	*/
	$host   = "-1";
	
	/**
	* username
	* set "-1" if ilias variables should be used
	* @var string
	* @access private
	*/
	$mysqlUser   = "-1";
	
	/**
	* password
	* set "-1" if ilias variables should be used
	* @var string
	* @access private
	*/
	$mysqlPass   = "-1";
	
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