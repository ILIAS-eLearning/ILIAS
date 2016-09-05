<?php
require_once 'Services/FileSystem/classes/class.ilFileSystemStorage.php';

class ilCertificateStorage extends ilFileSystemStorage {

	const CERTIFICATE_PREFIX = "gevHistorizedCertificates";
	const CERTIFICATE_POSTFIX = 'stored';
	protected static $instance;

	public function getInstance() {
		if(!self::$instance) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	public function __construct($a_version = self::STORAGE_DATA, $a_path_conversion = false, $a_container_id = 'certs') {
		parent::__construct($a_version ,$a_path_conversion , $a_container_id);
		$this->create();
	}

	protected function getPathPrefix() {
		return self::CERTIFICATE_PREFIX;
	}

	protected function getPathPostfix() {
		return self::CERTIFICATE_POSTFIX;
	}

	public function storeCertificate($data, $a_filename) {
		$path = $this->path.'/'.$a_filename;
		if($this->writeToFile($data,$path)) {
			return $path;
		}
		return false;
	}

	public function deliverCertificate($a_filename) {
		return ilUtil::deliverFile( $this->path.'/'.$a_filename,$a_filename, "application/pdf");
	}
}