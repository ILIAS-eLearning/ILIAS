<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilScormPlaceholderDescription implements ilCertificatePlaceholderDescription
{
    /**
     * @var ilDefaultPlaceholderDescription
     */
    private $defaultPlaceHolderDescriptionObject;

    /**
     * @var ilLanguage|null
     */
    private $language;

    /**
     * @var array
     */
    private $placeholder;

    /**
     * @var ilObject
     */
    private $object;

    /**
     * @var ilObjectLP|mixed|null
     */
    private $learningProgressObject;

    /**
     * @param ilObject $object
     * @param ilDefaultPlaceholderDescription|null $defaultPlaceholderDescriptionObject
     * @param ilLanguage|null $language
     * @param ilObjectLP|null $learningProgressObject
     * @param ilUserDefinedFieldsPlaceholderDescription|null $userDefinedFieldPlaceHolderDescriptionObject
     */
    public function __construct(
        ilObject $object,
        ilDefaultPlaceholderDescription $defaultPlaceholderDescriptionObject = null,
        ilLanguage $language = null,
        ilObjectLP $learningProgressObject = null,
        ilUserDefinedFieldsPlaceholderDescription $userDefinedFieldPlaceHolderDescriptionObject = null
    ) {
        global $DIC;

        $this->object = $object;

        if (null === $language) {
            $language = $DIC->language();
        }
        $this->language = $language;

        if (null === $defaultPlaceholderDescriptionObject) {
            $defaultPlaceholderDescriptionObject = new ilDefaultPlaceholderDescription($language, $userDefinedFieldPlaceHolderDescriptionObject);
        }
        $this->defaultPlaceHolderDescriptionObject = $defaultPlaceholderDescriptionObject;

        if (null === $learningProgressObject) {
            $learningProgressObject = ilObjectLP::getInstance($this->object->getId());
        }
        $this->learningProgressObject = $learningProgressObject;

        $this->placeholder = $this->defaultPlaceHolderDescriptionObject->getPlaceholderDescriptions();

        $this->placeholder['SCORM_TITLE']        = $language->txt('certificate_ph_scormtitle');
        $this->placeholder['SCORM_POINTS']       = $language->txt('certificate_ph_scormpoints');
        $this->placeholder['SCORM_POINTS_MAX']   = $language->txt('certificate_ph_scormmaxpoints');
        $this->placeholder['DATE_COMPLETED']     = ilUtil::prepareFormOutput($language->txt('certificate_ph_date_completed'));
        $this->placeholder['DATETIME_COMPLETED'] = ilUtil::prepareFormOutput($language->txt('certificate_ph_datetime_completed'));
    }

    /**
     * This methods MUST return an array containing an array with
     * the the description as array value.
     *
     * @param ilTemplate|null $template
     * @return mixed - [PLACEHOLDER] => 'description'
     */
    public function createPlaceholderHtmlDescription(ilTemplate $template = null) : string
    {
        if (null === $template) {
            $template = new ilTemplate('tpl.scorm_description.html', true, true, 'Services/Certificate');
        }

        $template->setCurrentBlock('items');

        foreach ($this->placeholder as $id => $caption) {
            $template->setVariable('ID', $id);
            $template->setVariable('TXT', $caption);
            $template->parseCurrentBlock();
        }

        $template->setVariable('PH_INTRODUCTION', $this->language->txt('certificate_ph_introduction'));

        $collection = $this->learningProgressObject->getCollectionInstance();
        if ($collection) {
            $items = $collection->getPossibleItems();
        }

        if (!$items) {
            $template->setCurrentBlock('NO_SCO');
            $template->setVariable('PH_NO_SCO', $this->language->txt('certificate_ph_no_sco'));
            $template->parseCurrentBlock();
        } else {
            $template->setCurrentBlock('SCOS');
            $template->setVariable('PH_SCOS', $this->language->txt('certificate_ph_scos'));
            $template->parseCurrentBlock();
            $template->setCurrentBlock('SCO_HEADER');
            $template->setVariable('PH_TITLE_SCO', $this->language->txt('certificate_ph_title_sco'));
            $template->setVariable('PH_SCO_TITLE', $this->language->txt('certificate_ph_sco_title'));
            $template->setVariable('PH_SCO_POINTS_RAW', $this->language->txt('certificate_ph_sco_points_raw'));
            $template->setVariable('PH_SCO_POINTS_MAX', $this->language->txt('certificate_ph_sco_points_max'));
            $template->setVariable('PH_SCO_POINTS_SCALED', $this->language->txt('certificate_ph_sco_points_scaled'));
            $template->parseCurrentBlock();
        }

        if ($collection) {
            $counter = 0;
            foreach ($items as $item_id => $sahs_item) {
                if ($collection->isAssignedEntry($item_id)) {
                    $template->setCurrentBlock('SCO');
                    $template->setVariable('SCO_TITLE', $sahs_item['title']);
                    $template->setVariable('PH_SCO_TITLE', '[SCO_T_' . $counter . ']');
                    $template->setVariable('PH_SCO_POINTS_RAW', '[SCO_P_' . $counter . ']');
                    $template->setVariable('PH_SCO_POINTS_MAX', '[SCO_PM_' . $counter . ']');
                    $template->setVariable('PH_SCO_POINTS_SCALED', '[SCO_PP_' . $counter . ']');
                    $template->parseCurrentBlock();
                    $counter++;
                }
            }
        }

        return $template->get();
    }

    /**
     * This method MUST return an array containing an array with
     * the the description as array value.
     *
     * @return mixed - [PLACEHOLDER] => 'description'
     */
    public function getPlaceholderDescriptions() : array
    {
        return $this->placeholder;
    }
}
