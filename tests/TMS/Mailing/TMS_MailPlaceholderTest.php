<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> */

use ILIAS\TMS\Mailing;
use PHPUnit\Framework\TestCase;

class TMS_MailPlaceholderTest extends TestCase {
	public function test_fields() {
		$entity = $this->createMock(CaT\Ente\Entity::class);
		$placeholder = "LABEL";
		$description = "This is a placeholder.";
		$fnc = function() { return; };
		$mail_placeholder = new Mailing\MailPlaceholder($entity, $fnc, $placeholder, $description);

		$this->assertEquals($entity, $mail_placeholder->entity());
		$this->assertEquals($placeholder, $mail_placeholder->getPlaceholder());
		$this->assertEquals($description, $mail_placeholder->getDescription());
	}
}
