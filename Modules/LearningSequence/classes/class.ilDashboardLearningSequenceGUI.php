<?php

/* Copyright (c) 2015 Daniel Weise <daniel.weise@concepts-and-training.de> Extended GPL, see docs/LICENSE */

/**
 * Personal Desktop-Presentation for the Learningsequence
 */
class ilDashboardLearningSequenceGUI
{
    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * @var ilAccessHandler
     */
    protected $access;

    /**
     * @var ILIAS\UI\Implementation\Factory
     */
    protected $factory;

    /**
     * @var ILIAS\UI\Implementation\DefaultRenderer
     */
    protected $renderer;

    /**
     * @var array Object-Ids where user is assigned
     */
    protected $assignments;

    /**
     * @var ILIAS\UI\Implementation\Component\Symbol\Icon\Custom
     */
    protected $icon;

    public function __construct()
    {
        global $DIC;

        $this->lng = $DIC['lng'];
        ;
        $this->user = $DIC['ilUser'];
        $this->access = $DIC['ilAccess'];
        $this->factory = $DIC['ui.factory'];
        $this->renderer = $DIC['ui.renderer'];
    }

    /**
     * @return array Object-Ids where user is assigned
     */
    protected function getAssignments() : array
    {
        if (is_null($this->assignments)) {
            $this->assignments = ilParticipants::_getMembershipByType($this->user->getId(), 'lso');
        }

        return $this->assignments;
    }

    public function getHTML() : string
    {
        if (count($this->getAssignments()) == 0) {
            return '';
        }

        $items = array();
        foreach ($this->getAssignments() as $assignment) {
            $lso_ref_id = array_shift(ilObject::_getAllReferences($assignment));
            $lso_obj = ilObjLearningSequence::getInstanceByRefId($lso_ref_id);

            if (!$lso_obj) {
                continue;
            }

            if (!$this->access->checkAccess('read', '', $lso_ref_id)) {
                continue;
            }

            if (!$this->isRelevantLso($lso_obj)) {
                continue;
            }

            $items[] = $this->getLsoItem($lso_obj);
        }

        if (count($items) == 0) {
            return '';
        }

        $std_list = $this->factory->panel()->listing()->standard($this->lng->txt('dash_learningsequences'), array(
            $this->factory->item()->group(
                '',
                $items
            )
        ));

        return $this->renderer->render($std_list);
    }

    protected function getLsoItem(ilObjLearningSequence $lso_obj) : ILIAS\UI\Implementation\Component\Item\Standard
    {
        $ref_id = (int) $lso_obj->getRefId();
        $title = $lso_obj->getTitle();

        $link = $this->getLinkedTitle($ref_id, $title);

        return $this->factory->item()->standard($link)
            ->withProperties(
                [
                    $this->lng->txt('status') => $this->getOnlineStatus($ref_id)
                ]
            )
            ->withLeadIcon($this->getIcon($title))
        ;
    }

    protected function isRelevantLso(ilObjLearningSequence $obj) : bool
    {
        $relevant = false;

        $ls_lp_items = $obj->getLSLearnerItems($this->user->getId());
        if (count($ls_lp_items) == 0) {
            return $relevant;
        }

        foreach ($ls_lp_items as $item) {
            if ($item->getLearningProgressStatus() == ilLPStatus::LP_STATUS_IN_PROGRESS_NUM) {
                $relevant = true;
            }
        }

        return $relevant;
    }

    protected function getLinkedTitle(int $ref_id, string $title) : ILIAS\UI\Component\Button\Shy
    {
        $link = ilLink::_getLink($ref_id, 'lso');
        return $this->factory->button()->shy($title, $link);
    }

    protected function getOnlineStatus(int $ref_id) : string
    {
        $status = ilObjLearningSequenceAccess::isOffline($ref_id);

        if ($status) {
            return 'Offline';
        }

        return 'Online';
    }

    protected function getIcon(string $title) : ILIAS\UI\Component\Symbol\Icon\Icon
    {
        if (is_null($this->icon)) {
            $this->icon = $this->factory->symbol()->icon()->standard(
                'lso',
                $title,
                'medium'
            );
        }

        return $this->icon;
    }
}
