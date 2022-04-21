<?php declare(strict_types=1);

use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use Psr\Http\Message\RequestInterface as RequestInterface;
use ILIAS\UI\Implementation\Component\Card\Card as Card;

/**
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 * @ilCtrl_IsCalledBy ilECSUserConsentModalGUI: ilObjRemoteCategoryGUI, ilObjRemoteCourseGUI, ilObjRemoteFileGUI
 * @ilCtrl_IsCalledBy ilECSUserConsentModalGUI: ilObjRemoteGlossaryGUI, ilObjRemoteGroupGUI, ilObjRemoteLearningModuleGUI
 * @ilCtrl_IsCalledBy ilECSUserConsentModalGUI: ilObjRemoteTestGUI, ilObjRemoteWikiGUI
 *
 */
class ilECSUserConsentModalGUI
{
    public const CMD_RENDER_MODAL = 'renderConsentModal';
    public const CMD_SAVE_CONSENT = 'saveConsent';

    protected const TRGIGGER_TYPE_SHY = 1;
    protected const TRGIGGER_TYPE_STANDARD = 2;
    protected const TRIGGER_TYPE_CARD = 3;


    /**
     * @var int
     */
    private $usr_id = 0;
    private $ref_id = 0;
    private $obj_id = 0;
    private $mid = 0;

    private $type = '';


    /**
     * @var ilRemoteObjectBaseGUI
     */
    private $remote_gui = null;

    /**
     * @var ilECSUserConsents
     */
    protected $consents;

    /**
     * @var UIRenderer
     */
    protected $ui_renderer;

    /**
     * @var UIFactory
     */
    protected $ui_factory;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilCtrl
     */
    protected  $ctrl;

    /**
     * @var ilObjectDefinition
     */
    protected $objDefinition;


    public function __construct(int $a_usr_id, int $a_ref_id, ilRemoteObjectBaseGUI $remote_gui = null)
    {
        global $DIC;

        $this->usr_id = $a_usr_id;
        $this->ref_id = $a_ref_id;
        $this->obj_id = ilObject::_lookupObjId($this->ref_id);
        $this->type = ilObject::_lookupType($this->obj_id);
        $this->remote_gui = $remote_gui;
        $this->consents = ilECSUserConsents::getInstanceByUserId($this->usr_id);

        $this->ui_factory = $DIC->ui()->factory();
        $this->ui_renderer = $DIC->ui()->renderer();
        $this->request = $DIC->http()->request();
        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule('ecs');
        $this->ctrl = $DIC->ctrl();
        $this->objDefinition = $DIC['objDefinition'];

        $this->initMid();
    }

    public function hasConsented() : bool
    {
        return $this->consents->hasConsented($this->mid);
    }

    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd(self::CMD_RENDER_MODAL);
        //$cmd(); // no idea why this fails
        switch ($cmd) {
            case self::CMD_RENDER_MODAL:
                $this->renderConsentModal();
                break;
            case self::CMD_SAVE_CONSENT:
                $this->saveConsent();
                break;
        }
    }

    protected function initMid() : void
    {
        $classname = $this->objDefinition->getClassName($this->type);
        $object_classname = 'ilObj' . $classname;
        $this->mid = (int) $object_classname::_lookupMid($this->obj_id);
    }

    protected function lookupOrganization() : string
    {
        $classname = $this->objDefinition->getClassName($this->type);
        $object_classname = 'ilObj' . $classname;
        return $object_classname::_lookupOrganization($this->obj_id);
    }
    
    protected function isLocalObject() 
    {
        return !ilECSExport::_isRemote(
            ilECSImport::lookupServerId($this->obj_id),
            ilECSImport::_lookupEContentId($this->obj_id)
        );
    }


    public function getTitleLink()  : string
    {
        if (
            $this->usr_id === ANONYMOUS_USER_ID ||
            $this->isLocalObject() ||
            $this->consents->hasConsented($this->mid)
        ) {
            return '';
        }
        $components = $this->getConsentModalComponents(self::TRGIGGER_TYPE_SHY);
        return $this->ui_renderer->render($components);
    }

    public function addLinkToToolbar(ilToolbarGUI $toolbar)
    {
        if (
            $this->usr_id === ANONYMOUS_USER_ID ||
            $this->isLocalObject()
        ) {
            return;
        }
        if ($this->consents->hasConsented(1)) {
            $this->addRemoteLinkToToolbar($toolbar);

        } else {
            $this->addConsentModalToToolbar($toolbar);
        }
    }


    protected function addRemoteLinkToToolbar(ilToolbarGUI $toolbar)
    {
        $button = $this->ui_factory
            ->button()
            ->standard(
                $this->lng->txt(ilObject::_lookupType(ilObject::_lookupObjId($this->ref_id)) . '_call'),
                $this->ctrl->getLinkTarget($this->remote_gui, 'call')
            );
        $toolbar->addComponent($button);
    }


    public function addConsentModalToToolbar(ilToolbarGUI $toolbar)
    {
        $components = $this->getConsentModalComponents();
        foreach ($components as $component) {
            $toolbar->addComponent($component);
        }
    }

    public function addConsentModalToCard(Card $card)
    {
        global $DIC;

        $components = $this->getConsentModalComponents(self::TRIGGER_TYPE_CARD);
        foreach ($components as $component) {
            if ($component === null) {
                continue;
            }
            $DIC->toolbar()->addComponent($component);

            $image = $card->getImage();
            $image = $image->withOnClick($component->getShowSignal());
            $card = $card
                ->withImage($image)
                ->withTitleAction($component->getShowSignal());


        }
        return $card;
    }

    protected function getConsentModalComponents(int $a_trigger_type = self::TRGIGGER_TYPE_STANDARD) : array
    {
        $form = $this->initConsentForm();
        $form_id = 'form_' . $form->getId();
        $agree = $this->ui_factory->button()
                                  ->primary('Agree and Proceed', '#')
                                  ->withOnLoadCode(function ($id) use ($form_id) {
                                      return "$('#{$id}').click(function() { $('#{$form_id}').submit(); return false; });";
                                  });

        $submitted = (string) ($this->request->getParsedBody()['cmd'] ?? '');
        $valid = true;
        if (strcmp($submitted, 'submit') === 0) {
            if (!$this->saveConsent($form)) {
                $form->setValuesByPost();
                $form->getItemByPostVar('consent')->setAlert($this->lng->txt('ecs_consent_required'));
                $valid = false;
            }
        }

        $modal = $this->ui_factory->modal()->roundtrip(
            $this->lng->txt('ecs_consent_modal_title'),
            $this->ui_factory->legacy($form->getHTML())
        )->withActionButtons([$agree]);
        if (!$valid) {
            $modal = $modal->withOnLoad($modal->getShowSignal());
        }

        $button = null;
        if ($a_trigger_type === self::TRGIGGER_TYPE_STANDARD) {
            $button = $this->ui_factory->button()->standard(
                $this->lng->txt(ilObject::_lookupType(ilObject::_lookupObjId($this->ref_id)) . '_call'),
                '#')->withOnClick($modal->getShowSignal()
            );
        } elseif ($a_trigger_type === self::TRGIGGER_TYPE_SHY) {
            $button = $this->ui_factory->button()->shy(
                ilObject::_lookupTitle(ilObject::_lookupObjId($this->ref_id)),
                '#')->withOnClick($modal->getShowSignal()
            );
        }
        return [$button, $modal];
    }

    protected function saveConsent(ilPropertyFormGUI $form)
    {
        $consented = (bool) ($this->request->getParsedBody()['consent'] ?? 0);
        if ($consented) {
            $this->consents->add($this->mid);
            $this->ctrl->setParameterByClass($this->getGUIClassName(), 'ref_id', $this->ref_id);
            $this->ctrl->redirectToURL($this->ctrl->getLinkTargetByClass(
                [
                    ilRepositoryGUI::class,
                    $this->getGUIClassName()
                ],
                'call'
            ));
            return true;
        }
        return false;
    }

    protected function initConsentForm()
    {
        $form = new ilPropertyFormGUI();
        $form->setTableWidth('100%');
        $form->setId(uniqid('form'));
        $form->setTarget('_top');
        $form->setFormAction('#');

        $title = new ilNonEditableValueGUI(
            $this->lng->txt('title'),
            'title'
        );
        $title->setValue(ilObject::_lookupTitle(ilObject::_lookupObjId($this->ref_id)));
        $form->addItem($title);

        $target = new ilNonEditableValueGUI(
            $this->lng->txt('ecs_form_target_platform'),
            'organisation'
        );
        $target->setValue($this->lookupOrganization());
        $form->addItem($target);

        // provider
        $organisation = $this->getOrganisation();
        if ($organisation instanceof ilECSOrganisation) {
            $provider = new ilNonEditableValueGUI(
                $this->lng->txt('organization'),
                'provider'
            );
            $provider->setValue($organisation->getName());
            $form->addItem($provider);
        }

        $consent = new ilCheckboxInputGUI($this->lng->txt('ecs_form_consent'), 'consent');
        $consent->setValue("1");
        $consent->setChecked($this->consents->hasConsented($this->mid));
        $consent->setRequired(true);
        $form->addItem($consent);

        $user_data_fields = [];
        foreach(['login', 'firstname', 'lastname', 'email', 'institution'] as $field) {
            $user_data_fields[] = $this->lng->txt('ecs_' . $field);
        }
        $listing = $this->ui_factory->listing()->unordered($user_data_fields);
        $listing_html = $this->ui_renderer->render([$listing]);
        $consent->setOptionTitle($this->lng->txt('ecs_form_consent_option_title') . '<br />' . $listing_html);
        $submit = new ilHiddenInputGUI('cmd');
        $submit->setValue('submit');
        $form->addItem($submit);
        return $form;
    }

    protected function getOrganisation() : ?ilECSOrganisation
    {
        $server_id = ilECSImport::lookupServerId($this->obj_id);
        $community_reader = ilECSCommunityReader::getInstanceByServerId($server_id);
        try {
            $part = $community_reader->getParticipantByMID($this->mid);
            if ($part instanceof ilECSParticipant) {
                return $part->getOrganisation();
            }
        } catch (ilECSConnectorException $e) {
            ;
        }
    }

    protected function getGUIClassName() : string
    {
        $classname = $this->objDefinition->getClassName($this->type);
        return 'ilObj' . $classname . 'GUI';

    }
}