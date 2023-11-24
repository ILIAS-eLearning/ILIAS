<?php

declare(strict_types=1);

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

/**
 * Class ilObjLTIConsumerGUI
 * @author       Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author       Björn Heyser <info@bjoernheyser.de>
 * @package      Modules/LTIConsumer
 * @ilCtrl_Calls ilObjLTIConsumerGUI: ilObjectCopyGUI
 * @ilCtrl_Calls ilObjLTIConsumerGUI: ilCommonActionDispatcherGUI
 * @ilCtrl_Calls ilObjLTIConsumerGUI: ilPermissionGUI
 * @ilCtrl_Calls ilObjLTIConsumerGUI: ilObjectMetaDataGUI
 * @ilCtrl_Calls ilObjLTIConsumerGUI: ilInfoScreenGUI
 * @ilCtrl_Calls ilObjLTIConsumerGUI: ilLearningProgressGUI
 * @ilCtrl_Calls ilObjLTIConsumerGUI: ilLTIConsumerSettingsGUI
 * @ilCtrl_Calls ilObjLTIConsumerGUI: ilLTIConsumerXapiStatementsGUI
 * @ilCtrl_Calls ilObjLTIConsumerGUI: ilLTIConsumerScoringGUI
 * @ilCtrl_Calls ilObjLTIConsumerGUI: ilLTIConsumerContentGUI
 * @ilCtrl_Calls ilObjLTIConsumerGUI: ilLTIConsumerGradeSynchronizationGUI
 */
class ilObjLTIConsumerGUI extends ilObject2GUI
{
    public const CFORM_CUSTOM_NEW = 99;
    public const CFORM_DYNAMIC_REGISTRATION = 98; //

    public const TAB_ID_INFO = 'tab_info';
    public const TAB_ID_CONTENT = 'tab_content';
    public const TAB_ID_SETTINGS = 'tab_settings';
    public const TAB_ID_STATEMENTS = 'tab_statements';
    public const TAB_ID_SCORING = 'tab_scoring';
    public const TAB_ID_GRADE_SYNCHRONIZATION = 'tab_grade_synchronization';
    public const TAB_ID_METADATA = 'tab_metadata';
    public const TAB_ID_LEARNING_PROGRESS = 'learning_progress';
    public const TAB_ID_PERMISSIONS = 'perm_settings';

    public const DEFAULT_CMD = 'launch';

    public ?ilObject $object = null;
    protected ilLTIConsumerAccess $ltiAccess;

    public int $parent_node_id = 0; //check

    public function __construct(int $a_id = 0, int $a_id_type = self::REPOSITORY_NODE_ID, int $a_parent_node_id = 0)
    {
        global $DIC;
        /* @var \ILIAS\DI\Container $DIC */

        parent::__construct($a_id, $a_id_type, $a_parent_node_id);
        $this->parent_node_id = $a_parent_node_id;
        if ($this->object instanceof ilObjLTIConsumer) {
            $this->ltiAccess = new ilLTIConsumerAccess($this->object);
        }

        $DIC->language()->loadLanguageModule("lti");
        $DIC->language()->loadLanguageModule("rep");
    }

    public function getType(): string
    {
        return 'lti';
    }

    /**
     * @return \ilPropertyFormGUI[]|null[]
     */
    protected function initCreationForms(string $a_new_type): array
    {
        $forms = array(
            self::CFORM_NEW => $this->initCreateForm($a_new_type)
        );

        if (ilLTIConsumerAccess::hasCustomProviderCreationAccess()) {
            $forms[self::CFORM_DYNAMIC_REGISTRATION] = $this->initDynRegForm($a_new_type);
            $forms[self::CFORM_CUSTOM_NEW] = $this->initCustomCreateForm($a_new_type);
        }

        //$forms[self::CFORM_IMPORT] = $this->initImportForm($a_new_type), // no import yet
        $forms[self::CFORM_CLONE] = $this->fillCloneTemplate(null, $a_new_type);

        return $forms;
    }

    protected function initCreateForm(string $a_new_type): \ilLTIConsumerProviderSelectionFormTableGUI
    {
        global $DIC;
        /* @var \ILIAS\DI\Container $DIC */

        $form = $this->buildProviderSelectionForm($a_new_type);

        $globalProviderList = new ilLTIConsumeProviderList();
        $globalProviderList->setAvailabilityFilter((string) ilLTIConsumeProvider::AVAILABILITY_CREATE);
        $globalProviderList->setScopeFilter(ilLTIConsumeProviderList::SCOPE_GLOBAL);

        $userProviderList = new ilLTIConsumeProviderList();
        $userProviderList->setAvailabilityFilter((string) ilLTIConsumeProvider::AVAILABILITY_CREATE);
        $userProviderList->setScopeFilter(ilLTIConsumeProviderList::SCOPE_USER);
        $userProviderList->setCreatorFilter($DIC->user()->getId());

        if ($form->getFilter('title')) {
            $globalProviderList->setTitleFilter($form->getFilter('title'));
            $userProviderList->setTitleFilter($form->getFilter('title'));
        }

        if ($form->getFilter('category')) {
            $globalProviderList->setCategoryFilter($form->getFilter('category'));
            $userProviderList->setCategoryFilter($form->getFilter('category'));
        }

        if ($form->getFilter('keyword')) {
            $globalProviderList->setKeywordFilter($form->getFilter('keyword'));
            $userProviderList->setKeywordFilter($form->getFilter('keyword'));
        }

        if ($form->getFilter('outcome')) {
            $globalProviderList->setHasOutcomeFilter(true);
            $userProviderList->setHasOutcomeFilter(true);
        }

        if ($form->getFilter('internal')) {
            $globalProviderList->setIsExternalFilter(false);
            $userProviderList->setIsExternalFilter(false);
        }

        if ($form->getFilter('with_key')) {
            $globalProviderList->setIsProviderKeyCustomizableFilter(false);
            $userProviderList->setIsProviderKeyCustomizableFilter(false);
        }

        $globalProviderList->load();
        $userProviderList->load();

        $form->setData([...$globalProviderList->getTableData(), ...$userProviderList->getTableData()]);

        return $form;
    }

    public function initDynRegForm(string $a_new_type): \ilLTIConsumeProviderFormGUI
    {
        global $DIC;
        /* @var \ILIAS\DI\Container $DIC */
        $provider = new ilLTIConsumeProvider();
        $form = new ilLTIConsumeProviderFormGUI($provider);
        $form->initDynRegForm($this->ctrl->getFormAction($this, "cancelDynReg"));
        $form->setTitle($DIC->language()->txt($a_new_type . '_dynamic_registration'));
        return $form;
    }

    public function initShowToolConfig(string $a_new_type, int $a_provider_id): \ilLTIConsumeProviderFormGUI
    {
        global $DIC;

        //ToDo: Error Handling
        $provider = new ilLTIConsumeProvider($a_provider_id);

        $form = new ilLTIConsumeProviderFormGUI($provider);

        $form->initToolConfigForm($this->ctrl->getFormAction($this, "cancelDynReg"), '', '');

        $form->clearCommandButtons();
        $form->addCommandButton("saveDynReg", $this->lng->txt('save'));
        $form->addCommandButton("cancelDynReg", $this->lng->txt("cancel"));

        $form->setTitle($DIC->language()->txt($a_new_type . '_custom_new'));

        return $form;
    }

    public function initCustomCreateForm(string $a_new_type): \ilLTIConsumeProviderFormGUI
    {
        global $DIC;
        /* @var \ILIAS\DI\Container $DIC */

        $provider = new ilLTIConsumeProvider();

        $form = new ilLTIConsumeProviderFormGUI($provider);

        $form->initForm($this->ctrl->getFormAction($this, "save"), '', '');

        $form->clearCommandButtons();
        $form->addCommandButton("saveCustom", $this->lng->txt($a_new_type . "_add_own_provider"));
        $form->addCommandButton("cancel", $this->lng->txt("cancel"));

        $form->setTitle($DIC->language()->txt($a_new_type . '_custom_new'));

        return $form;
    }

    protected function buildProviderSelectionForm(string $a_new_type): \ilLTIConsumerProviderSelectionFormTableGUI
    {
        return new ilLTIConsumerProviderSelectionFormTableGUI(
            $a_new_type,
            $this,
            'create',
            'applyProviderFilter',
            'resetProviderFilter'
        );
    }

    protected function applyProviderFilter(): void
    {
        $form = $this->buildProviderSelectionForm('');
        $form->applyFilter();
        $this->createObject();
    }

    protected function resetProviderFilter(): void
    {
        $form = $this->buildProviderSelectionForm('');
        $form->resetFilter();
        $this->createObject();
    }

    protected function createNewObject(string $newType, string $title, string $description): ilObject
    {
        $classname = "ilObj" . $this->obj_definition->getClassName($newType);

        $newObj = new $classname();
        $newObj->setType($newType);
        $newObj->setTitle($title);
        $newObj->setDescription($description);
        $newObj->create();
        $this->putObjectInTree($newObj);
        return $newObj;
    }

    public function saveCustom(): void
    {
        if (!ilLTIConsumerAccess::hasCustomProviderCreationAccess()) {
            throw new ilLtiConsumerException('permission denied!');
        }

        global $DIC;
        /* @var \ILIAS\DI\Container $DIC */

        $new_type = $this->getRequestValue("new_type");

        $DIC->ctrl()->setParameter($this, "new_type", $new_type);

        $DIC->language()->loadLanguageModule($new_type);

        $form = $this->initCustomCreateForm($new_type);

        if ($form->checkInput()) {
            $DIC->ctrl()->setParameter($this, "new_type", "");

            // create object
            $newObj = $this->createNewObject(
                $new_type,
                $form->getInput('title'),
                $form->getInput('desc')
            );

            // apply didactic template?
            $dtpl = $this->getDidacticTemplateVar("dtpl");
            if ($dtpl) {
                $newObj->applyDidacticTemplate($dtpl);
            }

            $this->afterSave($newObj);

            return;
        }

        $form->setValuesByPost();

        $DIC->ui()->mainTemplate()->setContent($form->getHtml());
    }

    public function addDynReg(): void
    {
        global $DIC;
        if (!ilLTIConsumerAccess::hasCustomProviderCreationAccess()) {
            throw new ilLtiConsumerException('permission denied!');
        }
        $new_type = $this->getRequestValue("new_type");

        $DIC->ctrl()->setParameter($this, "new_type", $new_type);

        $DIC->language()->loadLanguageModule($new_type);
        $form = $this->initDynRegForm($new_type);
        $error = ($this->getRequestValue('error') != null) ? $this->getRequestValue('error') : "";
        $form->setValuesByPost();
        if (!empty($error)) {
            $DIC->ctrl()->setParameter($this, "new_type", "");
            $DIC->ui()->mainTemplate()->setOnScreenMessage('failure', base64_decode($error));
            $DIC->ui()->mainTemplate()->setContent($form->getDynRegError());
            return;
        }
        if ($form->checkInput()) {
            $DIC->ctrl()->setParameter($this, "new_type", "");
            $DIC->ui()->mainTemplate()->setContent($form->getDynRegRequest());
            return;
        }
        $DIC->ui()->mainTemplate()->setContent($form->getHTML());
    }

    public function cancelDynReg(): void
    {
        global $DIC;
        if (!ilLTIConsumerAccess::hasCustomProviderCreationAccess()) {
            throw new ilLtiConsumerException('permission denied!');
        }
        $new_type = $this->getRequestValue("new_type");
        //$provider_id = $this->getRequestValue("provider_id");
        if (ilSession::has('lti_dynamic_registration_client_id')) {
            $providerId = ilLTIConsumeProvider::getProviderIdFromClientId(ilSession::get('lti_dynamic_registration_client_id'));
            if ($providerId > 0) {
                try {
                    $provider = new ilLTIConsumeProvider($providerId);
                    //ToDo: check if provider is in use? should never happen...
                    $provider->delete();
                } catch (\ILIAS\Filesystem\Exception\IOException $e) {
                    //ToDo: error handling
                }
            }
        }
        $DIC->ctrl()->setParameterByClass(ilRepositoryGUI::class, 'new_type', $new_type);
        $DIC->ctrl()->redirectByClass(ilRepositoryGUI::class, 'create');
    }

    public function showToolConfig(): void
    {
        global $DIC;
        if (!ilLTIConsumerAccess::hasCustomProviderCreationAccess()) {
            throw new ilLtiConsumerException('permission denied!');
        }
        $new_type = $this->getRequestValue("new_type");
        $DIC->ctrl()->setParameter($this, "new_type", $new_type);
        $provider_id = $this->getRequestValue("provider_id");
        $DIC->ctrl()->setParameter($this, "provider_id", $provider_id);
        $DIC->language()->loadLanguageModule($new_type);
        $form = $this->initShowToolConfig($new_type, (int)$provider_id);
        $DIC->ui()->mainTemplate()->setContent($form->getHTML());
    }

    public function saveDynReg(): void
    {
        global $DIC;
        if (!ilLTIConsumerAccess::hasCustomProviderCreationAccess()) {
            throw new ilLtiConsumerException('permission denied!');
        }
        $new_type = $this->getRequestValue("new_type");
        $DIC->ctrl()->setParameter($this, "new_type", $new_type);
        $provider_id = $this->getRequestValue("provider_id");
        $DIC->ctrl()->setParameter($this, "provider_id", $provider_id);
        $DIC->language()->loadLanguageModule($new_type);
        ilSession::clear('lti_dynamic_registration_client_id');
        ilSession::clear('lti_dynamic_registration_custom_params');
        $form = $this->initShowToolConfig($new_type, (int)$provider_id);
        $form->setValuesByPost();
        if ($form->checkInput()) { // update only overridable fields
            $provider = $form->getProvider();
            $form->initProvider($provider);
            $provider->update();
            // jump to provider selection is one option:
            // $DIC->ctrl()->redirectByClass([ilRepositoryGUI::class, ilObjLTIConsumerGUI::class],"create");
            // direct redirection to save is onle less click (there is a switch for save | contentSelection in GUI class):
            $DIC->ctrl()->redirectByClass([ilRepositoryGUI::class, ilObjLTIConsumerGUI::class], "save");
            return;
        }
        $DIC->ui()->mainTemplate()->setContent($form->getHTML());
    }

    public function contentSelection(string $providerId, string $newType, string $refId, ilLTIConsumeProvider $provider): void
    {
        global $DIC;
        if (!ilLTIConsumerAccess::hasCustomProviderCreationAccess()) {
            throw new ilLtiConsumerException('permission denied!');
        }
        $DIC->ctrl()->setParameter($this, "new_type", $newType);
        $DIC->ctrl()->setParameter($this, "provider_id", $providerId);
        $DIC->ctrl()->setParameter($this, "ref_id", $refId);

        $form = new ilLTIConsumeProviderFormGUI($provider);
        $formaction = $this->ctrl->getFormAction($this, "cancelContentSelection");
        $DIC->ui()->mainTemplate()->setContent($form->getContentSelectionFrame($formaction));
    }

    public function contentSelectionRequest(): void
    {
        global $DIC;
        if (!ilLTIConsumerAccess::hasCustomProviderCreationAccess()) {
            throw new ilLtiConsumerException('permission denied!');
        }
        $new_type = $this->getRequestValue("new_type");
        $DIC->ctrl()->setParameter($this, "new_type", $new_type);
        $provider_id = $this->getRequestValue("provider_id");
        $DIC->ctrl()->setParameter($this, "provider_id", $provider_id);
        $ref_id = $this->getRequestValue("ref_id");
        $DIC->ctrl()->setParameter($this, "ref_id", $ref_id);
        $DIC->language()->loadLanguageModule($new_type);
        $provider = new ilLTIConsumeProvider((int) $provider_id);
        $redirectUrl = $DIC->ctrl()->getLinkTarget($this, 'contentSelectionRequest');

        if (!ilSession::has('lti13_login_data')) {
            $userIdLTI = ilCmiXapiUser::getIdentAsId($provider->getPrivacyIdent(), $DIC->user());
            //$emailPrimary = ilCmiXapiUser::getIdent($provider->getPrivacyIdent(), $DIC->user());
            $ltiMessageHint = (string) $ref_id . ":" . CLIENT_ID . ":" . base64_encode($redirectUrl);
            $tplLogin = new ilTemplate("tpl.lti_initial_login.html", true, true, "Modules/LTIConsumer");
            $tplLogin->setVariable("LTI_INITIAL_LOGIN_ACTION", $provider->getInitiateLogin());
            $tplLogin->setVariable("ISS", ilObjLTIConsumer::getIliasHttpPath());
            $tplLogin->setVariable("TARGET_LINK_URL", $provider->getProviderUrl());
            $tplLogin->setVariable("LOGIN_HINT", $userIdLTI);
            $tplLogin->setVariable("LTI_MESSAGE_HINT", $ltiMessageHint);
            $tplLogin->setVariable("CLIENT_ID", $provider->getClientId());
            $tplLogin->setVariable("LTI_DEPLOYMENT_ID", (string) $provider->getId());
            echo $tplLogin->get();
            exit; //TODO: no exit
        } else {
            $loginData = ilSession::get('lti13_login_data');
            // ToDo: correct Link!! replace ILIAS_HTTP_PATH
            $data = ilObjLTIConsumer::buildContentSelectionParameters($provider, (int) $ref_id, ilObjLTIConsumer::getIliasHttpPath() . "/" . $DIC->ctrl()->getLinkTarget($this, 'contentSelectionResponse'), $loginData['nonce']);
            $tplContentSelection = new ilTemplate("tpl.lti_jwt_autosubmit.html", true, true, "Modules/LTIConsumer");
            $tplContentSelection->setVariable("LTI_JWT_FORM_ACTION", $provider->getContentItemUrl());
            $tplContentSelection->setVariable("LTI_JWT_ID_TOKEN", $data['id_token']);
            $tplContentSelection->setVariable("LTI_JWT_STATE", $loginData['state']);
            ilSession::clear('lti13_login_data');
            echo $tplContentSelection->get();
            exit; //TODO: no exit
        }
    }

    public function contentSelectionResponse(): void
    {
        global $DIC;
        if (!ilLTIConsumerAccess::hasCustomProviderCreationAccess()) {
            throw new ilLtiConsumerException('permission denied!');
        }
        $new_type = $this->getRequestValue("new_type");
        $DIC->ctrl()->setParameter($this, "new_type", $new_type);
        $provider_id = $this->getRequestValue("provider_id");
        $DIC->ctrl()->setParameter($this, "provider_id", $provider_id);
        $ref_id = $this->getRequestValue("ref_id");
        $DIC->ctrl()->setParameter($this, "ref_id", $ref_id);
        $DIC->language()->loadLanguageModule($new_type);
        $token = '';
        if ($DIC->http()->wrapper()->post()->has('JWT')) {
            $token = $DIC->http()->wrapper()->post()->retrieve('JWT', $DIC->refinery()->kindlyTo()->string());
        }
        if ($DIC->http()->wrapper()->post()->has('jwt')) {
            $token = $DIC->http()->wrapper()->post()->retrieve('jwt', $DIC->refinery()->kindlyTo()->string());
        }
        if (!empty($token)) {
            $provider = new ilLTIConsumeProvider((int) $provider_id);
            $this->saveContentSelection($provider, $token);
        } else {
            echo "no token";
            exit;
            //ToDo:
        }
    }

    public function cancelContentSelection(): void
    {
        global $DIC;
        if (!ilLTIConsumerAccess::hasCustomProviderCreationAccess()) {
            throw new ilLtiConsumerException('permission denied!');
        }
        $new_type = $this->getRequestValue("new_type");
        $DIC->ctrl()->setParameterByClass(ilRepositoryGUI::class, 'new_type', $new_type);
        $DIC->ctrl()->redirectByClass(ilRepositoryGUI::class, 'create');
    }

    public function saveContentSelection(ilLTIConsumeProvider $provider, string $token): void
    {
        //ToDo: fetch with file_get_contents ok? needs caching?
        $jwks = file_get_contents($provider->getPublicKeyset());
        //ToDo: Errorhandling
        $keyset = json_decode($jwks, true);
        $keys = Firebase\JWT\JWK::parseKeySet($keyset);
        $data = Firebase\JWT\JWT::decode($token, $keys);
        //ilObjLTIConsumer::getLogger()->debug(var_export($data,TRUE));
        $refId = $this->getRequestValue('ref_id');

        foreach ($data->{'https://purl.imsglobal.org/spec/lti-dl/claim/content_items'} as $item) {
            $gui = new ilObjLTIConsumerGUI(0, self::REPOSITORY_NODE_ID, (int) $refId);
            $description = "";
            if (isset($item->description)) {
                $description = $item->description;
            }
            $newObj = $gui->createNewObject('lti', $item->title, $description);
            $dtpl = $gui->getDidacticTemplateVar("dtpl");
            if ($dtpl) {
                $newObj->applyDidacticTemplate($dtpl);
            }

            $newObj->setProviderId($provider->getId());
            $newObj->setProvider($provider);
            // custom params
            $customParams = [];
            foreach ($item->{'custom'} as $key => $value) {
                $customParams[] = $key . "=" . $value;
            }
            if (count($customParams) > 0) {
                $newObj->setCustomParams(implode(";", $customParams));
            }
            $newObj->save();
            $this->initMetadata($newObj);
        }
        $type = ilObjectFactory::getTypeByRefId((int) $refId);
        $template = new ilTemplate('tpl.lti_content_selection_finished.html', true, true, 'Modules/LTIConsumer');
        $redirect = ilObjLTIConsumer::getIliasHttpPath() . "/" . "goto.php?target=" . $type . "_" . $refId;
        $template->setVariable("CONTENT_SELECTION_REDIRECT", $redirect);
        echo $template->get();
        exit;
    }

    protected function afterSave(\ilObject $newObject): void
    {
        global $DIC; //check

        if ($DIC->http()->wrapper()->query()->has('provider_id')) {
            $newObject->setProviderId((int) $DIC->http()->wrapper()->query()->retrieve('provider_id', $DIC->refinery()->kindlyTo()->int()));
            $newObject->initProvider();
            $newObject->save();

            $newObject->setTitle($newObject->getProvider()->getTitle());
            $newObject->setMasteryScore($newObject->getProvider()->getMasteryScore());
            $newObject->update();

            $this->initMetadata($newObject);

            $DIC->ctrl()->redirectByClass(ilLTIConsumerSettingsGUI::class);
        }

        if (!ilLTIConsumerAccess::hasCustomProviderCreationAccess()) {
            throw new ilLtiConsumerException('permission denied!');
        }

        $form = $this->initCustomCreateForm($newObject->getType());

        if ($form->checkInput()) {
            $provider = new ilLTIConsumeProvider();
            $form->initProvider($provider);
            $provider->setAvailability(ilLTIConsumeProvider::AVAILABILITY_CREATE);
            $provider->setIsGlobal(false);
            $provider->setCreator($DIC->user()->getId());
            $provider->save();

            $newObject->setProviderId($provider->getId());
            $newObject->setProvider($provider);
            $newObject->save();

            $newObject->setTitle($provider->getTitle());
            $newObject->setMasteryScore($newObject->getProvider()->getMasteryScore());
            $newObject->update();

            $this->initMetadata($newObject);

            $DIC->ctrl()->redirectByClass(ilObjLTIConsumerGUI::class);
        }

        throw new ilLtiConsumerException(
            'form validation seems to not have worked in ilObjLTIConsumer::saveCustom()!'
        );
    }

    public function initMetadata(\ilObject $object): void
    {
        $metadata = new ilMD($object->getId(), $object->getId(), $object->getType());

        $generalMetadata = $metadata->getGeneral();

        if (!$generalMetadata) {
            $generalMetadata = $metadata->addGeneral();
        }

        $generalMetadata->setTitle($object->getTitle());
        $generalMetadata->save();

        $id = $generalMetadata->addIdentifier();
        $id->setCatalog('ILIAS');
        $id->setEntry('il__' . $object->getType() . '_' . $object->getId());
        $id->save();

        $keywords = $object->getProvider()->getKeywordsArray();

        // language needed now
        $ulang = $this->user->getLanguage();
        $keywords = array($ulang => $keywords);

        ilMDKeyword::updateKeywords($generalMetadata, $keywords);
    }

    /**
     * @return ilObjectListGUI
     * @throws ilCtrlException
     */
    protected function initHeaderAction(?string $a_sub_type = null, ?int $a_sub_id = null): ?\ilObjectListGUI
    {
        global $DIC;
        /* @var \ILIAS\DI\Container $DIC */

        $return = parent::initHeaderAction($a_sub_type, $a_sub_id);

        if ($this->creation_mode) {
            return $return;
        }

        $validator = new ilCertificateDownloadValidator();
        if ($validator->isCertificateDownloadable($DIC->user()->getId(), $this->object->getId())) {
            $certLink = $DIC->ctrl()->getLinkTargetByClass(
                [ilObjLTIConsumerGUI::class, ilLTIConsumerSettingsGUI::class],
                ilLTIConsumerSettingsGUI::CMD_DELIVER_CERTIFICATE
            );

            $DIC->language()->loadLanguageModule('certificate');

            $return->addCustomCommand($certLink, 'download_certificate');

            $return->addHeaderIcon(
                'cert_icon',
                ilUtil::getImagePath('icon_cert.svg'),
                $DIC->language()->txt('download_certificate'),
                null,
                null,
                $certLink
            );
        }

        return $return;
    }

    public static function _goto(string $a_target): void
    {
        global $DIC;
        $main_tpl = $DIC->ui()->mainTemplate();
        /* @var \ILIAS\DI\Container $DIC */
        $err = $DIC['ilErr'];
        /* @var ilErrorHandling $err */
        $ctrl = $DIC->ctrl();
        $request = $DIC->http()->request();
        $access = $DIC->access();
        $lng = $DIC->language();

        $targetParameters = explode('_', $a_target);
        $id = (int) $targetParameters[0];

        if ($id <= 0) {
            $err->raiseError($lng->txt('msg_no_perm_read'), $err->FATAL);
        }

        if ($access->checkAccess('read', '', $id)) {
            $ctrl->setTargetScript('ilias.php');
            $ctrl->setParameterByClass(ilObjLTIConsumerGUI::class, 'ref_id', $id);
            $ctrl->redirectByClass([ilRepositoryGUI::class, ilObjLTIConsumerGUI::class]);
        } elseif ($access->checkAccess('visible', '', $id)) {
            ilObjectGUI::_gotoRepositoryNode($id, 'infoScreen');
        } elseif ($access->checkAccess('read', '', ROOT_FOLDER_ID)) {
            $main_tpl->setOnScreenMessage(
                'info',
                sprintf(
                    $DIC->language()->txt('msg_no_perm_read_item'),
                    ilObject::_lookupTitle(ilObject::_lookupObjId($id))
                ),
                true
            );

            ilObjectGUI::_gotoRepositoryRoot();
        }

        $err->raiseError($DIC->language()->txt("msg_no_perm_read_lm"), $err->FATAL);
    }

    /**
     * @throws ilCtrlException
     */
    public function executeCommand(): void
    {
        global $DIC;
        /* @var \ILIAS\DI\Container $DIC */

        // TODO: general access checks (!)

        if (!ilLTIConsumerContentGUI::isEmbeddedLaunchRequest()) {
            $this->prepareOutput();
            $this->addHeaderAction();
        }

        if (!$this->creation_mode) {
            $this->trackObjectReadEvent();

            if ($this->object->getProvider()->hasProviderIcon()) {
                $DIC->ui()->mainTemplate()->setTitleIcon(
                    $this->object->getProvider()->getProviderIcon()->getAbsoluteFilePath(),
                    'Icon ' . $this->object->getProvider()->getTitle()
                );
            }

            $link = ilLink::_getLink($this->object->getRefId(), $this->object->getType());
            $navigationHistory = $DIC['ilNavigationHistory'];
            /* @var ilNavigationHistory $navigationHistory */
            $navigationHistory->addItem($this->object->getRefId(), $link, $this->object->getType());
        }

        /** @var ilObjLTIConsumer $obj */
        $obj = $this->object;

        switch ($DIC->ctrl()->getNextClass()) {
            case strtolower(ilObjectCopyGUI::class):

                $gui = new ilObjectCopyGUI($this);
                $gui->setType($this->getType());
                $DIC->ctrl()->forwardCommand($gui);
                break;

            case strtolower(ilCommonActionDispatcherGUI::class):

                $gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
                $DIC->ctrl()->forwardCommand($gui);
                break;

            case strtolower(ilLearningProgressGUI::class):

                $DIC->tabs()->activateTab(self::TAB_ID_LEARNING_PROGRESS);

                $gui = new ilLearningProgressGUI(
                    ilLearningProgressGUI::LP_CONTEXT_REPOSITORY,
                    $this->object->getRefId()
                );

                $DIC->ctrl()->forwardCommand($gui);

                break;

            case strtolower(ilObjectMetaDataGUI::class):

                $DIC->tabs()->activateTab(self::TAB_ID_METADATA);

                $gui = new ilObjectMetaDataGUI($obj);
                $DIC->ctrl()->forwardCommand($gui);
                break;

            case strtolower(ilPermissionGUI::class):

                $DIC->tabs()->activateTab(self::TAB_ID_PERMISSIONS);

                $gui = new ilPermissionGUI($this);
                $DIC->ctrl()->forwardCommand($gui);
                break;

            case strtolower(ilLTIConsumerSettingsGUI::class):

                $DIC->tabs()->activateTab(self::TAB_ID_SETTINGS);

                $gui = new ilLTIConsumerSettingsGUI($obj, $this->ltiAccess);
                $DIC->ctrl()->forwardCommand($gui);
                break;

            case strtolower(ilLTIConsumerXapiStatementsGUI::class):

                $DIC->tabs()->activateTab(self::TAB_ID_STATEMENTS);

                $gui = new ilLTIConsumerXapiStatementsGUI($obj);
                $DIC->ctrl()->forwardCommand($gui);

                break;

            case strtolower(ilLTIConsumerScoringGUI::class):

                $DIC->tabs()->activateTab(self::TAB_ID_SCORING);

                $gui = new ilLTIConsumerScoringGUI($obj);
                $DIC->ctrl()->forwardCommand($gui);

                break;

            case strtolower(ilLTIConsumerGradeSynchronizationGUI::class):

                $DIC->tabs()->activateTab(self::TAB_ID_GRADE_SYNCHRONIZATION);

                $gui = new ilLTIConsumerGradeSynchronizationGUI($obj);
                $DIC->ctrl()->forwardCommand($gui);

                break;

            case strtolower(ilLTIConsumerContentGUI::class):

                $DIC->tabs()->activateTab(self::TAB_ID_CONTENT);

                $gui = new ilLTIConsumerContentGUI($obj);
                $DIC->ctrl()->forwardCommand($gui);

                break;

            case strtolower(ilInfoScreenGUI::class):

                $DIC->tabs()->activateTab(self::TAB_ID_INFO);
                $this->infoScreen();

                break;

            default:
                if ($DIC->ctrl()->getCmd() === 'save') {
                    $this->checkContentSelection();
                } else {
                    $command = $DIC->ctrl()->getCmd(self::DEFAULT_CMD);
                    $this->{$command}();
                }
        }
    }

    protected function checkContentSelection(): void
    {
        global $DIC;
        $providerId = $this->getRequestValue('provider_id');
        $newType = $this->getRequestValue('new_type');
        $refId = $this->getRequestValue('ref_id');
        if ($providerId !== null && $newType == 'lti' && $refId != null) {
            $provider = new ilLTIConsumeProvider((int)$providerId);
            // check if post variables from contentSelectionResponse
            if ($DIC->http()->wrapper()->post()->has('JWT')) {
                // ToDo:
            } else {
                if ($provider->isContentItem() && !empty($provider->getContentItemUrl())) {
                    $this->contentSelection($providerId, $newType, $refId, $provider);
                } else {
                    $this->save();
                }
            }
        } else {
            $this->save();
        }
    }

    protected function setTabs(): void
    {
        global $DIC;

        /* @var \ILIAS\DI\Container $DIC */
        $DIC->language()->loadLanguageModule('lti');

        if (!$this->object->getOfflineStatus() &&
            $this->object->getProvider()->getAvailability() != ilLTIConsumeProvider::AVAILABILITY_NONE
        ) {
            $DIC->tabs()->addTab(
                self::TAB_ID_CONTENT,
                $DIC->language()->txt(self::TAB_ID_CONTENT),
                $DIC->ctrl()->getLinkTargetByClass(ilLTIConsumerContentGUI::class)
            );
        }

        $DIC->tabs()->addTab(
            self::TAB_ID_INFO,
            $DIC->language()->txt(self::TAB_ID_INFO),
            $this->ctrl->getLinkTargetByClass(ilInfoScreenGUI::class)
        );

        if ($this->ltiAccess->hasWriteAccess()) {
            $DIC->tabs()->addTab(
                self::TAB_ID_SETTINGS,
                $DIC->language()->txt(self::TAB_ID_SETTINGS),
                $DIC->ctrl()->getLinkTargetByClass(ilLTIConsumerSettingsGUI::class)
            );
        }

        if ($this->ltiAccess->hasStatementsAccess()) {
            $DIC->tabs()->addTab(
                self::TAB_ID_STATEMENTS,
                $DIC->language()->txt(self::TAB_ID_STATEMENTS),
                $DIC->ctrl()->getLinkTargetByClass(ilLTIConsumerXapiStatementsGUI::class)
            );
        }

        if ($this->ltiAccess->hasHighscoreAccess()) {
            //$DIC->language()->loadLanguageModule('lti');
            $DIC->tabs()->addTab(
                self::TAB_ID_SCORING,
                $DIC->language()->txt(self::TAB_ID_SCORING),
                $DIC->ctrl()->getLinkTargetByClass(ilLTIConsumerScoringGUI::class)
            );
        }

        if ($this->object->getProvider()->isGradeSynchronization()) {
            $DIC->tabs()->addTab(
                self::TAB_ID_GRADE_SYNCHRONIZATION,
                $DIC->language()->txt(self::TAB_ID_GRADE_SYNCHRONIZATION),
                $DIC->ctrl()->getLinkTargetByClass(ilLTIConsumerGradeSynchronizationGUI::class)
            );
        }

        if ($this->ltiAccess->hasLearningProgressAccess() && $this->object->getProvider()->getHasOutcome()) {
            $DIC->tabs()->addTab(
                self::TAB_ID_LEARNING_PROGRESS,
                $DIC->language()->txt(self::TAB_ID_LEARNING_PROGRESS),
                $DIC->ctrl()->getLinkTargetByClass(ilLearningProgressGUI::class)
            );
        }

        if ($this->ltiAccess->hasWriteAccess()) {
            $gui = new ilObjectMetaDataGUI($this->object);
            $link = $gui->getTab();

            if (strlen($link)) {
                $DIC->tabs()->addTab(
                    self::TAB_ID_METADATA,
                    $DIC->language()->txt('meta_data'),
                    $link
                );
            }
        }

        if ($this->ltiAccess->hasEditPermissionsAccess()) {
            $DIC->tabs()->addTab(
                self::TAB_ID_PERMISSIONS,
                $DIC->language()->txt(self::TAB_ID_PERMISSIONS),
                $DIC->ctrl()->getLinkTargetByClass(ilPermissionGUI::class, 'perm')
            );
        }

        //        if (defined('DEVMODE') && DEVMODE) {
        //            $DIC->tabs()->addTab(
        //                'debug',
        //                'DEBUG',
        //                $DIC->ctrl()->getLinkTarget($this, 'debug')
        //            );
        //        }
    }

    //    protected function debug(): void
    //    {
    //        global $DIC;
    //        /* @var \ILIAS\DI\Container $DIC */
    //
    //        $DIC->tabs()->activateTab('debug');
    //
    //        $filter = new ilCmiXapiStatementsReportFilter();
    //        $filter->setActivityId($this->object->getActivityId());
    //
    //        $aggregateEndPointUrl = str_replace(
    //            'data/xAPI',
    //            'api/statements/aggregate',
    //            $this->object->getProvider()->getXapiLaunchUrl() // should be named endpoint not launch url
    //        );
    //
    //        $linkBuilder = new ilCmiXapiHighscoreReportLinkBuilder(
    //            $this->object->getId(),
    //            $aggregateEndPointUrl,
    //            $filter
    //        );
    //
    //        $basicAuth = ilCmiXapiLrsType::buildBasicAuth(
    //            $this->object->getProvider()->getXapiLaunchKey(),
    //            $this->object->getProvider()->getXapiLaunchSecret()
    //        );
    //
    //        $request = new ilCmiXapiHighscoreReportRequest(
    //            $basicAuth,
    //            $linkBuilder
    //        );
    //
    //        try {
    //            $report = $request->queryReport($this->object->getId());
    //
    //            $DIC->ui()->mainTemplate()->setContent(
    //                $report->getResponseDebug()
    //            );
    //
    //            //ilUtil::sendSuccess('Object ID: '.$this->object->getId());
    //            $DIC->ui()->mainTemplate()->setOnScreenMessage('info', $linkBuilder->getPipelineDebug());
    //            $DIC->ui()->mainTemplate()->setOnScreenMessage('question', '<pre>' . print_r($report->getTableData(), true) . '</pre>');
    //        } catch (Exception $e) {
    //            $this->tpl->setOnScreenMessage('failure', $e->getMessage());
    //        }
    //    }

    protected function addLocatorItems(): void
    {
        global $DIC;
        /* @var \ILIAS\DI\Container $DIC */

        $locator = $DIC['ilLocator'];
        /* @var ilLocatorGUI $locator */
        $locator->addItem(
            $this->object->getTitle(),
            $this->ctrl->getLinkTarget($this, self::DEFAULT_CMD),
            "",
            $this->object->getRefId()
        );
    }

    protected function trackObjectReadEvent(): void
    {
        global $DIC;
        /* @var \ILIAS\DI\Container $DIC */

        ilChangeEvent::_recordReadEvent(
            $this->object->getType(),
            $this->object->getRefId(),
            $this->object->getId(),
            $DIC->user()->getId()
        );

        ilLPStatusWrapper::_updateStatus($this->object->getId(), $DIC->user()->getId());
    }

    protected function launch(): void
    {
        /** @var ilObjLTIConsumer $obj */
        $obj = $this->object;
        $this->tabs_gui->activateTab(self::TAB_ID_CONTENT);
        $gui = new ilLTIConsumerContentGUI($obj);
        $this->ctrl->forwardCommand($gui);
    }

    protected function infoScreen(): void
    {
        global $DIC;
        /* @var \ILIAS\DI\Container $DIC */

        $DIC->tabs()->activateTab(self::TAB_ID_INFO);

        $DIC->ctrl()->setCmd("showSummary");
        $DIC->ctrl()->setCmdClass("ilinfoscreengui");
        $this->infoScreenForward();
    }

    protected function infoScreenForward(): void
    {
        global $DIC;
        /* @var \ILIAS\DI\Container $DIC */
        $ilErr = $DIC['ilErr'];
        /* @var ilErrorHandling $ilErr */

        if (!$this->checkPermissionBool("visible") && !$this->checkPermissionBool("read")) {
            $ilErr->raiseError($DIC->language()->txt("msg_no_perm_read"));
        }

        $this->handleAvailablityMessage();

        $info = new ilInfoScreenGUI($this);

        $info->enablePrivateNotes();

        if ($this->checkPermissionBool("read")) {
            $info->enableNews();
        }

        $info->enableNewsEditing(false);

        if ($this->checkPermissionBool("write")) {
            $news_set = new ilSetting("news");
            $enable_internal_rss = $news_set->get("enable_rss_for_internal");

            if ($enable_internal_rss) {
                //todo check
                $info->setBlockProperty("news", "settings", "true");
                $info->setBlockProperty("news", "public_notifications_option", "true");
            }
        }

        // standard meta data
        $info->addMetaDataSections($this->object->getId(), 0, $this->object->getType());

        if (DEVMODE) {
            // Development Info
            $info->addSection('DEVMODE Info');
            $info->addProperty('Local Object ID', (string) $this->object->getId());
            $info->addProperty('Current User ID', (string) $DIC->user()->getId());
        }
        if ($this->object->getProvider()->getHasOutcome() && ilLPObjSettings::_lookupDBMode($this->object->getId()) != 0) {
            $info->addSection($DIC->language()->txt("lti_info_learning_progress_section"));
            $info->addProperty(
                $DIC->language()->txt("mastery_score"),
                ($this->object->getMasteryScorePercent()) . ' %'
            );
        }

        // LTI Ressource Info about privacy
        $info->addSection($DIC->language()->txt("lti_info_privacy_section"));

        $info->addProperty(
            $DIC->language()->txt("lti_con_prov_url"),
            $this->object->getProvider()->getProviderUrl()
        );

        $info->addProperty(
            $DIC->language()->txt("conf_privacy_name"),
            $DIC->language()->txt('conf_privacy_name_' . ilObjCmiXapiGUI::getPrivacyNameString($this->object->getProvider()->getPrivacyName()))
        );

        $info->addProperty(
            $DIC->language()->txt("conf_privacy_ident"),
            $DIC->language()->txt('conf_privacy_ident_' . ilObjCmiXapiGUI::getPrivacyIdentString($this->object->getProvider()->getPrivacyIdent()))
        );
        if ($this->object->getProvider()->isExternalProvider()) {
            $info->addProperty(
                $DIC->language()->txt("lti_info_external_provider_label"),
                $DIC->language()->txt('lti_info_external_provider_info')
            );
        }

        if ($this->object->getProvider()->getUseXapi()) {
            $info->addProperty(
                $DIC->language()->txt("lti_con_prov_xapi_launch_url"),
                $this->object->getProvider()->getXapiLaunchUrl()
            );
        }

        // FINISHED INFO SCREEN, NOW FORWARD
        $this->ctrl->forwardCommand($info);
    }

    protected function handleAvailablityMessage(): void
    {
        global $DIC;
        /* @var \ILIAS\DI\Container $DIC */
        if ($this->object->getProvider()->getProviderUrl() == '') {
            $this->tpl->setOnScreenMessage('failure', $DIC->language()->txt('lti_provider_not_set_msg'));
        } elseif ($this->object->getProvider()->getAvailability() == ilLTIConsumeProvider::AVAILABILITY_NONE) {
            $this->tpl->setOnScreenMessage('failure', $DIC->language()->txt('lti_provider_not_avail_msg'));
        }
    }

    protected function getRequestValue(string $key): ?string
    {
        if ($this->request_wrapper->has($key)) {
            return $this->request_wrapper->retrieve($key, $this->refinery->kindlyTo()->string());
        }
        return null;
    }
}
