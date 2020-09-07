<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Component/classes/class.ilService.php");

/**
 * Preview Service.
 *
 * @author Stefan Born <stefan.born@phzh.ch>
 * @version $Id$
 *
 * @ingroup ServicesPreview
 */
class ilPreviewService extends ilService
{
    /**
     * Constructor: read information on component
     */
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * Core modules vs. plugged in modules
     */
    public function isCore()
    {
        return true;
    }

    /**
     * Get version of service.
     */
    public function getVersion()
    {
        return "-";
    }
}
