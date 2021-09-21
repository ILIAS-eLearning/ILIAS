<?php declare(strict_types=1);

use ILIAS\Data\Link;
use PHPUnit\Framework\TestCase;

/**
 * Tests the Link Datatype
 */
class DataLinkTest extends TestCase
{
    protected function setUp() : void
    {
        $this->f = new ILIAS\Data\Factory();
        $this->label = 'ILIAS Homepage';
        $this->url = $this->f->uri('https://www.ilias.de');
    }
    
    public function testFactory() : Link
    {
        $link = $this->f->link($this->label, $this->url);
        $this->assertInstanceOf(Link::class, $link);
        return $link;
    }

    /**
     * @depends testFactory
     */
    public function testValues(Link $link)
    {
        $this->assertEquals(
            $this->label,
            $link->getLabel()
        );
        $this->assertEquals(
            $this->url,
            $link->getUrl()
        );
    }
}
