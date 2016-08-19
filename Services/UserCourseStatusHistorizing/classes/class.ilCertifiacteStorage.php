<?php


class ilCertificateStorage extends ilFileSystemStorage {

	public function __construct($a_crs_id, $a_usr_id, $a_version, $a_path_conversion, $a_container_id) {
		parent::__construct(self::STORAGE_DATA,false,0);
	}

	protected function content($data) {
		$this->data = $data;
	}

	protected function getPathPrefix() {
		return "gevHistorizedCertificates";
	}

	protected function getPathPostfix() {
		return "certificate";
	}
}