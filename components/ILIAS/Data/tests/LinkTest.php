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

use ILIAS\Data\Link;
use PHPUnit\Framework\TestCase;

/**
 * Tests the Link Datatype
 */
class DataLinkTest extends TestCase
{
    private \ILIAS\Data\Factory $f;
    private string $label;
    private \ILIAS\Data\URI $url;

    protected function setUp(): void
    {
        $this->f = new ILIAS\Data\Factory();
        $this->label = 'ILIAS Homepage';
        $this->url = $this->f->uri('https://www.ilias.de');
    }

    public function testFactory(): Link
    {
        $link = $this->f->link($this->label, $this->url);
        $this->assertInstanceOf(Link::class, $link);
        return $link;
    }

    /**
     * @depends testFactory
     */
    public function testValues(Link $link): void
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
