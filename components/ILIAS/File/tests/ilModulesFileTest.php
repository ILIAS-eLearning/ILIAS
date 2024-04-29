<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\DI\Container;
use ILIAS\ResourceStorage\Services;
use ILIAS\FileUpload\FileUpload;
use ILIAS\ResourceStorage\Manager\Manager;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Revision\FileRevision;
use ILIAS\ResourceStorage\Resource\StorableFileResource;

class ilModulesFileTest extends TestCase
{
    private ?\ILIAS\DI\Container $dic_backup = null;
    /**
     * @var Services|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $storage_mock;
    /**
     * @var ilDBInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $db_mock;
    protected $manager_mock;

    protected function setUp(): void
    {
        global $DIC;
        $this->dic_backup = is_object($DIC) ? clone $DIC : null;

        $DIC = new Container();
        $DIC['resource_storage'] = $this->storage_mock = $this->createMock(Services::class);
        $this->manager_mock = $this->createMock(Manager::class);
        $DIC['ilUser'] = $this->createMock(ilObjUser::class);
        $DIC['ilUser']->expects($this->any())->method('getPref')->willReturn('en');
        $DIC['ilDB'] = $this->db_mock = $this->createMock(ilDBInterface::class);
        $DIC['upload'] = $this->createMock(FileUpload::class);
        $DIC['ilias'] = $this->createMock(ILIAS::class);
        $DIC['objDefinition'] = $this->createMock(ilObjectDefinition::class);
        $DIC['ilLog'] = $this->createMock(ilLogger::class);
        $DIC['ilErr'] = $this->createMock(ilErrorHandling::class);
        $DIC['tree'] = $this->createMock(ilTree::class);
        $DIC['tpl'] = $this->createMock(ilGlobalTemplateInterface::class);
        $DIC['ilClientIniFile'] = $this->createMock(ilIniFile::class);
        $DIC['ilAccess'] = $this->createMock(ilAccess::class);
        $DIC['ilObjDataCache'] = $this->createMock(ilObjectDataCache::class);
        $DIC['ilAppEventHandler'] = $this->createMock(ilAppEventHandler::class);
        $DIC['lng'] = $this->createMock(ilLanguage::class);
        $DIC['ilCtrl'] = $this->createMock(ilCtrlInterface::class);
        $DIC['refinery'] = $this->createMock(\ILIAS\Refinery\Factory::class);
        $DIC['http'] = $this->createMock(\ILIAS\HTTP\Services::class);
        $DIC['object.customicons.factory'] = $this->createMock(ilObjectCustomIconFactory::class);
        /*  $DIC['ilCtrl'] = $this->getMockBuilder(ilCtrl::class)
                                ->disableOriginalConstructor()
                                ->disableArgumentCloning()
                                ->getMock();*/

        if (!defined('ILIAS_LOG_ENABLED')) {
            define('ILIAS_LOG_ENABLED', false);
        }
    }

    protected function tearDown(): void
    {
        global $DIC;
        $DIC = $this->dic_backup;
    }

    /**
     * @preserveGlobalState disabled
     * @runInSeparateProcess
     */
    public function testAppendStream(): void
    {
        // DB mock
        $title = 'Revision One';
        $file_stream = Streams::ofString('Test Content');

        $this->storage_mock->expects($this->any())
                           ->method('manage')
                           ->willReturn($this->manager_mock);

        $this->db_mock->expects($this->any())
                      ->method('query')
                      ->willReturnCallback(function ($query) {
                          $mock_object = $this->createMock(ilDBStatement::class);
                          $mock_object->expects($this->any())->method('fetchAssoc')->willReturn([$query]);

                          return $mock_object;
                      });

        $this->db_mock->expects($this->any())
                      ->method('fetchAssoc')
                      ->willReturnCallback(function (ilDBStatement $statement): ?array {
                          $row = $statement->fetchAssoc();
                          $query = '';
                          if ($row !== null) {
                              $query = end($row);
                          }
                          if (str_contains($query, 'last_update')) {
                              return [
                                  'last_update' => '',
                                  'create_date' => ''
                              ];
                          }

                          return null;
                      });

        // Create File Object with disabled news notification
        $file = $this->getMockBuilder(ilObjFile::class)
            ->onlyMethods(['update'])
            ->getMock();
        $file->method('update');

        $r = new ReflectionClass(ilObjFile::class);
        $property = $r->getProperty('just_notified');
        $property->setAccessible(true);
        $property->setValue($file, true);
        $file->setMode(ilObjFile::MODE_FILELIST);
        $this->db_mock->expects($this->any())
                      ->method('fetchAssoc')
                      ->willReturn(
                          [
                              'last_update' => '',
                              'create_date' => ''
                          ]
                      );
        $file->create();

        // identification
        $rid = new ResourceIdentification('the_identification');

        $this->manager_mock->expects($this->any())
                           ->method('find')
                           ->withConsecutive(['-'], ['the_identification'], ['the_identification'])
                           ->willReturnOnConsecutiveCalls(null, $rid, $rid);

        $this->manager_mock->expects($this->once())
                           ->method('stream')
                           ->with($file_stream, new ilObjFileStakeholder(0), $title)
                           ->willReturn($rid);

        $revision = new FileRevision($rid);
        $revision->setVersionNumber(1);
        $revision->setTitle($title);
        $resource = new StorableFileResource($rid);
        $resource->addRevision($revision);

        $this->manager_mock->expects($this->once())
                           ->method('getCurrentRevision')
                           ->with($rid)
                           ->willReturn($revision);

        $this->manager_mock->expects($this->any())
                           ->method('getResource')
                           ->with($rid)
                           ->willReturn($resource);

        $revision_number = $file->appendStream($file_stream, $title);
        $this->assertEquals(1, $revision_number);
        $this->assertEquals(1, $file->getVersion());
        $this->assertEquals($title, $file->getTitle());
    }
}
