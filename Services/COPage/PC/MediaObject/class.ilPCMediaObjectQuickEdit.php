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

/**
 * Manages business logic in media slate editing
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilPCMediaObjectQuickEdit
{
    protected ilPCMediaObject $pcmedia;
    protected ilObjMediaObject $mob;
    protected int $usage_cnt;

    public function __construct(
        ilPCMediaObject $pcmedia
    ) {
        $this->pcmedia = $pcmedia;
        $this->mob = $pcmedia->getMediaObject();
        $this->usage_cnt = count($this->mob->getUsages());
    }

    // TITLE

    /**
     * Get title (always from mob)
     */
    public function getTitle(): string
    {
        return $this->mob->getTitle();
    }

    /**
     * Is title read only? (If more than one usage exists)
     */
    public function isTitleReadOnly(): bool
    {
        return ($this->usage_cnt > 1);
    }

    /**
     * Set title
     */
    public function setTitle(string $title): void
    {
        if (!$this->isTitleReadOnly()) {
            $this->mob->setTitle($title);
        }
    }

    // STYLE CLASS (always from pc)

    /**
     * Get style class
     */
    public function getClass(): string
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
    public function setClass(string $class): void
    {
        $this->pcmedia->setClass($class);
    }


    // HORIZONTAL ALIGNMENT (from pc standard alias)

    /**
     * Get horizontal alignment
     */
    public function getHorizontalAlign(): string
    {
        return $this->pcmedia->getStandardMediaAliasItem()->getHorizontalAlign();
    }

    /**
     * Set horizontal alignment
     */
    public function setHorizontalAlign(string $align): void
    {
        $this->pcmedia->getStandardMediaAliasItem()->setHorizontalAlign($align);
    }

    // USE FULLSCREEN

    /**
     * Using fullscreen? Yes, if mob has fullscreen item and fullscreen alias exists
     */
    public function getUseFullscreen(): bool
    {
        return ($this->mob->hasFullscreenItem() && $this->pcmedia->getFullscreenMediaAliasItem()->exists());
    }

    public function setUseFullscreen(bool $use_fullscreen): void
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
            if ($this->pcmedia->checkInstanceEditing()) {
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
    public function getCaption(): string
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
    public function setCaption(string $caption): void
    {
        $std_alias = $this->pcmedia->getStandardMediaAliasItem();
        $std_item = $this->mob->getMediaItem("Standard");
        if ($this->pcmedia->checkInstanceEditing()) {
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
    public function getTextRepresentation(): string
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
    public function setTextRepresentation(string $alt_text): void
    {
        $std_alias = $this->pcmedia->getStandardMediaAliasItem();
        $std_item = $this->mob->getMediaItem("Standard");
        if ($this->pcmedia->checkInstanceEditing()) {
            $std_alias->setTextRepresentation($alt_text);
        } else {
            $std_alias->deriveTextRepresentation();
            $std_item->setTextRepresentation($alt_text);
        }
    }
}
