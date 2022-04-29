<?php declare(strict_types=1);

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
 
class ilLearningSequenceXMLWriter extends ilXmlWriter
{
    protected ilObjLearningSequence $ls_object;
    protected ilSetting $settings;
    protected ilLPObjSettings $lp_settings;
    protected ilRbacReview $rbac_review;
    protected ilLearningSequenceSettings $ls_settings;

    public function __construct(
        ilObjLearningSequence $ls_object,
        ilSetting $settings,
        ilLPObjSettings $lp_settings,
        ilRbacReview $rbac_review
    ) {
        $this->ls_object = $ls_object;
        $this->settings = $settings;
        $this->lp_settings = $lp_settings;
        $this->rbac_review = $rbac_review;

        $this->ls_settings = $ls_object->getLSSettings();
    }

    public function getXml() : string
    {
        return $this->xmlDumpMem(false);
    }

    public function start() : void
    {
        $this->writeHeader();
        $this->writeLearningSequence();
        $this->writeTitle();
        $this->writeDescription();
        $this->writeOwner();
        $this->writeLSItems();
        $this->writeSettings();
        $this->writeLPSettings();
        $this->writeFooter();
    }

    protected function writeHeader() : void
    {
        $this->xmlSetDtdDef(
            "<!DOCTYPE learning sequence PUBLIC \"-//ILIAS//DTD LearningSequence//EN\" \"" .
            ILIAS_HTTP_PATH . "/xml/ilias_lso_5_4.dtd\">"
        );

        $this->xmlSetGenCmt(
            "Export of ILIAS LearningSequence " .
            $this->ls_object->getId() .
            " of installation " .
            $this->settings->get("inst_id") .
            "."
        );
    }

    protected function writeLearningSequence() : void
    {
        $att["ref_id"] = $this->ls_object->getRefId();

        $this->xmlStartTag("lso", $att);
    }

    protected function writeTitle() : void
    {
        $this->xmlElement("title", null, $this->ls_object->getTitle());
    }

    protected function writeDescription() : void
    {
        $this->xmlElement("description", null, $this->ls_object->getDescription());
    }

    protected function writeOwner() : void
    {
        $att['id'] = 'il_' . $this->settings->get("inst_id") . '_usr_' . $this->ls_object->getOwner();
        $this->xmlElement('owner', $att);
    }

    protected function writeLSItems() : void
    {
        $ls_items = $this->ls_object->getLSItems();

        $this->xmlStartTag("ls_items");

        foreach ($ls_items as $ls_item) {
            $post_condition = $ls_item->getPostCondition();
            $att["id"] = $ls_item->getRefId();
            $this->xmlStartTag("ls_item", $att);

            $this->xmlElement("ls_item_pc_ref_id", null, $post_condition->getRefId());
            $this->xmlElement("ls_item_pc_condition_type", null, $post_condition->getConditionOperator());
            $this->xmlElement("ls_item_pc_value", null, $post_condition->getValue());
            $this->xmlElement("ls_item_type", null, $ls_item->getType());
            $this->xmlElement("ls_item_title", null, $ls_item->getTitle());
            $this->xmlElement("ls_item_description", null, $ls_item->getDescription());
            $this->xmlElement("ls_item_icon_path", null, $ls_item->getIconPath());
            $this->xmlElement("ls_item_is_online", null, (string) $ls_item->isOnline());
            $this->xmlElement("ls_item_order_number", null, (string) $ls_item->getOrderNumber());

            $this->xmlEndTag("ls_item");
        }

        $this->xmlEndTag("ls_items");
    }

    protected function writeSettings() : void
    {
        $abstract_img = $this->ls_settings->getAbstractImage();
        $extro_img = $this->ls_settings->getExtroImage();

        $this->xmlElement("abstract", null, base64_encode($this->ls_settings->getAbstract()));
        $this->xmlElement("extro", null, base64_encode($this->ls_settings->getExtro()));
        $this->xmlElement("members_gallery", null, $this->ls_settings->getMembersGallery());
        $this->xmlElement("abstract_img", null, $abstract_img);
        $this->xmlElement("extro_img", null, $extro_img);
        $this->xmlElement("abstract_img_data", null, $this->encodeImage($abstract_img));
        $this->xmlElement("extro_img_data", null, $this->encodeImage($extro_img));
    }

    protected function writeLPSettings() : void
    {
        if (!$this->settings->get("enable_tracking")) {
            return;
        }

        $collection = ilLPCollection::getInstanceByMode(
            $this->ls_object->getId(),
            $this->lp_settings->getMode()
        );

        if (!is_null($collection)) {
            $items = $collection->getItems();

            foreach ($items as $item) {
                $this->xmlElement("lp_item_ref_id", null, $item);
            }
        }

        $this->xmlElement("lp_type", null, $this->lp_settings->getObjType());
        $this->xmlElement("lp_mode", null, $this->lp_settings->getMode());
    }

    protected function encodeImage(string $path = null) : string
    {
        if ($path == "") {
            return "";
        }

        return base64_encode(file_get_contents($path));
    }

    protected function writeFooter() : void
    {
        $this->xmlEndTag('lso');
    }
}
