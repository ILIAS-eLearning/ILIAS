<?php declare(strict_types=1);

require_once(__DIR__ . "/../../../../libs/composer/vendor/autoload.php");

class ilStudyProgrammeGUIMessagesTest extends \PHPUnit\Framework\TestCase
{
    public function setUp() : void
    {
        $collection = new ilPRGMessageCollection();
        $lng = $this->createMock(ilLanguage::class);
        $this->messages = new ilPRGMessagePrinter($collection, $lng);
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
    public function testCollectionDefaults(ilPRGMessageCollection $collection)
    {
        $this->assertEquals($this->topic, $collection->getDescription());

        $this->assertFalse($collection->hasErrors());
        $this->assertEquals([], $collection->getErrors());

        $this->assertFalse($collection->hasSuccess());
        $this->assertEquals([], $collection->getSuccess());
        
        $this->assertFalse($collection->hasAnyMessages());
        return $collection;
    }

    /**
     * @depends testMessageFactory
     */
    public function testAddMessages(ilPRGMessageCollection $collection)
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
    public function testAddErrorMessages(ilPRGMessageCollection $collection) : ilPRGMessageCollection
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
        return $collection;
    }
}
