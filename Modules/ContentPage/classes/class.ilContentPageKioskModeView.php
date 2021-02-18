<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\KioskMode\ControlBuilder;
use ILIAS\KioskMode\State;
use ILIAS\KioskMode\URLBuilder;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\MessageBox\MessageBox;
use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class ilContentPageKioskModeView
 */
class ilContentPageKioskModeView extends ilKioskModeView
{
    const CMD_LP_TO_COMPLETED = 'lp_completed';
    const CMD_LP_TO_INCOMPLETE = 'lp_incomplete';

    /** @var \ilObjContentPage */
    protected $contentPageObject;

    /** @var \ilObjUser */
    protected $user;

    /** @var Factory */
    protected $uiFactory;

    /** @var Renderer */
    protected $uiRenderer;

    /** @var \ilCtrl */
    protected $ctrl;

    /** @var \ilTemplate */
    protected $mainTemplate;

    /** @var ServerRequestInterface */
    protected $httpRequest;

    /** @var \ilTabsGUI */
    protected $tabs;

    /** @var MessageBox */
    protected $messages = [];

    /**
     * @inheritDoc
     */
    protected function getObjectClass() : string
    {
        return \ilObjContentPage::class;
    }

    /**
     * @inheritDoc
     */
    protected function setObject(\ilObject $object)
    {
        global $DIC;

        $this->contentPageObject = $object;

        $this->ctrl = $DIC->ctrl();
        $this->mainTemplate = $DIC->ui()->mainTemplate();
        $this->uiFactory = $DIC->ui()->factory();
        $this->uiRenderer = $DIC->ui()->renderer();
        $this->httpRequest = $DIC->http()->request();
        $this->tabs = $DIC->tabs();
        $this->user = $DIC->user();
    }

    /**
     * @inheritDoc
     */
    protected function hasPermissionToAccessKioskMode() : bool
    {
        return $this->access->checkAccess('read', '', $this->contentPageObject->getRefId());
    }

    /**
     * @inheritDoc
     */
    public function buildInitialState(State $empty_state) : State
    {
        return $empty_state;
    }

    /**
     * @inheritDoc
     */
    public function buildControls(State $state, ControlBuilder $builder)
    {
        $this->builtLearningProgressToggleControl($builder);
    }

    /**
     * @param ControlBuilder $builder
     */
    protected function builtLearningProgressToggleControl(ControlBuilder $builder)
    {
        $learningProgress = \ilObjectLP::getInstance($this->contentPageObject->getId());
        if ($learningProgress->getCurrentMode() == \ilLPObjSettings::LP_MODE_MANUAL) {
            $isCompleted = \ilLPMarks::_hasCompleted($this->user->getId(), $this->contentPageObject->getId());

            $this->lng->loadLanguageModule('copa');
            $learningProgressToggleCtrlLabel = $this->lng->txt('copa_btn_lp_toggle_state_completed');
            $cmd = self::CMD_LP_TO_INCOMPLETE;
            if (!$isCompleted) {
                $learningProgressToggleCtrlLabel = $this->lng->txt('copa_btn_lp_toggle_state_not_completed');
                $cmd = self::CMD_LP_TO_COMPLETED;
            }

            $builder->generic(
                $learningProgressToggleCtrlLabel,
                $cmd,
                1
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function updateGet(State $state, string $command, int $param = null) : State
    {
        $this->toggleLearningProgress($command);

        return $state;
    }

    /**
     * @param string $command
     */
    protected function toggleLearningProgress(string $command)
    {
        if (in_array($command, [
            self::CMD_LP_TO_COMPLETED,
            self::CMD_LP_TO_INCOMPLETE
        ])) {
            $learningProgress = ilObjectLP::getInstance($this->contentPageObject->getId());
            if ($learningProgress->getCurrentMode() == ilLPObjSettings::LP_MODE_MANUAL) {
                $marks = new ilLPMarks($this->contentPageObject->getId(), $this->user->getId());

                $old_state = $marks->getCompleted();
                $new_state = ($command === self::CMD_LP_TO_COMPLETED);
                $marks->setCompleted($new_state);
                $marks->update();
                ilLPStatusWrapper::_updateStatus($this->contentPageObject->getId(), $this->user->getId());

                if ($old_state != $new_state) {
                    $this->lng->loadLanguageModule('trac');
                    $this->messages[] = $this->uiFactory->messageBox()->success(
                        $this->lng->txt('trac_updated_status')
                    );
                }
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function updatePost(State $state, string $command, array $post) : State
    {
        return $state;
    }

    /**
     * @inheritDoc
     */
    public function render(
        State $state,
        Factory $factory,
        URLBuilder $url_builder,
        array $post = null
    ) : Component {
        \ilLearningProgress::_tracProgress(
            $this->user->getId(),
            $this->contentPageObject->getId(),
            $this->contentPageObject->getRefId(),
            $this->contentPageObject->getType()
        );

        $this->renderContentStyle();

        $forwarder = new \ilContentPagePageCommandForwarder(
            $this->httpRequest,
            $this->ctrl,
            $this->tabs,
            $this->lng,
            $this->contentPageObject
        );
        $forwarder->setPresentationMode(\ilContentPagePageCommandForwarder::PRESENTATION_MODE_EMBEDDED_PRESENTATION);

        $this->ctrl->setParameterByClass(\ilContentPagePageGUI::class, 'ref_id', $this->contentPageObject->getRefId());

        return $factory->legacy(implode('', [
            $this->uiRenderer->render($this->messages),
            $forwarder->forward($this->ctrl->getLinkTargetByClass([
                \ilRepositoryGUI::class, \ilObjContentPageGUI::class, \ilContentPagePageGUI::class
            ]))
        ]));
    }

    /**
     * Renders the content style of a ContentPage object into main template
     */
    protected function renderContentStyle()
    {
        $this->mainTemplate->addCss(\ilObjStyleSheet::getSyntaxStylePath());
        $this->mainTemplate->addCss(
            \ilObjStyleSheet::getContentStylePath(
                $this->contentPageObject->getStyleSheetId()
            )
        );
    }
}
