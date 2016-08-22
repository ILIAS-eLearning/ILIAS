<?php
require_once 'Services/FilesSystem/classes/class.ilFileSystemStorage.php';

class ilCertificateStorage extends ilFileSystemStorage {

	public function __construct($a_version = self::STORAGE_DATA, $a_path_conversion = false, $a_container_id = '') {
		parent::__construct($a_version ,$a_path_conversion , $a_container_id);
	}

	protected function getPathPrefix() {
		return "gevHistorizedCertificates";
	}

	protected function getPathPostfix() {
		return "";
	}

	protected function storeCertficate($data, $a_filename) {
		$path = $this->path.'/'.$a_filename;
		if($this->writeToFile($data,$path)) {
			return $path;
		}
		return false;
	}
}