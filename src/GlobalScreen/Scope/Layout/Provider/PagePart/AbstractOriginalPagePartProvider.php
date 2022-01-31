<?php namespace ILIAS\GlobalScreen\Scope\Layout\Provider\PagePart;

/******************************************************************************
 * This file is part of ILIAS, a powerful learning management system.
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *****************************************************************************/

/**
 * Class AbstractOriginalPagePartProvider
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class AbstractOriginalPagePartProvider
{
    protected PagePartProvider $original;
    
    /**
     * AbstractOriginalPagePartProvider constructor.
     * @param PagePartProvider $original
     */
    public function __construct(PagePartProvider $original)
    {
        $this->original = $original;
    }
}
