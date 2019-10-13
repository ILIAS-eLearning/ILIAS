<?php declare(strict_types=1);

namespace ILIAS\UI\Component\MainControls;

use ILIAS\Data\URI;
use ILIAS\UI\Component\Button\Shy;
use ILIAS\UI\Component\Component;

/**
 * Interface HeadInfo
 *
 * The interface describes a HeadInfo Component
 *
 * @package ILIAS\UI\Component\MainControls
 */
interface HeadInfo extends Component {

	/**
	 * @return string
	 */
	public function getTitle(): string;


	/**
	 * @param string $info_message
	 *
	 * @return HeadInfo
	 */
	public function withDescription(string $info_message): HeadInfo;


	/**
	 * @return string
	 */
	public function getDescription(): string;


	/**
	 * @param Shy $shy_button
	 *
	 * @return HeadInfo
	 */
	public function withButton(Shy $shy_button): HeadInfo;


	/**
	 * @return Shy
	 */
	public function getButton(): Shy;


	/**
	 * @param bool $is_important
	 *
	 * @return HeadInfo
	 */
	public function withImportance(bool $is_important): HeadInfo;


	/**
	 * @return bool
	 */
	public function isImportant(): bool;
}
