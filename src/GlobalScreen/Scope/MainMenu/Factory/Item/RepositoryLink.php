<?php namespace ILIAS\GlobalScreen\Scope\MainMenu\Factory\Item;

use ILIAS\GlobalScreen\Scope\MainMenu\Factory\AbstractChildItem;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasAction;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasTitle;
use ilLink;
use ilObject2;

/**
 * Class Link
 *
 * Attention: This is not the same as the \ILIAS\UI\Component\Link\Link. Please
 * read the difference between GlobalScreen and UI in the README.md of the GlobalScreen Service.
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class RepositoryLink extends AbstractChildItem implements hasTitle, hasAction
{

    /**
     * @var int
     */
    protected $ref_id = 0;
    /**
     * @var string
     */
    protected $alt_text;
    /**
     * @var string
     */
    protected $title;


    /**
     * @param string $title
     *
     * @return RepositoryLink
     */
    public function withTitle(string $title) : hasTitle
    {
        $clone = clone($this);
        $clone->title = $title;

        return $clone;
    }


    /**
     * @return string
     */
    public function getTitle() : string
    {
        return $this->title !== null ? $this->title : ($this->getRefId() > 0 ? ilObject2::_lookupTitle(ilObject2::_lookupObjectId($this->getRefId())) : "");
    }


    /**
     * @param string $alt_text
     *
     * @return RepositoryLink
     */
    public function withAltText(string $alt_text) : RepositoryLink
    {
        $clone = clone($this);
        $clone->alt_text = $alt_text;

        return $clone;
    }


    /**
     * @return string
     */
    public function getAltText() : string
    {
        return $this->alt_text;
    }


    /**
     * @return string
     */
    final public function getAction() : string
    {
        return ilLink::_getLink($this->ref_id);
    }


    /**
     * @param string $action
     *
     * @return hasAction
     */
    public function withAction(string $action) : hasAction
    {
        $clone = clone $this;
        $clone->ref_id = (int) $action;

        return $clone;
    }


    /**
     * @param int $ref_id
     *
     * @return RepositoryLink
     */
    public function withRefId(int $ref_id) : RepositoryLink
    {
        $clone = clone $this;
        $clone->ref_id = $ref_id;

        return $clone;
    }


    /**
     * @return int
     */
    public function getRefId() : int
    {
        return $this->ref_id;
    }


    /**
     * @inheritDoc
     */
    public function withIsLinkToExternalAction(bool $is_external) : hasAction
    {
        throw new \LogicException("Repository-Links are always internal");
    }


    /**
     * @inheritDoc
     */
    public function isLinkWithExternalAction() : bool
    {
        return false;
    }
}
