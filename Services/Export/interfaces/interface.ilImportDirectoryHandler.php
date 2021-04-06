<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Import directory interface
 *
 * @author	Stefan Meyer <smeyer.ilias@gmx.de>
 * @ingroup	ServicesExport
 */
interface ilImportDirectoryHandler
{
    public function exists() : bool;

    public function getAbsolutePath() : string;
}
