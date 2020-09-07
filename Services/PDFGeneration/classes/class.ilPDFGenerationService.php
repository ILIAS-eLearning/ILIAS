<?php
include_once("./Services/Component/classes/class.ilService.php");

class ilPDFGenerationService extends ilService
{
    /**
     * @see ilComponent::getVersion()
     */
    public function getVersion()
    {
    }

    /**
     * @see ilComponent::isCore()
     */
    public function isCore()
    {
        return true;
    }
}
