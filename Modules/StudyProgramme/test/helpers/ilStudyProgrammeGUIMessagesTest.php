<?php declare(strict_types=1);

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

class ilStudyProgrammeGUIMessagesTest extends TestCase
{
    protected ilPRGMessagePrinter $messages;
    protected string $topic;

    protected function setUp() : void
    {
        $collection = new ilPRGMessageCollection();
        $lng = $this->createMock(ilLanguage::class);
        $tpl = $this->createMock(ilGlobalTemplateInterface::class);
        $this->messages = new ilPRGMessagePrinter($collection, $lng, $tpl);
        $this->topic = 'a test topic';
    }

    public function testMessageFactory() : ilPRGMessageCollection
    {
        $collection = $this->messages->getMessageCollection($this->topic);
        $collection2 = $this->messages->getMessageCollection($this->topic);
        
        $this->assertInstanceOf(ilPRGMessageCollection::class, $collection);
        $this->assertEquals($collection, $collection2);
        $this->assertNotSame($collection, $collection2);

        return $collection;
    }
    
    /**
     * @depends testMessageFactory
     */
    public function testCollectionDefaults(ilPRGMessageCollection $collection) : void
    {
        $this->assertEquals($this->topic, $collection->getDescription());

        $this->assertFalse($collection->hasErrors());
        $this->assertEquals([], $collection->getErrors());

        $this->assertFalse($collection->hasSuccess());
        $this->assertEquals([], $collection->getSuccess());
        
        $this->assertFalse($collection->hasAnyMessages());
    }

    /**
     * @depends testMessageFactory
     */
    public function testAddMessages(ilPRGMessageCollection $collection) : void
    {
        $ok_message = 'looks good';
        $ok_id = 'some good record';
        $collection->add(true, $ok_message, $ok_id);

        $this->assertTrue($collection->hasAnyMessages());
        
        $this->assertFalse($collection->hasErrors());
        $this->assertEquals([], $collection->getErrors());

        $this->assertTrue($collection->hasSuccess());
        $this->assertEquals(
            [[$ok_message, $ok_id]],
            $collection->getSuccess()
        );
    }

    /**
     * @depends testMessageFactory
     */
    public function testAddErrorMessages(ilPRGMessageCollection $collection) : void
    {
        $message = 'looks bad';
        $id = 'some record';

        $this->assertTrue($collection->hasAnyMessages());
        $collection = $collection->withNewTopic($this->topic);
        $this->assertFalse($collection->hasAnyMessages());

        $collection->add(false, $message, $id);
        $collection->add(false, $message, $id);
        
        $this->assertTrue($collection->hasAnyMessages());

        $this->assertTrue($collection->hasErrors());
        $this->assertEquals(
            [[$message, $id],[$message, $id]],
            $collection->getErrors()
        );
        
        $this->assertEquals([], $collection->getSuccess());
        $this->assertFalse($collection->hasSuccess());
    }
}
