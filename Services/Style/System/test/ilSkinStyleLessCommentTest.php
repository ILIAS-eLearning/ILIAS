<?php

include_once("./Services/Style/System/classes/Less/class.ilSystemStyleLessComment.php");

use PHPUnit\Framework\TestCase;

/**
 *
 * @author            Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version           $Id$*
 */
class ilSkinStyleLessCommentTest extends TestCase {

	public function testConstruct() {
		$comment = new ilSystemStyleLessComment("comment");
		$this->assertEquals("comment", $comment->getComment());
	}

	public function testSetters() {
		$comment = new ilSystemStyleLessComment("name", "comment");

		$comment->setComment("newComment");
		$this->assertEquals("newComment", $comment->getComment());
	}

	public function testToString(){
		$comment = new ilSystemStyleLessComment("comment");

		$this->assertEquals("comment\n",(string)$comment);
	}

}
