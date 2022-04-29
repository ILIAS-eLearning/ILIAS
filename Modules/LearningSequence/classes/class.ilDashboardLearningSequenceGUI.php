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
 
use ILIAS\UI\Implementation\Factory;
use ILIAS\UI\Implementation\DefaultRenderer;
use ILIAS\UI\Implementation\Component\Symbol\Icon;
use ILIAS\UI\Implementation\Component\Item;
use ILIAS\UI\Component\Button;

/**
 * Personal Desktop-Presentation for the LearningSequence
 */
class ilDashboardLearningSequenceGUI
{
    protected ilLanguage $lng;
    protected ilObjUser $user;
    protected ilAccessHandler $access;
    protected Factory $factory;
    protected DefaultRenderer $renderer;

    /**
     * @var array Object-Ids where user is assigned
     */
    protected array $assignments = [];
    protected Icon\Custom $icon;

    public function __construct()
    {
        global $DIC;

        $this->lng = $DIC['lng'];
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
            $this->assignments = ilParticipants::_getMembershipByType($this->user->getId(), ['lso']);
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
            $ref_ids = ilObject::_getAllReferences($assignment);
            $lso_ref_id = array_shift($ref_ids);

            /** @var ilObjLearningSequence $lso_obj */
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

    protected function getLsoItem(ilObjLearningSequence $lso_obj) : Item\Standard
    {
        $ref_id = $lso_obj->getRefId();
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

    protected function getLinkedTitle(int $ref_id, string $title) : Button\Shy
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

    protected function getIcon(string $title) : Icon\Standard
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
