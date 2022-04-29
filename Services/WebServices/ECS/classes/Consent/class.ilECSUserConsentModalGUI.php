<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use Psr\Http\Message\RequestInterface as RequestInterface;
use ILIAS\UI\Component\Card\RepositoryObject;

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

    private int $usr_id;
    private int $ref_id;
    private int $obj_id;
    private int $mid = 0;

    protected ?ilRemoteObjectBaseGUI $remote_gui = null;
    protected ilRemoteObjectBase $remote_object;
    protected ilECSUserConsents $consents;
    protected ilECSImportManager $importManager;
    protected ilECSExportManager $exportManager;

    protected UIRenderer $ui_renderer;
    protected UIFactory $ui_factory;
    protected RequestInterface $request;
    protected ilToolbarGUI $toolbar;
    protected ilLanguage $lng;
    protected ilCtrlInterface $ctrl;
    protected ilObjectDefinition $objDefinition;

    public function __construct(
        int $a_usr_id,
        int $a_ref_id,
        ilRemoteObjectBaseGUI $remote_gui = null
    ) {
        global $DIC;

        $this->usr_id = $a_usr_id;
        $this->ref_id = $a_ref_id;
        $this->remote_gui = $remote_gui;
        $this->consents = ilECSUserConsents::getInstanceByUserId($this->usr_id);
        $this->importManager = ilECSImportManager::getInstance();
        $this->exportManager = ilECSExportManager::getInstance();

        $this->ui_factory = $DIC->ui()->factory();
        $this->ui_renderer = $DIC->ui()->renderer();
        $this->request = $DIC->http()->request();
        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule('ecs');
        $this->ctrl = $DIC->ctrl();
        $this->objDefinition = $DIC['objDefinition'];

        $this->remote_object = $this->initRemoteObject();
        $this->initMid();
        $this->obj_id = $this->remote_object->getId();
    }

    public function hasConsented() : bool
    {
        return $this->consents->hasConsented($this->mid);
    }

    protected function initMid() : void
    {
        $this->mid = $this->remote_object->getMID();
    }

    protected function lookupOrganization() : string
    {
        return $this->remote_object->getOrganization();
    }

    protected function isLocalObject() : bool
    {
        return $this->remote_object->isLocalObject();
    }

    public function getTitleLink() : string
    {
        if (
            $this->usr_id === ANONYMOUS_USER_ID ||
            $this->isLocalObject() ||
            $this->hasConsented()
        ) {
            return '';
        }
        $components = $this->getConsentModalComponents(self::TRGIGGER_TYPE_SHY);
        return $this->ui_renderer->render($components);
    }

    public function addLinkToToolbar(ilToolbarGUI $toolbar) : void
    {
        if (
            $this->usr_id === ANONYMOUS_USER_ID ||
            $this->isLocalObject()
        ) {
            return;
        }
        if ($this->hasConsented()) {
            $this->addRemoteLinkToToolbar($toolbar);
        } else {
            $this->addConsentModalToToolbar($toolbar);
        }
    }

    protected function addRemoteLinkToToolbar(ilToolbarGUI $toolbar) : void
    {
        $button = $this->ui_factory
            ->button()
            ->standard(
                $this->lng->txt($this->remote_object->getType() . '_call'),
                $this->ctrl->getLinkTarget($this->remote_gui, 'call')
            );
        $toolbar->addComponent($button);
    }

    public function addConsentModalToToolbar(ilToolbarGUI $toolbar) : void
    {
        $components = $this->getConsentModalComponents();
        foreach ($components as $component) {
            $toolbar->addComponent($component);
        }
    }

    public function addConsentModalToCard(
        RepositoryObject $card
    ) : RepositoryObject {
        $components = $this->getConsentModalComponents(self::TRIGGER_TYPE_CARD);
        foreach ($components as $component) {
            if ($component === null) {
                continue;
            }
            $this->toolbar->addComponent($component);

            $image = $card->getImage();
            $image = $image->withOnClick($component->getShowSignal());
            $card = $card
                ->withImage($image)
                ->withTitleAction($component->getShowSignal());
        }
        return $card;
    }

    protected function getConsentModalComponents(
        int $a_trigger_type = self::TRGIGGER_TYPE_STANDARD
    ) : array {
        $form = $this->initConsentForm();
        $form_id = 'form_' . $form->getId();
        $agree = $this->ui_factory->button()
                                  ->primary('Agree and Proceed', '#')
                                  ->withOnLoadCode(
                                      function ($id) use ($form_id) {
                                          return "$('#$id').click(function() { $('#$form_id').submit(); return false; });";
                                      }
                                  );

        $submitted = (string) ($this->request->getParsedBody()['cmd'] ?? '');
        $valid = true;
        $error_html = '';
        if (strcmp($submitted, 'submit') === 0) {
            if (!$this->saveConsent($form)) {
                $form->setValuesByPost();
                $error = $this->ui_factory->messageBox()->failure(
                    $this->lng->txt('ecs_consent_required')
                );
                $error_html = $this->ui_renderer->render([$error]);
                $valid = false;
            }
        }

        $modal = $this->ui_factory->modal()->roundtrip(
            $this->lng->txt('ecs_consent_modal_title'),
            $this->ui_factory->legacy(
                $error_html .
                $form->getHTML()
            )
        )->withActionButtons([$agree]);
        if (!$valid) {
            $modal = $modal->withOnLoad($modal->getShowSignal());
        }

        $button = null;
        if ($a_trigger_type === self::TRGIGGER_TYPE_STANDARD) {
            $button = $this->ui_factory->button()->standard(
                $this->lng->txt($this->remote_object->getType() . '_call'),
                '#'
            )->withOnClick(
                $modal->getShowSignal()
            );
        } elseif ($a_trigger_type === self::TRGIGGER_TYPE_SHY) {
            $button = $this->ui_factory->button()->shy(
                $this->remote_object->getTitle(),
                '#'
            )->withOnClick(
                $modal->getShowSignal()
            );
        }
        return [$button, $modal];
    }

    protected function saveConsent(ilPropertyFormGUI $form) : bool
    {
        $consented = (bool) ($this->request->getParsedBody()['consent'] ?? 0);
        if ($consented) {
            $this->consents->add($this->mid);
            $this->ctrl->setParameterByClass(
                $this->getGUIClassName(),
                'ref_id',
                $this->ref_id
            );
            $this->ctrl->redirectToURL(
                $this->ctrl->getLinkTargetByClass(
                    [
                        ilRepositoryGUI::class,
                        $this->getGUIClassName()
                    ],
                    'call'
                )
            );
            return true;
        }
        return false;
    }

    protected function initConsentForm() : ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setId(uniqid('form'));
        $form->setFormAction('#');

        $title = new ilNonEditableValueGUI(
            $this->lng->txt('title'),
            'title'
        );
        $title->setValue(
            ilObject::_lookupTitle($this->obj_id)
        );
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

        $consent = new ilCheckboxInputGUI(
            $this->lng->txt('ecs_form_consent'),
            'consent'
        );
        $consent->setValue("1");
        $consent->setChecked($this->consents->hasConsented($this->mid));
        $consent->setRequired(true);
        $form->addItem($consent);

        $user_data_fields = [];
        foreach (['login',
                  'firstname',
                  'lastname',
                  'email',
                  'institution'
                 ] as $field) {
            $user_data_fields[] = $this->lng->txt('ecs_' . $field);
        }
        $listing = $this->ui_factory->listing()->unordered($user_data_fields);
        $listing_html = $this->ui_renderer->render([$listing]);
        $consent->setOptionTitle(
            $this->lng->txt(
                'ecs_form_consent_option_title'
            ) . '<br />' . $listing_html
        );
        $submit = new ilHiddenInputGUI('cmd');
        $submit->setValue('submit');
        $form->addItem($submit);
        return $form;
    }

    protected function getOrganisation() : ?ilECSOrganisation
    {
        $server_id = $this->importManager->lookupServerId($this->obj_id);
        $community_reader = ilECSCommunityReader::getInstanceByServerId(
            $server_id
        );
        try {
            $part = $community_reader->getParticipantByMID($this->mid);
            if ($part instanceof ilECSParticipant) {
                return $part->getOrganisation();
            }
            return null;
        } catch (ilECSConnectorException $e) {
            return null;
        }
    }

    protected function getGUIClassName() : string
    {
        return get_class($this->remote_gui);
    }

    /**
     * @throws ilDatabaseException
     * @throws ilObjectNotFoundException
     */
    protected function initRemoteObject() : ilRemoteObjectBase
    {
        $remote = ilObjectFactory::getInstanceByRefId($this->ref_id);
        if (!$remote instanceof ilRemoteObjectBase) {
            throw new ilObjectNotFoundException(
                'Invalid ref_id given: ' . $this->ref_id
            );
        }
        return $remote;
    }
}
