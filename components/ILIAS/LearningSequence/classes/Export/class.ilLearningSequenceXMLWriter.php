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

declare(strict_types=1);

class ilLearningSequenceXMLWriter extends ilXmlWriter
{
    public const TAG_LSO = 'LearningSequence';
    public const TAG_LSITEMS = 'LSItems';
    public const TAG_LSITEM = 'LSItem';
    public const TAG_MEMBERSGALLERY = 'MembersGallery';
    public const TAG_CONDITION = 'Condition';
    public const TAG_LPSETTING = 'LPSetting';
    public const TAG_LPREFID = 'LPRefId';
    public const TAG_TITLE = 'title';
    public const TAG_DESCRIPTION = 'description';
    public const TAG_CONTAINERSETTING = 'ContainerSetting';

    protected ilLearningSequenceSettings $ls_settings;

    public function __construct(
        protected ilObjLearningSequence $ls_object,
        protected ilSetting $settings,
        protected ilLPObjSettings $lp_settings
    ) {
        parent::__construct();
        $this->ls_settings = $ls_object->getLSSettings();
    }

    public function getXml(): string
    {
        return $this->xmlDumpMem(false);
    }

    public function start(): void
    {
        $this->writeHeader();
        $this->writeLearningSequence();
        $this->writeLSItems();
        $this->writeFooter();
    }

    protected function writeHeader(): void
    {
        $this->xmlSetDtdDef(
            "<!DOCTYPE learning sequence PUBLIC \"-//ILIAS//DTD LearningSequence//EN\" \"" .
            ILIAS_HTTP_PATH . "/xml/ilias_lso_9_0.dtd\">"
        );

        $this->xmlSetGenCmt(
            "Export of ILIAS LearningSequence " .
            $this->ls_object->getId() .
            " of installation " .
            $this->settings->get("inst_id") .
            "."
        );
    }

    protected function writeLearningSequence(): void
    {
        $attributes = [
            'ref_id' => $this->ls_object->getRefId(),
            'members_gallery' => $this->ls_settings->getMembersGallery() ? 'true' : 'false'
        ];
        $this->xmlStartTag(self::TAG_LSO, $attributes);

        $this->xmlElement(self::TAG_TITLE, null, $this->ls_object->getTitle());
        if ($desc = $this->ls_object->getDescription()) {
            $this->xmlElement(self::TAG_DESCRIPTION, null, $desc);
        }

        $this->writeLPSettings();
        \ilContainer::_exportContainerSettings($this, $this->ls_object->getId());
    }

    protected function writeLPSettings(): void
    {
        $type = $this->lp_settings->getObjType();
        $mode = $this->lp_settings->getMode();
        $this->xmlStartTag(
            self::TAG_LPSETTING,
            [
                'type' => $type,
                'mode' => $mode
            ]
        );
        $collection = ilLPCollection::getInstanceByMode(
            $this->ls_object->getId(),
            $mode
        );
        if (!is_null($collection)) {
            $items = $collection->getItems();
            foreach ($items as $item) {
                $this->xmlElement(self::TAG_LPREFID, null, $item);
            }
        }
        $this->xmlEndTag(self::TAG_LPSETTING);
    }

    protected function writeLSItems(): void
    {
        $this->xmlStartTag(self::TAG_LSITEMS);

        $ls_items = $this->ls_object->getLSItems();
        foreach ($ls_items as $ls_item) {
            $post_condition = $ls_item->getPostCondition();

            $this->xmlStartTag(
                self::TAG_LSITEM,
                [
                    'obj_id' => \ilObject::_lookupObjectId($ls_item->getRefId()),
                    'ref_id' => $ls_item->getRefId()
                ]
            );

            $this->xmlElement(
                self::TAG_CONDITION,
                ['type' => $post_condition->getConditionOperator()],
                $post_condition->getValue()
            );

            $this->xmlEndTag(self::TAG_LSITEM);
        }

        $this->xmlEndTag(self::TAG_LSITEMS);
    }

    protected function writeFooter(): void
    {
        $this->xmlEndTag(self::TAG_LSO);
    }
}
