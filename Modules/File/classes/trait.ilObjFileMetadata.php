<?php

/**
 * Trait ilObjFileMetadata
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
trait ilObjFileMetadata
{
    /**
     * @var bool
     */
    protected $no_meta_data_creation;

    /**
     * The basic properties of a file object are stored in table object_data.
     * This is not sufficient for a file object. Therefore we create additional
     * properties in table file_data.
     * This method has been put into a separate operation, to allow a WebDAV Null resource
     * (class.ilObjNull.php) to become a file object.
     */
    public function createProperties($a_upload = false)
    {
        global $DIC;

        if ($a_upload) {
            return true;
        }

        // New Item
        $default_visibility = ilNewsItem::_getDefaultVisibilityForRefId($_GET['ref_id']);
        if ($default_visibility === "public") {
            ilBlockSetting::_write("news", "public_notifications", 1, 0, $this->getId());
        }

        // log creation
        $this->log->debug("ilObjFile::createProperties, ID: " . $this->getId() . ", Name: "
            . $this->getFileName() . ", Type: " . $this->getFileType() . ", Size: "
            . $this->getFileSize() . ", Mode: " . $this->getMode() . ", Name(Bytes): "
            . implode(":", ilStr::getBytesForString($this->getFileName())));
        $this->log->logStack(ilLogLevel::DEBUG);

        $DIC->database()->insert('file_data', $this->getArrayForDatabase());

        // no meta data handling for file list files
        if ($this->getMode() != self::MODE_FILELIST) {
            $this->createMetaData();
        }
    }

    /**
     * @param bool $a_status
     */
    public function setNoMetaDataCreation($a_status)
    {
        $this->no_meta_data_creation = (bool) $a_status;
    }

    protected function beforeCreateMetaData()
    {
        return !(bool) $this->no_meta_data_creation;
    }

    protected function beforeUpdateMetaData()
    {
        return !(bool) $this->no_meta_data_creation;
    }

    /**
     * create file object meta data
     */
    protected function doCreateMetaData()
    {
        // add technical section with file size and format
        $md_obj = new ilMD($this->getId(), 0, $this->getType());
        $technical = $md_obj->addTechnical();
        $technical->setSize($this->getFileSize());
        $technical->save();
        $format = $technical->addFormat();
        $format->setFormat($this->getFileType());
        $format->save();
        $technical->update();
    }

    protected function beforeMDUpdateListener($a_element)
    {
        // Check file extension
        // Removing the file extension is not allowed
        include_once 'Services/MetaData/classes/class.ilMD.php';
        $md = new ilMD($this->getId(), 0, $this->getType());
        if (!is_object($md_gen = $md->getGeneral())) {
            return false;
        }
        $title = $this->checkFileExtension($this->getFileName(), $md_gen->getTitle());
        $md_gen->setTitle($title);
        $md_gen->update();

        return true;
    }

    protected function doMDUpdateListener($a_element)
    {
        // handling for technical section
        include_once 'Services/MetaData/classes/class.ilMD.php';

        switch ($a_element) {
            case 'Technical':

                // Update Format (size is not stored in db)
                $md = new ilMD($this->getId(), 0, $this->getType());
                if (!is_object($md_technical = $md->getTechnical())) {
                    return false;
                }

                foreach ($md_technical->getFormatIds() as $id) {
                    $md_format = $md_technical->getFormat($id);
                    $this->setFileType($md_format->getFormat());
                    break;
                }

                break;
        }

        return true;
    }

    /**
     * update meta data
     */
    protected function doUpdateMetaData()
    {
        // add technical section with file size and format
        $md_obj = new ilMD($this->getId(), 0, $this->getType());
        if (!is_object($technical = $md_obj->getTechnical())) {
            $technical = $md_obj->addTechnical();
            $technical->save();
        }
        $technical->setSize($this->getFileSize());

        $format_ids = $technical->getFormatIds();
        if (count($format_ids) > 0) {
            $format = $technical->getFormat($format_ids[0]);
            $format->setFormat($this->getFileType());
            $format->update();
        } else {
            $format = $technical->addFormat();
            $format->setFormat($this->getFileType());
            $format->save();
        }
        $technical->update();
    }
}
