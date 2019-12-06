<?php


class ilWebDAVMountInstructionsDocumentContentValidator
{
    protected $necessary_text_placeholders = array(
        'WEBFOLDER_TITLE', 'WEBFOLDER_URI_HTTP', 'WEBFOLDER_URI_KONQUEROR', 'WEBFOLDER_URI_NAUTILUS'
    );

    public function checkMountInstructionsContent(string $a_raw_mount_instructions) : bool
    {


        return false;
    }

    /**
     * Checks if necessary placeholders are in the mount instructions
     *
     * @param string $a_raw_mount_instructions
     * @return bool
     */
    public function checkForNecessaryTextPlaceholders(string $a_raw_mount_instructions) : bool
    {
        foreach($this->necessary_text_placeholders as $necessary_text_placeholder)
        {
            if(strstr($a_raw_mount_instructions, "[$necessary_text_placeholder]"))
            {
                return false;
            }
        }

        return false;
    }

    public function checkIfPlaceholderIsBetweenOtherPlaceholders() : bool
    {
        return false;
    }
}