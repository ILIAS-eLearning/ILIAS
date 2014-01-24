<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* This class handles all operations on files in directory data/shop.
* Currently it handles only one image per shop object...
*  
* @author	Michael Jansen <mjansen@databay.de>
* 
*/
require_once './Services/FileSystem/classes/class.ilFileData.php';
				
class ilFileDataShop extends ilFileData
{
	const SHOPPATH = 'shop';
	
	private $pobject_id = 0;
	private $shop_path = '';
	private $image_current = '';
	private $image_new = '';
	private $db = null;

	public function __construct($a_pobject_id)
	{
		global $ilDB;
		
		$this->db = $ilDB;
		$this->pobject_id = $a_pobject_id;
		
		parent::__construct();
		$this->shop_path = ilUtil::getWebspaceDir().'/'.self::SHOPPATH.'/'.$this->pobject_id;		
		$this->initDirectory();
		$this->checkReadWrite();		
		
		$this->__read();
	}
	
	private function __read()
	{
		$result = $this->db->queryf('SELECT image FROM payment_objects WHERE pobject_id = %s',
			array('integer'),array($this->pobject_id));
		
		while($record = $this->db->fetchAssoc($result))
		{
			$this->image_current = $record['image'];
			break;
		}
	}
	
	public function getCurrentImageWebPath()
	{
		if($this->image_current != '' &&
		   $this->checkFilesExist(array($this->image_current)))
		{
			return ilUtil::getWebspaceDir('output').'/'.self::SHOPPATH.'/'.$this->pobject_id.'/'.$this->image_current;
		}
		
		return false;
	}
	
	public function getCurrentImageServerPath()
	{
		if($this->image_current != "" &&
		   $this->checkFilesExist(array($this->image_current)))
		{
			return $this->shop_path.'/'.$this->image_current;
		}
		
		return false;
	}

	private function initDirectory()
	{
		if(is_writable($this->getPath()))
		{
			if(ilUtil::makeDirParents($this->shop_path))
			{
				return true;
			}		 
		}

		return false;
	}
	
	private function checkReadWrite()
	{
		if(is_writable($this->shop_path) && is_readable($this->shop_path))
		{
			return true;
		}
		else
		{
			$this->ilias->raiseError('Shop directory is not readable/writable by webserver', $this->ilias->error_obj->FATAL);
		}
	}
	
	public function storeUploadedFile($a_http_post_file)
	{
		if($this->image_current != '') $this->unlinkFile($this->image_current);
		
		if(isset($a_http_post_file) && $a_http_post_file['size'])
		{			
			if(ilUtil::moveUploadedFile($a_http_post_file['tmp_name'],	$a_http_post_file['name'], 
				$this->shop_path.'/'.$a_http_post_file['name']))
			{
				ilUtil::resizeImage('"'.$this->shop_path.'/'.$a_http_post_file['name'].'"', '"'.$this->shop_path.'/'.$a_http_post_file['name'].'"', 100, 75);
				return $this->image_new = $a_http_post_file['name'];
				
			}			
		}
		
		return false;
	}	
	
	public function assignFileToPaymentObject()
	{		
		$statement = $this->db->manipulateF(
			'UPDATE payment_objects
			 SET
			 image = %s
			 WHERE pobject_id = %s', 
			array('text', 'integer'),
			array($this->image_new, $this->pobject_id));

		
		$this->image_current = $this->image_new;
		
		return true;	
	}
	
	public function deassignFileFromPaymentObject()
	{		
		$statement = $this->db->manipulateF(
			'UPDATE payment_objects
			 SET
			 image = %s
			 WHERE pobject_id = %s', 
			array('text', 'integer'),
			//array('', $this->pobject_id));
			array(NULL, $this->pobject_id));
		
		if($this->image_current != '') $this->unlinkFile($this->image_current);
		ilUtil::delDir($this->shop_path);		
		$this->image_current = '';
		
		return true;	
	}
		

	public function unlinkFile($a_filename)
	{
		if(file_exists($this->shop_path.'/'.$a_filename))
		{
			return unlink($this->shop_path.'/'.$a_filename);
		}
	}
	
	public function checkFilesExist($a_files)
	{
		if($a_files)
		{
			foreach($a_files as $file)
			{
				if(!file_exists($this->shop_path.'/'.$file))
				{
					return false;
				}
			}
			return true;
		}
		return true;
	}
}
?>