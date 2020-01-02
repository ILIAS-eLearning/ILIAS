<?php

declare(strict_types=1);

/**
 * @author Daniel Weise <daniel.weise@concepts-and-training.de>
 */
class ilLearningSequenceXMLWriter extends ilXmlWriter
{
    public function __construct(
        ilObjLearningSequence $ls_object,
        ilSetting $il_settings,
        ilLPObjSettings $lp_settings,
        ilRbacReview $rbac_review
    ) {
        $this->ls_object = $ls_object;
        $this->il_settings = $il_settings;
        $this->settings = $ls_object->getLSSettings();
        $this->lp_settings = $lp_settings;
        $this->rbac_review = $rbac_review;
    }

    public function getXml()
    {
        return $this->xmlDumpMem(false);
    }

    public function start()
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

    protected function writeHeader()
    {
        $this->xmlSetDtdDef(
            "<!DOCTYPE learning sequence PUBLIC \"-//ILIAS//DTD LearningSequence//EN\" \"" .
            ILIAS_HTTP_PATH . "/xml/ilias_lso_5_4.dtd\">"
        );

        $this->xmlSetGenCmt(
            "Export of ILIAS LearningSequence " .
            $this->ls_object->getId() .
            " of installation " .
            $this->il_settings->get("inst_id") .
            "."
        );
    }

    protected function writeLearningSequence()
    {
        $att["ref_id"] = $this->ls_object->getRefId();

        $this->xmlStartTag("lso", $att);
    }

    protected function writeTitle()
    {
        $this->xmlElement("title", null, $this->ls_object->getTitle());
    }

    protected function writeDescription()
    {
        $this->xmlElement("description", null, $this->ls_object->getDescription());
    }

    protected function writeOwner()
    {
        $att['id'] = 'il_' . $this->il_settings->get("inst_id") . '_usr_' . $this->ls_object->getOwner();
        $this->xmlElement('owner', $att);
    }

    protected function writeLSItems()
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

    protected function writeSettings()
    {
        $abstract_img = $this->settings->getAbstractImage();
        $extro_img = $this->settings->getExtroImage();

        $this->xmlElement("abstract", null, $this->settings->getAbstract());
        $this->xmlElement("extro", null, $this->settings->getExtro());
        $this->xmlElement("members_gallery", null, $this->settings->getMembersGallery());
        $this->xmlElement("abstract_img", null, $abstract_img);
        $this->xmlElement("extro_img", null, $extro_img);
        $this->xmlElement("abstract_img_data", null, $this->encodeImage($abstract_img));
        $this->xmlElement("extro_img_data", null, $this->encodeImage($extro_img));
    }

    protected function writeLPSettings()
    {
        if (!$this->il_settings->get("enable_tracking")) {
            return;
        }

        $collection = ilLPCollection::getInstanceByMode(
            $this->ls_object->getId(),
            (int) $this->lp_settings->getMode()
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
        if (is_null($path) || $path == "") {
            return "";
        }

        return base64_encode(file_get_contents($path));
    }

    protected function writeFooter()
    {
        $this->xmlEndTag('lso');
    }
}
