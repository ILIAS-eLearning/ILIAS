<?php
interface IndividualAssessmentFileStorage
{
    public function isEmpty();
    public function deleteCurrentFile();
    public function getFilePath();
    public function uploadFile($file);
    public function create();
    /**
     * Set user for path creation
     *
     * @param int 	$user_id
     *
     * @return null
     */
    public function setUserId($user_id);
}
