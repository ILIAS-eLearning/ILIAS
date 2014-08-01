<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class gevBillStorage
*
* Used to store finalized bills as pdfs for revision safety.
*
* @author Richard Klees <richard.klees@concepts-and-training>
*/

require_once("Services/Billing/classes/class.ilBill.php");
require_once("Services/FileSystem/classes/class.ilFileSystemStorage.php");

class gevBillStorage extends ilFileSystemStorage {
	static $instance = null;

	/**
	 * Construct the storage for the mail log.
	 *
	 * @param integer $a_obj_id The id of the object the storage is responsible for.
	 */
	public function __construct() {
		parent::__construct(self::STORAGE_DATA, false, date("Y"));
		$this->create();
	}
	
	public static function getInstance() {
		if (self::$instance === null) {
			self::$instance = new gevBillStorage();
		}
		return self::$instance;
	}
	
	/**
	 * Add a bill to the storage.
	 * Will store bill with its bill number. Won't store a bill a second time.
	 **/
	public function storeBill(ilBill $a_bill) {
		require_once("Services/GEV/Utils/classes/class.gevPDFBill.php");
		$path = $this->getPath($a_bill);
		$pdf_bill = gevPDFBill::getInstance();
		$pdf_bill->setBill($a_bill);
		$pdf_bill->build($path);
	}

	public function getPath(ilBill $a_bill) {
		return $this->getAbsolutePath()."/".$a_bill->getBillNumber();
	}

	/**
	 * Add a file to the storage.
	 * Will calculate hash of the file to store similar files only once.
	 *
	 * @param string $a_filepath The path to the file to be stored.
	 * @return string The hash for the file.
	 */
	public function addFile($a_filepath) {
		if(!@file_exists($a_filepath)) {
			throw new Exception("Can't store file ".$a_filepath.". It does not exist.");
		}

		$hash = hash_file("md5", $a_filepath);

		$this->create();
		$this->copyFile($a_filepath, $this->getAbsolutePath()."/".$hash);

		return $hash;
	}

	// Implemented for ilFileSystemStorage
	protected function getPathPostfix() {
		return "bill";
	}

	protected function getPathPrefix() {
		return "gevBillStorage";
	}
}

?>