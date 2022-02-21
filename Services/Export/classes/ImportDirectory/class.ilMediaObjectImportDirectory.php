<?php declare(strict_types=1);

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\Filesystem\Filesystem;

/**
 * Import directory interface
 * @author     Stefan Meyer <smeyer.ilias@gmx.de>
 * @ingroup    ServicesExport
 */
class ilMediaObjectImportDirectory extends ilImportDirectory
{
    private const PATH_PREFIX = 'mob';

    protected function getPathPrefix() : string
    {
        return self::PATH_PREFIX;
    }
}
