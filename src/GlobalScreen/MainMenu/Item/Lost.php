<?php namespace ILIAS\GlobalScreen\MainMenu\Item;

use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Identification\NullIdentification;
use ILIAS\GlobalScreen\MainMenu\AbstractBaseItem;
use ILIAS\GlobalScreen\MainMenu\hasAction;
use ILIAS\GlobalScreen\MainMenu\hasAsyncContent;
use ILIAS\GlobalScreen\MainMenu\hasContent;
use ILIAS\GlobalScreen\MainMenu\hasTitle;
use ILIAS\GlobalScreen\MainMenu\isChild;
use ILIAS\GlobalScreen\MainMenu\isItem;
use ILIAS\GlobalScreen\MainMenu\isParent;
use ILIAS\GlobalScreen\MainMenu\isTopItem;
use ILIAS\UI\Component\Component;

/**
 * Class Lost
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class Lost extends AbstractBaseItem implements hasAsyncContent, hasContent, isTopItem, isParent, isChild, hasTitle, hasAction {

	/**
	 * @var isChild[]
	 */
	private $children = array();
	/**
	 * @var IdentificationInterface
	 */
	private $parent;
	/**
	 * @var string
	 */
	private $title = "";


	/**
	 * @inheritDoc
	 */
	public function __construct(IdentificationInterface $provider_identification) {
		parent::__construct($provider_identification);
		$this->parent = new NullIdentification();
	}


	/**
	 * @inheritDoc
	 */
	public function withTitle(string $title): hasTitle {
		$this->title = $title;

		return $this;
	}


	/**
	 * @inheritDoc
	 */
	public function getTitle(): string {
		return $this->title;
	}


	/**
	 * @inheritDoc
	 */
	public function getAsyncContentURL(): string {
		return "";
	}


	/**
	 * @inheritDoc
	 */
	public function withAsyncContentURL(string $async_content_url): hasAsyncContent {
		return $this;
	}


	/**
	 * @inheritDoc
	 */
	public function withContent(Component $ui_component): hasContent {
		return $this;
	}


	/**
	 * @inheritDoc
	 */
	public function getContent(): Component {
		global $DIC;

		return $DIC->ui()->factory()->legacy("");
	}


	/**
	 * @inheritDoc
	 */
	public function withParent(IdentificationInterface $identification): isItem {
		$this->parent = $identification;

		return $this;
	}


	/**
	 * @inheritDoc
	 */
	public function hasParent(): bool {
		return $this->parent instanceof isParent;
	}


	/**
	 * @inheritDoc
	 */
	public function getParent(): IdentificationInterface {
		return $this->parent;
	}


	/**
	 * @inheritDoc
	 */
	public function overrideParent(IdentificationInterface $identification): isChild {
		$this->parent = $identification;

		return $this;
	}


	/**
	 * @inheritDoc
	 */
	public function getChildren(): array {
		return $this->children;
	}


	/**
	 * @inheritDoc
	 */
	public function withChildren(array $children): isParent {
		$this->children = $children;

		return $this;
	}


	/**
	 * @inheritDoc
	 */
	public function appendChild(isChild $child): isParent {
		$this->children[] = $child;

		return $this;
	}


	/**
	 * @inheritDoc
	 */
	public function hasChildren(): bool {
		return count($this->children) > 0;
	}


	/**
	 * @inheritDoc
	 */
	public function withAction(string $action): hasAction {
		// noting to to
		return $this;
	}


	/**
	 * @inheritDoc
	 */
	public function getAction(): string {
		return "#";
	}


	/**
	 * @inheritDoc
	 */
	public function withIsLinkToExternalAction(bool $is_external): hasAction {
		// noting to to
		return $this;
	}


	/**
	 * @inheritDoc
	 */
	public function isLinkWithExternalAction(): bool {
		return false;
	}
}
