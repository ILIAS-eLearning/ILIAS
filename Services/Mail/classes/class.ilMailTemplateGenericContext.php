<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Mail/classes/class.ilMailTemplateContext.php';

/**
 * @author Guido Vollbach <gvollbach@databay.de>
 * Class ilMailTemplateGenericContext
 */
class ilMailTemplateGenericContext extends ilMailTemplateContext
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'mail_template_generic';
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        global $DIC;

        return $DIC->language()->txt('please_choose');
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        global $DIC;

        return $DIC->language()->txt('please_choose');
    }

    /**
     * {@inheritdoc}
     */
    public function getSpecificPlaceholders()
    {
        return array();
    }

    /**
     * {@inheritdoc}
     */
    public function resolveSpecificPlaceholder($placeholder_id, array $context_parameters, ilObjUser $recipient = null, $html_markup = false)
    {
        return '';
    }
}
