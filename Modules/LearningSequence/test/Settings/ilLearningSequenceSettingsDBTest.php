<?php

declare(strict_types=1);

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

use PHPUnit\Framework\TestCase;

class ilLearningSequenceSettingsDBTest extends TestCase
{
    /**
     * @var ilDBInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $db;

    /**
     * @var ilLearningSequenceFilesystem|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $ls_filesystem;

    protected function setUp(): void
    {
        $this->db = $this->createMock(ilDBInterface::class);
        $this->ls_filesystem = $this
            ->getMockBuilder(ilLearningSequenceFilesystem::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
    }

    public function testCreateObject(): void
    {
        $obj = new ilLearningSequenceSettingsDB($this->db, $this->ls_filesystem);

        $this->assertInstanceOf(ilLearningSequenceSettingsDB::class, $obj);
    }

    public function testStoreWithoutUploadsAndDeletionsAndEmptySettings(): void
    {
        $settings = new ilLearningSequenceSettings(333);

        $where = [
            'obj_id' => ['integer', 333]
        ];

        $values = [
            'abstract' => ['text', ''],
            'extro' => ['text', ''],
            'abstract_image' => ['text', ''],
            'extro_image' => ['text', ''],
            'gallery' => ['integer', false]
        ];

        $this->db
            ->expects($this->once())
            ->method('update')
            ->with('lso_settings', $values, $where)
        ;

        $obj = new ilLearningSequenceSettingsDB($this->db, $this->ls_filesystem);
        $obj->store($settings);
    }

    public function testStoreWithoutUploadsAndDeletionsAndWithSettings(): void
    {
        $settings = new ilLearningSequenceSettings(
            333,
            'abstract',
            'extro',
            'abstract/path',
            'extro_path',
            true
        );

        $where = [
            'obj_id' => ['integer', 333]
        ];

        $values = [
            'abstract' => ['text', 'abstract'],
            'extro' => ['text', 'extro'],
            'abstract_image' => ['text', 'abstract/path'],
            'extro_image' => ['text', 'extro_path'],
            'gallery' => ['integer', true]
        ];

        $this->db
            ->expects($this->once())
            ->method('update')
            ->with('lso_settings', $values, $where)
        ;

        $obj = new ilLearningSequenceSettingsDB($this->db, $this->ls_filesystem);
        $obj->store($settings);
    }

    public function testStoreWithUploadsAndWithoutDeletionsAndWithSettings(): void
    {
        $settings = new ilLearningSequenceSettings(
            333,
            'abstract',
            'extro',
            'abstract/path',
            'extro_path',
            true
        );
        $settings = $settings->withUpload(['upload_info'], 'test');

        $this->ls_filesystem
            ->expects($this->once())
            ->method('moveUploaded')
            ->with('test', ['upload_info'], $settings)
            ->willReturn($settings)
        ;

        $where = [
            'obj_id' => ['integer', 333]
        ];

        $values = [
            'abstract' => ['text', 'abstract'],
            'extro' => ['text', 'extro'],
            'abstract_image' => ['text', 'abstract/path'],
            'extro_image' => ['text', 'extro_path'],
            'gallery' => ['integer', true]
        ];

        $this->db
            ->expects($this->once())
            ->method('update')
            ->with('lso_settings', $values, $where)
        ;

        $obj = new ilLearningSequenceSettingsDB($this->db, $this->ls_filesystem);
        $obj->store($settings);
    }

    public function testStoreWithoutUploadsAndWithDeletionsAndWithSettings(): void
    {
        $settings = new ilLearningSequenceSettings(
            333,
            'abstract',
            'extro',
            'abstract/path',
            'extro_path',
            true
        );
        $settings = $settings->withDeletion('test');

        $this->ls_filesystem
            ->expects($this->once())
            ->method('delete_image')
            ->with('test', $settings)
            ->willReturn($settings)
        ;

        $where = [
            'obj_id' => ['integer', 333]
        ];

        $values = [
            'abstract' => ['text', 'abstract'],
            'extro' => ['text', 'extro'],
            'abstract_image' => ['text', 'abstract/path'],
            'extro_image' => ['text', 'extro_path'],
            'gallery' => ['integer', true]
        ];

        $this->db
            ->expects($this->once())
            ->method('update')
            ->with('lso_settings', $values, $where)
        ;

        $obj = new ilLearningSequenceSettingsDB($this->db, $this->ls_filesystem);
        $obj->store($settings);
    }

    public function testGetSettingsForWithNewObject(): void
    {
        $sql =
            'SELECT abstract, extro, abstract_image, extro_image, gallery' . PHP_EOL
            . 'FROM lso_settings' . PHP_EOL
            . 'WHERE obj_id = 333' . PHP_EOL
        ;

        $values = [
            'obj_id' => ['integer', 333],
            'abstract' => ['text', ''],
            'extro' => ['text', ''],
            'gallery' => ['integer', false]
        ];

        $this->db
            ->expects($this->once())
            ->method('quote')
            ->with(333, 'integer')
            ->willReturn('333')
        ;
        $return_statement = $this->getMockBuilder(ilDBStatement::class)->getMock();
        $this->db
            ->expects($this->once())
            ->method('query')
            ->with($sql)
            ->willReturn($return_statement)
        ;
        $this->db
            ->expects($this->once())
            ->method('numRows')
            ->willReturn(0)
        ;
        $this->db
            ->expects($this->once())
            ->method('insert')
            ->with('lso_settings', $values)
        ;

        $obj = new ilLearningSequenceSettingsDB($this->db, $this->ls_filesystem);
        $result = $obj->getSettingsFor(333);

        $this->assertEquals(333, $result->getObjId());
        $this->assertEquals('', $result->getAbstract());
        $this->assertEquals('', $result->getExtro());
        $this->assertEquals('', $result->getAbstractImage());
        $this->assertEquals('', $result->getExtroImage());
        $this->assertEquals(false, $result->getMembersGallery());
    }

    public function testGetSettingsForWithExistingData(): void
    {
        $sql =
              'SELECT abstract, extro, abstract_image, extro_image, gallery' . PHP_EOL
            . 'FROM lso_settings' . PHP_EOL
            . 'WHERE obj_id = 333' . PHP_EOL
        ;

        $row = [
            'obj_id' => 333,
            'abstract' => 'abstract',
            'extro' => 'extro',
            'abstract_image' => 'abstract_image',
            'extro_image' => 'extro_image',
            'gallery' => true
        ];

        $this->db
            ->expects($this->once())
            ->method('quote')
            ->with(333, 'integer')
            ->willReturn('333')
        ;
        $return_statement = $this->getMockBuilder(ilDBStatement::class)->getMock();
        $this->db
            ->expects($this->once())
            ->method('query')
            ->with($sql)
            ->willReturn($return_statement)
        ;
        $this->db
            ->expects($this->once())
            ->method('numRows')
            ->willReturn(1)
        ;
        $this->db
            ->expects($this->once())
            ->method('fetchAssoc')
            ->with($return_statement)
            ->willReturn($row)
        ;

        $obj = new ilLearningSequenceSettingsDB($this->db, $this->ls_filesystem);
        $result = $obj->getSettingsFor(333);

        $this->assertEquals(333, $result->getObjId());
        $this->assertEquals('abstract', $result->getAbstract());
        $this->assertEquals('extro', $result->getExtro());
        $this->assertEquals('abstract_image', $result->getAbstractImage());
        $this->assertEquals('extro_image', $result->getExtroImage());
        $this->assertEquals(true, $result->getMembersGallery());
    }
}
