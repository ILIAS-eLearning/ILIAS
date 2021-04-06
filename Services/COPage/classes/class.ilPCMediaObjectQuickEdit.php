<?php

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Manages business logic in media slate editing
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilPCMediaObjectQuickEdit
{
    /**
     * @var ilPCMediaObject
     */
    protected $pcmedia;

    /**
     * @var ilObjMediaObject
     */
    protected $mob;

    /**
     * @var int
     */
    protected $usage_cnt;

    /**
     * Constructor
     */
    public function __construct(ilPCMediaObject $pcmedia)
    {
        $this->pcmedia = $pcmedia;
        $this->mob = $pcmedia->getMediaObject();
        $this->usage_cnt = count($this->mob->getUsages());
    }

    // TITLE

    /**
     * Get title (always from mob)
     */
    public function getTitle() : string
    {
        return $this->mob->getTitle();
    }

    /**
     * Is title read only? (If more than one usage exists)
     */
    public function isTitleReadOnly() : bool
    {
        return ($this->usage_cnt > 1);
    }

    /**
     * Set title
     */
    public function setTitle(string $title)
    {
        if (!$this->isTitleReadOnly()) {
            $this->mob->setTitle($title);
        }
    }

    // STYLE CLASS (always from pc)

    /**
     * Get style class
     */
    public function getClass() : string
    {
        $selected = $this->pcmedia->getClass();
        if ($selected == "") {
            $selected = "MediaContainer";
        }
        return $selected;
    }

    /**
     * Set style class
     */
    public function setClass(string $class)
    {
        $this->pcmedia->setClass($class);
    }


    // HORIZONTAL ALIGNMENT (from pc standard alias)

    /**
     * Get horizontal alignment
     */
    public function getHorizontalAlign() : string
    {
        return $this->pcmedia->getStandardMediaAliasItem()->getHorizontalAlign();
    }

    /**
     * Set horizontal alignment
     */
    public function setHorizontalAlign(string $align)
    {
        $this->pcmedia->getStandardMediaAliasItem()->setHorizontalAlign($align);
    }

    // USE FULLSCREEN

    /**
     * Using fullscreen? Yes, if mob has fullscreen item and fullscreen alias exists
     */
    public function getUseFullscreen() : bool
    {
        return ($this->mob->hasFullscreenItem() && $this->pcmedia->getFullscreenMediaAliasItem()->exists());
    }

    public function setUseFullscreen(bool $use_fullscreen)
    {
        $full_alias = $this->pcmedia->getFullscreenMediaAliasItem();
        // if fullscreen should be used...
        if ($use_fullscreen) {

            //... ensure mob has fullscreen
            if (!$this->mob->hasFullscreenItem()) {
                $std_item = $this->mob->getMediaItem("Standard");
                $full_item = new ilMediaItem();
                $this->mob->addMediaItem($full_item);
                $full_item->setPurpose("Fullscreen");
                $full_item->setLocationType($std_item->getLocationType());
                $full_item->setFormat($std_item->getFormat());
                $full_item->setLocation($std_item->getLocation());
            }

            //... ensure fullscreen alias exists
            if (!$full_alias->exists()) {
                $full_alias->insert();
                $full_alias->deriveSize();
                $full_alias->deriveCaption();
                $full_alias->deriveTextRepresentation();
                $full_alias->deriveParameters();
            }
        } else {
            if ($this->usage_cnt > 1) {
                if ($full_alias->exists()) {
                    $full_alias->delete();
                }
            } else {
                if ($this->mob->hasFullscreenItem()) {
                    $this->mob->removeMediaItem("Fullscreen");
                }
            }
        }
    }

    // CAPTION

    /**
     * Get caption from pc, if set, from mob otherwise
     */
    public function getCaption() : string
    {
        $std_alias = $this->pcmedia->getStandardMediaAliasItem();
        $std_item = $this->mob->getMediaItem("Standard");

        if (trim($std_alias->getCaption()) == "") {
            return trim($std_item->getCaption());
        }
        return trim($std_alias->getCaption());
    }

    /**
     * Set caption (pc if more usages, otherwise mob)
     */
    public function setCaption(string $caption)
    {
        $std_alias = $this->pcmedia->getStandardMediaAliasItem();
        $std_item = $this->mob->getMediaItem("Standard");
        if ($this->usage_cnt > 1) {
            $std_alias->setCaption($caption);
        } else {
            $std_alias->deriveCaption();
            $std_item->setCaption($caption);
        }
    }

    // ALT TEXT

    /**
     * Get text representation from pc, if set, from mob otherwise
     */
    public function getTextRepresentation() : string
    {
        $std_alias = $this->pcmedia->getStandardMediaAliasItem();
        $std_item = $this->mob->getMediaItem("Standard");

        if (trim($std_alias->getTextRepresentation()) == "") {
            return trim($std_item->getTextRepresentation());
        }
        return trim($std_alias->getTextRepresentation());
    }

    /**
     * Set text representation (pc if more usages, otherwise mob)
     */
    public function setTextRepresentation(string $alt_text)
    {
        $std_alias = $this->pcmedia->getStandardMediaAliasItem();
        $std_item = $this->mob->getMediaItem("Standard");
        if ($this->usage_cnt > 1) {
            $std_alias->setTextRepresentation($alt_text);
        } else {
            $std_alias->deriveTextRepresentation();
            $std_item->setTextRepresentation($alt_text);
        }
    }
}
