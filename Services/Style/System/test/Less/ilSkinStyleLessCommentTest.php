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

require_once('libs/composer/vendor/autoload.php');

use PHPUnit\Framework\TestCase;

class ilSkinStyleLessCommentTest extends TestCase
{
    public function testConstruct(): void
    {
        $comment = new ilSystemStyleLessComment('comment');
        $this->assertEquals('comment', $comment->getComment());
    }

    public function testSetters(): void
    {
        $comment = new ilSystemStyleLessComment('comment');

        $comment->setComment('newComment');
        $this->assertEquals('newComment', $comment->getComment());
    }

    public function testToString(): void
    {
        $comment = new ilSystemStyleLessComment('comment');

        $this->assertEquals("comment\n", (string) $comment);
    }
}
