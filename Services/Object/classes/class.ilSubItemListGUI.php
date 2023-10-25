<?php

declare(strict_types=1);

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
 * Base class for all sub item list gui's
 *
 * @author Stefan Meyer <meyer@leifos.com>
 */
abstract class ilSubItemListGUI
{
    protected static int $MAX_SUBITEMS = 5;
    protected static array $details = [];

    protected ilCtrlInterface $ctrl;
    protected ilLanguage $lng;

    protected string $cmd_class = '';

    protected ?ilTemplate $tpl = null;
    protected ?ilLuceneHighlighterResultParser $highlighter = null;
    protected array $subitem_ids = [];
    protected ?ilObjectListGUI $item_list_gui = null;
    protected int $ref_id = 0;
    protected int $obj_id = 0;
    protected string $type = '';

    public function __construct(string $cmd_class)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();

        $this->cmd_class = $cmd_class;
        self::$MAX_SUBITEMS = ilSearchSettings::getInstance()->getMaxSubitems();
    }

    /**
     * set show details.
     * Show all sub item links for a specific object
     * As long as static::setShowDetails is not possible this method is final
     */
    final public static function setShowDetails(int $obj_id): void
    {
        $_SESSION['lucene_search']['details'][$obj_id] = true;
    }

    /**
     * As long as static::resetDetails is not possible this method is final
     */
    final public static function resetDetails(): void
    {
        $_SESSION['lucene_search']['details'] = [];
    }

    /**
     * As long as static::enableDetails is not possible this method is final
     */
    final public static function enabledDetails(int $obj_id): bool
    {
        return isset($_SESSION['lucene_search']['details'][$obj_id]) && $_SESSION['lucene_search']['details'][$obj_id];
    }

    public function getCmdClass(): string
    {
        return $this->cmd_class;
    }

    public function setHighlighter(?ilLuceneHighlighterResultParser $highlighter): void
    {
        $this->highlighter = $highlighter;
    }

    public function getHighlighter(): ?ilLuceneHighlighterResultParser
    {
        return $this->highlighter;
    }

    public function getRefId(): int
    {
        return $this->ref_id;
    }

    public function getObjId(): int
    {
        return $this->obj_id;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getSubItemIds(bool $limited = false): array
    {
        if ($limited && !self::enabledDetails($this->getObjId())) {
            return array_slice($this->subitem_ids, 0, self::$MAX_SUBITEMS);
        }

        return $this->subitem_ids;
    }

    public function getItemListGUI(): ?ilObjectListGUI
    {
        return $this->item_list_gui;
    }

    public function init(ilObjectListGUI $item_list_gui, int $ref_id, array $subitem_ids): void
    {
        $this->tpl = new ilTemplate('tpl.subitem_list.html', true, true, 'Services/Object');
        $this->item_list_gui = $item_list_gui;
        $this->ref_id = $ref_id;
        $this->obj_id = ilObject::_lookupObjId($this->getRefId());
        $this->type = ilObject::_lookupType($this->getObjId());
        $this->subitem_ids = $subitem_ids;
    }

    protected function showDetailsLink(): void
    {
        if (count($this->getSubItemIds()) <= self::$MAX_SUBITEMS) {
            return;
        }
        if (self::enabledDetails($this->getObjId())) {
            return;
        }

        $additional = count($this->getSubItemIds()) - self::$MAX_SUBITEMS;

        $this->ctrl->setParameterByClass($this->getCmdClass(), 'details', $this->getObjId());
        $link = $this->ctrl->getLinkTargetByClass($this->getCmdClass(), '');
        $this->ctrl->clearParametersByClass($this->getCmdClass());

        $this->tpl->setCurrentBlock('choose_details');
        $this->tpl->setVariable('LUC_DETAILS_LINK', $link);
        $this->tpl->setVariable('LUC_NUM_HITS', sprintf($this->lng->txt('lucene_more_hits_link'), $additional));
        $this->tpl->parseCurrentBlock();
    }

    // begin-patch mime_filter
    protected function parseRelevance(int $sub_item): void
    {
        if (
            !ilSearchSettings::getInstance()->isSubRelevanceVisible() ||
            !ilSearchSettings::getInstance()->enabledLucene()
        ) {
            return;
        }

        $relevance = $this->getHighlighter()->getRelevance($this->getObjId(), $sub_item);

        $pbar = ilProgressBar::getInstance();
        $pbar->setCurrent($relevance);

        $this->tpl->setVariable('REL_PBAR', $pbar->render());
    }
    // end-patch mime_filter

    abstract public function getHTML(): string;
}
