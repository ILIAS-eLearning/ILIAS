<?php

/**
 * Interface ilObjFileImplementationInterface
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ilObjFileImplementationInterface
{
    /**
     * @param int $a_version
     * @return string
     * @deprecated
     */
    public function getDirectory($a_version = 0);

    /**
     * @return void
     * @deprecated
     */
    public function createDirectory();

    /**
     * @param $a_upload_file
     * @param $a_filename
     * @return \ILIAS\FileUpload\DTO\UploadResult
     * @throws \ILIAS\FileUpload\Collection\Exception\NoSuchElementException
     * @throws \ILIAS\FileUpload\Exception\IllegalStateException
     * @deprecated
     */
    public function replaceFile($a_upload_file, $a_filename);

    /**
     * @deprecated
     */
    public function clearDataDirectory();

    /**
     * Deletes the specified history entries or all entries if no ids are specified.
     * @param array $a_hist_entry_ids The ids of the entries to delete or null to delete all entries
     * @deprecated
     */
    public function deleteVersions($a_hist_entry_ids = null);

    /**
     * @param string $a_type
     * @deprecated
     */
    public function setFileType($a_type);

    /**
     * @return string
     */
    public function getFileType();

    public function getStorageID() : ?string;

    /**
     * @param $a_size
     * @deprecated
     */
    public function setFileSize($a_size);

    public function getFileSize();

    /**
     * @param null $a_hist_entry_id
     * @return string
     * @deprecated
     */
    public function getFile($a_hist_entry_id = null);

    /**
     * @param $a_version
     * @deprecated
     */
    public function setVersion($a_version);

    public function getVersion();

    /**
     * @param $a_max_version
     * @deprecated
     */
    public function setMaxVersion($a_max_version);

    public function getMaxVersion();

    /**
     * @param null $a_hist_entry_id
     * @return void
     * @deprecated
     */
    public function sendFile($a_hist_entry_id = null);

    /**
     * Returns the extension of the file name converted to lower-case.
     * e.g. returns 'pdf' for 'document.pdf'.
     */
    public function getFileExtension();

    /**
     * @param string $a_upload_file
     * @param string $a_filename
     * @deprecated
     * storeUnzipedFile
     * Stores Files unzipped from uploaded archive in filesystem
     */
    public function storeUnzipedFile($a_upload_file, $a_filename);

    /**
     * Gets the file versions for this object.
     * @param array $version_ids The file versions to get. If not specified all versions are
     *                           returned.
     * @return array The file versions.
     *                           Example:  array (
     *                           'date' => '2019-07-25 11:19:51',
     *                           'user_id' => '6',
     *                           'obj_id' => '287',
     *                           'obj_type' => 'file',
     *                           'action' => 'create',
     *                           'info_params' => 'chicken_outlined.pdf,1,1',
     *                           'user_comment' => '',
     *                           'hist_entry_id' => '3',
     *                           'title' => NULL,
     *                           )
     */
    public function getVersions($version_ids = null) : array;
    
    /**
     * @depracated
     */
    public function export(string $target_dir) : void;
}
