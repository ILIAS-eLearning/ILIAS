<?php

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

        $this->assertEquals('comment\n', (string) $comment);
    }
}
