<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

class ilRubricGradeNotification extends ilMailNotification
{

    public function __construct()
    {
        parent::__construct();
    }

    function setObjId($a_val)
    {
        $this->obj_id = $a_val;
    }
    function getObjId()
    {
        return $this->obj_id;
    }

    public function send()
    {
        global $DIC;
        $lng = $DIC["lng"];

        $obj = new ilObjectFactory();
        $instance = $obj->getInstanceByRefId($_GET['ref_id']);

        $link = ilLink::_getLink($_GET['ref_id'], $instance->getType(), array(), '');


        foreach ($this->getRecipients() as $rcp) {
            $this->initLanguage($rcp);
            $this->initMail();
            $this->setSubject(
                sprintf(
                    $lng->txt('rubric_exercise_graded') . ' ' . ilObject::_lookupTitle($this->getObjId()) . ' ' . $lng->txt('rubric_is_now_available'),
                    $this->getObjectTitle(true)
                )
            );
            $this->setBody(ilMail::getSalutation($rcp, $this->getLanguage()));
            $this->appendBody("\n\n");
            $this->appendBody(
                $lng->txt('rubric_exercise_graded') . ' ' . ilObject::_lookupTitle($this->getObjId()) . ' ' . $lng->txt('rubric_is_now_available')
            );
            $this->appendBody("\n");
            $this->appendBody(
                $this->getLanguageText('obj_exc') . ": " . $this->getObjectTitle(true)
            );
            $this->appendBody("\n");
            $this->appendBody("\n\n");
            $this->appendBody($link);
            $this->getMail()->appendInstallationSignature(true);

            $this->sendMail(array($rcp), array('system'));
        }
    }
}
