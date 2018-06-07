<?php

/* Copyright (c) 2018 Thomas Famula <famula@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\MessageBox;

use ILIAS\UI\Component as C;

/**
 * Class Factory
 */
class Factory implements C\MessageBox\Factory {
	/**
	 * @inheritdoc
	 */
	public function failure() {
		return new MessageBox(C\MessageBox\MessageBox::FAILURE);
	}

	/**
	 * @inheritdoc
	 */
	public function success() {
		return new MessageBox(C\MessageBox\MessageBox::SUCCESS);
	}

	/**
	 * @inheritdoc
	 */
	public function info() {
		return new MessageBox(C\MessageBox\MessageBox::INFO);
	}

	/**
	 * @inheritdoc
	 */
	public function confirmation() {
		return new MessageBox(C\MessageBox\MessageBox::CONFIRMATION);
	}
}