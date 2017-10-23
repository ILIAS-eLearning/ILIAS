<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> */

use ILIAS\TMS\Mailing;
use PHPUnit\Framework\TestCase;

class TMS_MailPlaceholderValueTest extends TestCase {
	public function test_fields() {
		$entity = $this->createMock(CaT\Ente\Entity::class);
		$placeholder = "LABEL";
		$value = "I will replace the placeholder.";
		$mail_placeholder_value = new Mailing\MailPlaceholderValue($entity, $placeholder, $value);

		$this->assertEquals($entity, $mail_placeholder_value->entity());
		$this->assertEquals($placeholder, $mail_placeholder_value->getPlaceholder());
		$this->assertEquals($value, $mail_placeholder_value->getValue());
	}
}
