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

use ILIAS\UI\Implementation\Factory;
use ILIAS\UI\Implementation\DefaultRenderer;
use ILIAS\UI\Implementation\Component\Symbol\Icon;
use ILIAS\UI\Implementation\Component\Item;
use ILIAS\UI\Component\Button;
use ILIAS\UI\Renderer;

/**
 * Personal Desktop-Presentation for the LearningSequence
 */
class ilDashboardLearningSequenceGUI extends ilBlockGUI implements ilDesktopItemHandling
{
    private ilPDSelectedItemsBlockViewSettings $viewSettings;
    private ilPDSelectedItemsBlockViewGUI $blockView;
    public static string $block_type = 'pdlern';
    private string $content = '';
    protected ilLanguage $lng;
    protected ilObjUser $user;
    protected ilAccessHandler $access;
    protected Factory $factory;
    protected Renderer $renderer;

    /**
     * @var array Object-Ids where user is assigned
     */
    protected array $assignments = [];
    protected Icon\Standard $icon;

    public function __construct()
    {
        parent::__construct();
        $this->initViewSettings();
        global $DIC;

        $this->lng = $DIC['lng'];
        $this->user = $DIC['ilUser'];
        $this->access = $DIC['ilAccess'];
        $this->factory = $DIC['ui.factory'];
        $this->renderer = $DIC['ui.renderer'];
        $this->initContent();
    }

    private function initContent() : void
    {
        $content = '';
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

//            if (!$this->isRelevantLso($lso_obj)) {
//                continue;
//            }

            $content .= $this->renderer->render($this->getLsoItem($lso_obj));
        }

        $this->setContent($content);
    }

    /**
     * @return array Object-Ids where user is assigned
     */
    protected function getAssignments() : array
    {
        if (!$this->assignments) {
            $this->assignments = ilParticipants::_getMembershipByType($this->user->getId(), ['lso']);
        }

        return $this->assignments;
    }

    public function getHTML() : string
    {
        global $DIC;

        $this->setTitle($this->getViewTitle());

        $DIC->database()->useSlave(true);

        // workaround to show details row
        $this->setData([['dummy']]);

        $DIC['ilHelp']->setDefaultScreenId(ilHelpGUI::ID_PART_SCREEN, $this->blockView->getScreenId());

        $this->ctrl->clearParameters($this);

        $DIC->database()->useSlave(false);

        return parent::getHTML();
    }

    protected function getLsoItem(ilObjLearningSequence $lso_obj): Item\Standard
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

    protected function isRelevantLso(ilObjLearningSequence $obj): bool
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

    protected function getLinkedTitle(int $ref_id, string $title): Button\Shy
    {
        $link = ilLink::_getLink($ref_id, 'lso');
        return $this->factory->button()->shy($title, $link);
    }

    protected function getOnlineStatus(int $ref_id): string
    {
        $status = ilObjLearningSequenceAccess::isOffline($ref_id);

        if ($status) {
            return 'Offline';
        }

        return 'Online';
    }

    protected function getIcon(string $title): Icon\Standard
    {
        if (!isset($this->icon) || is_null($this->icon)) {
            $this->icon = $this->factory->symbol()->icon()->standard(
                'lso',
                $title,
                'medium'
            );
        }

        return $this->icon;
    }

    protected function initViewSettings() : void
    {
        $this->viewSettings = new ilPDSelectedItemsBlockViewSettings(
            $this->user,
            ilPDSelectedItemsBlockConstants::VIEW_LEARNING_SEQUENCES
        );

        $this->viewSettings->parse();

        $this->blockView = ilPDSelectedItemsBlockViewGUI::bySettings($this->viewSettings);

        $this->ctrl->setParameter($this, 'view', $this->viewSettings->getCurrentView());
    }

    public function getViewSettings() : ilPDSelectedItemsBlockViewSettings
    {
        return $this->viewSettings;
    }

    public function getBlockType() : string
    {
        return static::$block_type;
    }

    protected function isRepositoryObject() : bool
    {
        return false;
    }

    public function addToDeskObject() : void
    {
        $this->returnToContext();
    }

    protected function returnToContext() : void
    {
        $this->ctrl->setParameterByClass('ildashboardgui', 'view', $this->viewSettings->getCurrentView());
        $this->ctrl->redirectByClass('ildashboardgui', 'show');
    }

    public function removeFromDeskObject() : void
    {
        $this->returnToContext();
    }

    protected function getContent() : string
    {
        return $this->content;
    }

    protected function setContent(string $a_content) : void
    {
        $this->content = $a_content;
    }

    public function fillDataSection() : void
    {
        if ($this->getContent() === '') {
            $this->setDataSection($this->blockView->getIntroductionHtml());
        } else {
            $this->tpl->setVariable('BLOCK_ROW', $this->getContent());
        }
    }

    protected function getViewTitle() : string
    {
        return $this->blockView->getTitle();
    }
}
