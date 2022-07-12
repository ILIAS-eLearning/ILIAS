<?php declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

use PHPUnit\Framework\TestCase;

class ilServicesFileSystemTest extends TestCase
{
    private ?\ILIAS\DI\Container $dic_backup;
    
    public function testTrailingSlashes() : void
    {
        $file_data = new ilFileData();
        $this->assertEquals('/var/www/ilias', $file_data->deleteTrailingSlash('/var/www/ilias/'));
        $this->assertEquals('\\var\\www\\ilias', $file_data->deleteTrailingSlash('\\var\\www\\ilias\\'));
    }
    
    public function testBaseDirectory() : void
    {
        if (!defined('CLIENT_DATA_DIR')) {
            define('CLIENT_DATA_DIR', '/var/iliasdata');
        }
        $file_data = new ilFileData();
        $this->assertEquals(CLIENT_DATA_DIR, $file_data->getPath());
    }
}
