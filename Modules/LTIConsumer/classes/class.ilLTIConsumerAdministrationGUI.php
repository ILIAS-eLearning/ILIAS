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

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Uri;

/**
 * Class ilLTIConsumingAdministrationGUI
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 *
 * @package     Modules/LTIConsumer
 */
class ilLTIConsumerAdministrationGUI
{
    public const REDIRECTION_CMD_PARAMETER = 'redirectCmd';

    public const CMD_SHOW_GLOBAL_PROVIDER = 'showGlobalProvider';
    public const CMD_APPLY_GLOBAL_PROVIDER_FILTER = 'applyGlobalProviderFilter';
    public const CMD_RESET_GLOBAL_PROVIDER_FILTER = 'resetGlobalProviderFilter';
    public const CMD_SHOW_GLOBAL_PROVIDER_FORM = 'showGlobalProviderForm';
    public const CMD_SAVE_GLOBAL_PROVIDER_FORM = 'saveGlobalProviderForm';
    public const CMD_SHOW_GLOBAL_PROVIDER_IMPORT = 'showGlobalProviderImport';
    public const CMD_SAVE_GLOBAL_PROVIDER_IMPORT = 'saveGlobalProviderImport';

    public const CMD_SHOW_USER_PROVIDER = 'showUserProvider';
    public const CMD_SHOW_USER_PROVIDER_FORM = 'showUserProviderForm';
    public const CMD_SAVE_USER_PROVIDER_FORM = 'saveUserProviderForm';

    public const CMD_ACCEPT_PROVIDER_AS_GLOBAL = 'acceptProviderAsGlobal';
    public const CMD_ACCEPT_PROVIDER_AS_GLOBAL_MULTI = 'acceptProviderAsGlobalMulti';
    public const CMD_RESET_PROVIDER_TO_USER_SCOPE = 'resetProviderToUserScope';
    public const CMD_RESET_PROVIDER_TO_USER_SCOPE_MULTI = 'resetProviderToUserScopeMulti';

    public const CMD_DELETE_GLOBAL_PROVIDER = 'deleteGlobalProvider';
    public const CMD_DELETE_GLOBAL_PROVIDER_MULTI = 'deleteGlobalProviderMulti';
    public const CMD_DELETE_USER_PROVIDER = 'deleteUserProvider';
    public const CMD_DELETE_USER_PROVIDER_MULTI = 'deleteUserProviderMulti';
    public const CMD_PERFORM_DELETE_PROVIDERS = 'performDeleteProviders';

    public const CMD_SHOW_SETTINGS = 'showSettings';
    public const CMD_SAVE_SETTINGS = 'saveSettings';
    public const CMD_ROLE_AUTOCOMPLETE = 'roleAutocomplete';

    public const CMD_SHOW_USAGES = 'showUsages';

    public const ALLOWED_FILE_EXT = ['jpg', 'jpeg', 'png', 'gif', 'ico', 'svg'];

    private array $_importedXmlData = [];
    private \ilGlobalTemplateInterface $main_tpl;

    public function __construct()
    {
        global $DIC;
        $this->main_tpl = $DIC->ui()->mainTemplate(); /* @var \ILIAS\DI\Container $DIC */

        $DIC->language()->loadLanguageModule("rep");

        //$this->performProviderImport($this->xml2());
    }

    protected function initSubTabs(): void
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $DIC->tabs()->clearSubTabs();

        $DIC->tabs()->addSubTab(
            'global_provider',
            $DIC->language()->txt('global_provider_subtab'),
            $DIC->ctrl()->getLinkTarget($this, self::CMD_SHOW_GLOBAL_PROVIDER)
        );

        $DIC->tabs()->addSubTab(
            'user_provider',
            $DIC->language()->txt('user_provider_subtab'),
            $DIC->ctrl()->getLinkTarget($this, self::CMD_SHOW_USER_PROVIDER)
        );

        /* currently no settings at all
        $DIC->tabs()->addSubTab('settings',
            $DIC->language()->txt('settings_subtab'),
            $DIC->ctrl()->getLinkTarget($this, self::CMD_SHOW_SETTINGS)
        );*/

        // TODO: Implement Screen showing all Objects in Reporsitory
        $DIC->tabs()->addSubTab(
            'usage',
            $DIC->language()->txt('usage_subtab'),
            $DIC->ctrl()->getLinkTarget($this, 'showUsages')
        );
    }

    public function executeCommand(): void
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $this->initSubTabs();

        switch ($DIC->ctrl()->getNextClass()) {
            default:

                $cmd = $DIC->ctrl()->getCmd(self::CMD_SHOW_GLOBAL_PROVIDER) . 'Cmd';
                $this->{$cmd}();
        }
    }

//    todo?
    protected function applyGlobalProviderFilterCmd(): void
    {
        $table = $this->buildProviderTable($this, self::CMD_SHOW_GLOBAL_PROVIDER);
        $table->writeFilterToSession();
        $table->resetOffset();
        $this->showGlobalProviderCmd();
    }

    protected function resetGlobalProviderFilterCmd(): void
    {
        $table = $this->buildProviderTable($this, self::CMD_SHOW_GLOBAL_PROVIDER);
        $table->resetFilter();
        $table->resetOffset();
        $this->showGlobalProviderCmd();
    }

    protected function showGlobalProviderCmd(): void
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $DIC->tabs()->activateSubTab('global_provider');

        $button = $DIC->ui()->factory()->button()->standard(
            $DIC->language()->txt('lti_add_global_provider'),
            $DIC->ctrl()->getLinkTarget($this, self::CMD_SHOW_GLOBAL_PROVIDER_FORM)
        );

        $DIC->toolbar()->addComponent($button);

        $button = $DIC->ui()->factory()->button()->standard(
            $DIC->language()->txt('lti_import_global_provider'),
            $DIC->ctrl()->getLinkTarget($this, self::CMD_SHOW_GLOBAL_PROVIDER_IMPORT)
        );

        $DIC->toolbar()->addComponent($button);

        $table = $this->buildProviderTable($this, self::CMD_SHOW_GLOBAL_PROVIDER);
        $table->setEditProviderCmd(self::CMD_SHOW_GLOBAL_PROVIDER_FORM);
        $table->setDeleteProviderCmd(self::CMD_DELETE_GLOBAL_PROVIDER);
        $table->setDeleteProviderMultiCmd(self::CMD_DELETE_GLOBAL_PROVIDER_MULTI);
        $table->setResetProviderToUserScopeCmd(self::CMD_RESET_PROVIDER_TO_USER_SCOPE);
        $table->setResetProviderToUserScopeMultiCmd(self::CMD_RESET_PROVIDER_TO_USER_SCOPE_MULTI);

        $table->init();

        $providerList = new ilLTIConsumeProviderList();
        $providerList->setScopeFilter(ilLTIConsumeProviderList::SCOPE_GLOBAL);

        if ($table->getFilterItemByPostVar('title')->getValue()) {
            $providerList->setTitleFilter($table->getFilterItemByPostVar('title')->getValue());
        }

        if ($table->getFilterItemByPostVar('category')->getValue()) {
            $providerList->setCategoryFilter($table->getFilterItemByPostVar('category')->getValue());
        }

        if ($table->getFilterItemByPostVar('keyword')->getValue()) {
            $providerList->setKeywordFilter($table->getFilterItemByPostVar('keyword')->getValue());
        }

        if ($table->getFilterItemByPostVar('outcome')->getChecked()) {
            $providerList->setHasOutcomeFilter(true);
        }

        if ($table->getFilterItemByPostVar('internal')->getChecked()) {
            $providerList->setIsExternalFilter(false);
        }

        if ($table->getFilterItemByPostVar('with_key')->getChecked()) {
            $providerList->setIsProviderKeyCustomizableFilter(false);
        }

        $providerList->load();

        $table->setData($providerList->getTableData());

        $DIC->ui()->mainTemplate()->setContent($table->getHTML());
    }

    /**
     * @throws \ILIAS\Filesystem\Exception\IOException
     * @throws ilCtrlException
     */
    protected function showGlobalProviderFormCmd(?ilLTIConsumeProviderFormGUI $form = null): void
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $DIC->tabs()->activateSubTab('global_provider');

        if ($form === null) {
            if ($DIC->http()->wrapper()->query()->has('provider_id')) {
                $DIC->ctrl()->saveParameter($this, 'provider_id');
                $provider = new ilLTIConsumeProvider((int) $DIC->http()->wrapper()->query()->retrieve('provider_id', $DIC->refinery()->kindlyTo()->int()));
            } else {
                $provider = new ilLTIConsumeProvider();
            }

            $form = $this->buildProviderForm(
                $provider,
                self::CMD_SAVE_GLOBAL_PROVIDER_FORM,
                self::CMD_SHOW_GLOBAL_PROVIDER
            );
        }

        $DIC->ui()->mainTemplate()->setContent($form->getHTML());
    }

    protected function saveGlobalProviderFormCmd(): void
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $provider = $this->fetchProvider();

        $form = $this->buildProviderForm(
            $provider,
            self::CMD_SAVE_GLOBAL_PROVIDER_FORM,
            self::CMD_SHOW_GLOBAL_PROVIDER
        );

        if ($form->checkInput()) {
            $form->initProvider($provider);

            if (!$provider->getCreator()) {
                $provider->setCreator($DIC->user()->getId());
            }

            $provider->setIsGlobal(true);
            $provider->save();

            $DIC->ctrl()->redirect($this, self::CMD_SHOW_GLOBAL_PROVIDER);
        }

        $this->showGlobalProviderFormCmd($form);
    }

    protected function showGlobalProviderImportCmd(ilPropertyFormGUI $form = null): void
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $DIC->tabs()->activateSubTab('global_provider');

        if ($form === null) {
            $form = $this->buildProviderImportForm(
                self::CMD_SAVE_GLOBAL_PROVIDER_IMPORT,
                self::CMD_SHOW_GLOBAL_PROVIDER
            );
        }

        $DIC->ui()->mainTemplate()->setContent($form->getHTML());
    }

    protected function saveGlobalProviderImportCmd(): void
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $form = $this->buildProviderImportForm(
            self::CMD_SAVE_GLOBAL_PROVIDER_IMPORT,
            self::CMD_SHOW_GLOBAL_PROVIDER
        );

        if (!$form->checkInput()) {
            $this->showGlobalProviderImportCmd($form);
            return;
        }

        $fileData = (array) $DIC->http()->wrapper()->post()->retrieve('provider_xml', $DIC->refinery()->kindlyTo()->listOf($DIC->refinery()->kindlyTo()->string()));

        if (!$fileData['tmp_name']) {
            $this->showGlobalProviderImportCmd($form);
            return;
        }

        $providerXml = file_get_contents($fileData['tmp_name']);

        $provider = $this->performProviderImport($providerXml);

        $this->main_tpl->setOnScreenMessage('success', $DIC->language()->txt('provider_import_success_msg'));
        $DIC->ctrl()->setParameter($this, 'provider_id', $provider->getId());
        $DIC->ctrl()->redirect($this, self::CMD_SHOW_GLOBAL_PROVIDER_FORM);
    }

    /**
     * @throws ilCtrlException
     */
    protected function buildProviderImportForm(string $saveCommand, string $cancelCommand): \ilPropertyFormGUI
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $form = new ilPropertyFormGUI();

        $form->setTitle($DIC->language()->txt('form_import_provider'));

        $form->setFormAction($DIC->ctrl()->getFormAction($this));

        $form->addCommandButton($saveCommand, $DIC->language()->txt('import'));
        $form->addCommandButton($cancelCommand, $DIC->language()->txt('cancel'));

        $provXmlUpload = new ilFileInputGUI($DIC->language()->txt('field_provider_xml'), 'provider_xml');
        $provXmlUpload->setInfo($DIC->language()->txt('field_provider_xml_info'));
        $provXmlUpload->setRequired(true);
        $provXmlUpload->setSuffixes(['xml']);
        $form->addItem($provXmlUpload);

        return $form;
    }

    /**
     * @throws \ILIAS\FileUpload\Exception\IllegalStateException
     * @throws \ILIAS\Filesystem\Exception\FileNotFoundException
     * @throws \ILIAS\Filesystem\Exception\IOException
     */
    protected function performProviderImport(string $providerXml): \ilLTIConsumeProvider
    {
        $doc = new DOMDocument();
        $doc->loadXML($providerXml);
        $xPath = new DOMXPath($doc);
        $this->_importedXmlData = [
            'title' => $xPath->query("//*[local-name() = 'title']")->item(0)->nodeValue,
            'description' => null !== ($desc = $xPath->query("//*[local-name() = 'description']")->item(0)->nodeValue) ? $desc : '',
            'provider_url' => $xPath->query("//*[local-name() = 'launch_url']")->item(0)->nodeValue,
            'provider_icon' => $xPath->query("//*[local-name() = 'icon']")->item(0)->nodeValue,
            'launch_method' => 'newWin',
        ];

        // DONE ?
        /**
         * TODO: parse xml and initialise provider object
         * --> consider two kind xmls
         */

        return $this->prepareProvider();
    }

    /**
     * @throws \ILIAS\FileUpload\Exception\IllegalStateException
     * @throws \ILIAS\Filesystem\Exception\FileNotFoundException
     * @throws \ILIAS\Filesystem\Exception\IOException
     */
    private function prepareProvider(): \ilLTIConsumeProvider
    {
        $provider = new ilLTIConsumeProvider();
        $provider->setTitle($this->getInput('title'));
        $provider->setDescription($this->getInput('description'));
        if (null !== $this->getInput('provider_url')) {
            $provider->setProviderUrl($this->getInput('provider_url'));
        }
        $provider->setIsGlobal(true);
        $provider->save();

        // PROVIDER ICON
        $pId = $provider->getId();
        if (null !== $pIconFileName = $this->getIconXml($this->getInput('provider_icon'), (string) $pId)) {
            $provider->setProviderIconFilename($pIconFileName);
            $provider->update();
            $provider->update();
        }

        return $provider;
    }

    /**
     * @param mixed $key
     * @return string
     */
    private function getInput($key): string
    {
        if (!is_bool($this->_importedXmlData[$key])) {
            $this->_importedXmlData[$key] = trim($this->_importedXmlData[$key]);
        }
        return $this->_importedXmlData[$key];
    }

    /**
     * @throws \ILIAS\Filesystem\Exception\IOException
     */
    private function getIconXml(string $url, string $pId): ?string
    {
        global $DIC;

        $regex = '~(.+)://([^/]+)/([^?]+)\??(.*)~';
        preg_match_all($regex, $url, $urlPart, PREG_SET_ORDER);
        $urlPart = $urlPart[0];
        //var_dump([$url, $urlPart]); exit;
        $fileExt = strtolower(substr($urlPart[3], strrpos($urlPart[3], '.') + 1));
        //var_dump($fileExt); exit;
        if (true !== $this->checkIconFileExtension($fileExt)) {
            return null;
        }
        $finalIcoName = $pId . '.' . $fileExt;

        /** @var GuzzleHttp\Psr7\Uri $uri */
        $uri = new Uri($urlPart[0]);
        $uri->withScheme($urlPart[1])
            ->withHost($urlPart[2])
            ->withPath($urlPart[3])
            ->withQuery($urlPart[4]);
        //var_dump($uri); exit;
        /** @var GuzzleHttp\Client $httpClient */
        $httpClient = new Client();
        $response = $httpClient->get($uri);
        //var_dump($response); exit;
        /** @var GuzzleHttp\Psr7\Stream $icoResource */
        $icoResource = $response->getBody();
        $ico = $icoResource->getContents();

        if (false === $this->checkIconFileVirus($ico)) {
            $DIC->filesystem()->web()->put('lti_data/provider_icon/' . $finalIcoName, $ico); // $DIC->filesystem()->web()->readAndDelete('lti_data/provider_icon/' . $tempIcoName)
        } else {
            return null;
        }

        return $finalIcoName;
    }

    private function checkIconFileExtension(string $ext): bool
    {
//        todo - check?
        return false !== ($check = array_search($ext, self::ALLOWED_FILE_EXT)) ? true : false;
    }

    private function checkIconFileVirus(string $ico): bool
    {
        $virusScan = ilVirusScannerFactory::_getInstance();
        if (!$virusScan) {
            return false;
        }
        return $virusScan->scanBuffer($ico);
        // return false === (bool)$virusScan->scanBuffer($ico) ? false : true;
    }

    protected function showUserProviderCmd(): void
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $DIC->tabs()->activateSubTab('user_provider');

        $providerList = new ilLTIConsumeProviderList();
        $providerList->setScopeFilter(ilLTIConsumeProviderList::SCOPE_USER);
        $providerList->load();

        $table = $this->buildProviderTable($this, self::CMD_SHOW_USER_PROVIDER);
        $table->setEditProviderCmd(self::CMD_SHOW_USER_PROVIDER_FORM);
        $table->setAcceptProviderAsGlobalMultiCmd(self::CMD_ACCEPT_PROVIDER_AS_GLOBAL_MULTI);
        $table->setAcceptProviderAsGlobalCmd(self::CMD_ACCEPT_PROVIDER_AS_GLOBAL);
        $table->setDeleteProviderCmd(self::CMD_DELETE_USER_PROVIDER);
        $table->setDeleteProviderMultiCmd(self::CMD_DELETE_USER_PROVIDER_MULTI);

        $table->setData($providerList->getTableData());

        $table->init();

        $DIC->ui()->mainTemplate()->setContent($table->getHTML());
    }

    /**
     * @throws \ILIAS\Filesystem\Exception\IOException
     * @throws ilCtrlException
     */
    protected function showUserProviderFormCmd(?ilLTIConsumeProviderFormGUI $form = null): void
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $DIC->tabs()->activateSubTab('user_provider');

        if ($form === null) {
            if ($DIC->http()->wrapper()->query()->has('provider_id')) {
                $DIC->ctrl()->saveParameter($this, 'provider_id');
                $provider = new ilLTIConsumeProvider((int) $DIC->http()->wrapper()->query()->retrieve('provider_id', $DIC->refinery()->kindlyTo()->int()));
            } else {
                $provider = new ilLTIConsumeProvider();
            }

            $form = $this->buildProviderForm(
                $provider,
                self::CMD_SAVE_USER_PROVIDER_FORM,
                self::CMD_SHOW_USER_PROVIDER
            );
        }

        $DIC->ui()->mainTemplate()->setContent($form->getHTML());
    }

    /**
     * @throws \ILIAS\FileUpload\Exception\IllegalStateException
     * @throws \ILIAS\Filesystem\Exception\FileNotFoundException
     * @throws \ILIAS\Filesystem\Exception\IOException
     * @throws ilCtrlException
     */
    protected function saveUserProviderFormCmd(): void
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $provider = $this->fetchProvider();

        $form = $this->buildProviderForm(
            $provider,
            self::CMD_SAVE_USER_PROVIDER_FORM,
            self::CMD_SHOW_USER_PROVIDER
        );

        if ($form->checkInput()) {
            $form->initProvider($provider);
            $provider->setIsGlobal(false);
            $provider->save();

            $DIC->ctrl()->redirect($this, self::CMD_SHOW_USER_PROVIDER);
        }

        $this->showUserProviderFormCmd($form);
    }

    /**
     * @throws ilCtrlException
     */
    protected function acceptProviderAsGlobalMultiCmd(): void
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $providers = $this->fetchProviderMulti();

        if (!count($providers)) {
            $this->main_tpl->setOnScreenMessage('failure', $DIC->language()->txt('lti_no_provider_selected'), true);
            $DIC->ctrl()->redirect($this, self::CMD_SHOW_USER_PROVIDER);
        }

        foreach ($providers as $provider) {
            if (!$provider->isAcceptableAsGlobal()) {
                $this->main_tpl->setOnScreenMessage('failure', $DIC->language()->txt('lti_at_least_one_not_acceptable_as_global'), true);
                $DIC->ctrl()->redirect($this, self::CMD_SHOW_USER_PROVIDER);
            }
        }

        $this->performAcceptProvidersAsGlobal($providers);

        $this->main_tpl->setOnScreenMessage('success', $DIC->language()->txt('lti_success_accept_as_global_multi'), true);
        $DIC->ctrl()->redirect($this, self::CMD_SHOW_USER_PROVIDER);
    }

    /**
     * @throws ilCtrlException
     */
    protected function acceptProviderAsGlobalCmd(): void
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $provider = $this->fetchProvider();

        if ($provider->isAcceptableAsGlobal()) {
            $this->performAcceptProvidersAsGlobal([$provider]);
        }

        $this->main_tpl->setOnScreenMessage('success', $DIC->language()->txt('lti_success_accept_as_global'), true);
        $DIC->ctrl()->redirect($this, self::CMD_SHOW_USER_PROVIDER);
    }

    /**
     * @param ilLTIConsumeProvider[] $providers
     * @throws \ILIAS\FileUpload\Exception\IllegalStateException
     * @throws \ILIAS\Filesystem\Exception\FileNotFoundException
     * @throws \ILIAS\Filesystem\Exception\IOException
     */
    protected function performAcceptProvidersAsGlobal(array $providers): void
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        foreach ($providers as $provider) {
            $provider->setIsGlobal(true);
            $provider->setAcceptedBy($DIC->user()->getId());
            $provider->save();
        }
    }

    /**
     * @throws ilCtrlException
     */
    protected function resetProviderToUserScopeMultiCmd(): void
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $providers = $this->fetchProviderMulti();

        if (!count($providers)) {
            $this->main_tpl->setOnScreenMessage('failure', $DIC->language()->txt('lti_no_provider_selected'), true);
            $DIC->ctrl()->redirect($this, self::CMD_SHOW_GLOBAL_PROVIDER);
        }

        foreach ($providers as $provider) {
            if (!$provider->isResetableToUserDefined()) {
                $this->main_tpl->setOnScreenMessage('failure', $DIC->language()->txt('lti_at_least_one_not_resetable_to_usr_def'), true);
                $DIC->ctrl()->redirect($this, self::CMD_SHOW_GLOBAL_PROVIDER);
            }
        }

        $this->performResetProvidersToUserScope($providers);

        $this->main_tpl->setOnScreenMessage('success', $DIC->language()->txt('lti_success_reset_to_usr_def_multi'), true);
        $DIC->ctrl()->redirect($this, self::CMD_SHOW_GLOBAL_PROVIDER);
    }

    /**
     * @throws ilCtrlException
     */
    protected function resetProviderToUserScopeCmd(): void
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $provider = $this->fetchProvider();

        if ($provider->isResetableToUserDefined()) {
            $this->performResetProvidersToUserScope([$provider]);
        }

        $this->main_tpl->setOnScreenMessage('success', $DIC->language()->txt('lti_success_reset_to_usr_def'), true);
        $DIC->ctrl()->redirect($this, self::CMD_SHOW_GLOBAL_PROVIDER);
    }

    /**
     * @param ilLTIConsumeProvider[] $providers
     * @throws \ILIAS\FileUpload\Exception\IllegalStateException
     * @throws \ILIAS\Filesystem\Exception\FileNotFoundException
     * @throws \ILIAS\Filesystem\Exception\IOException
     */
    protected function performResetProvidersToUserScope(array $providers): void
    {
        foreach ($providers as $provider) {
            $provider->setIsGlobal(false);
            $provider->setAcceptedBy(0);
            $provider->save();
        }
    }

    /**
     * @throws ilCtrlException
     */
    protected function deleteGlobalProviderMultiCmd(): void
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $DIC->tabs()->activateSubTab('global_provider');

        $DIC->ctrl()->setParameter($this, self::REDIRECTION_CMD_PARAMETER, self::CMD_SHOW_GLOBAL_PROVIDER);

        $providers = $this->fetchProviderMulti();

        if (!$this->validateProviderDeletionSelection($providers)) {
            $DIC->ctrl()->redirect($this, self::CMD_SHOW_GLOBAL_PROVIDER);
        }

        $this->confirmDeleteProviders($providers, self::CMD_SHOW_GLOBAL_PROVIDER);
    }

    /**
     * @throws ilCtrlException
     */
    protected function deleteGlobalProviderCmd(): void
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $DIC->tabs()->activateSubTab('global_provider');

        $DIC->ctrl()->setParameter($this, self::REDIRECTION_CMD_PARAMETER, self::CMD_SHOW_GLOBAL_PROVIDER);

        $provider = $this->fetchProvider();
        $providers = [$provider->getId() => $provider];

        if (!$this->validateProviderDeletionSelection($providers)) {
            $DIC->ctrl()->redirect($this, self::CMD_SHOW_GLOBAL_PROVIDER);
        }

        $this->confirmDeleteProviders($providers, self::CMD_SHOW_GLOBAL_PROVIDER);
    }

    /**
     * @throws ilCtrlException
     */
    protected function deleteUserProviderMultiCmd(): void
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $DIC->tabs()->activateSubTab('user_provider');

        $DIC->ctrl()->setParameter($this, self::REDIRECTION_CMD_PARAMETER, self::CMD_SHOW_USER_PROVIDER);

        $providers = $this->fetchProviderMulti();

        if (!$this->validateProviderDeletionSelection($providers)) {
            $DIC->ctrl()->redirect($this, self::CMD_SHOW_USER_PROVIDER);
        }

        $this->confirmDeleteProviders($providers, self::CMD_SHOW_USER_PROVIDER);
    }

    /**
     * @throws ilCtrlException
     */
    protected function deleteUserProviderCmd(): void
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $DIC->tabs()->activateSubTab('global_provider');

        $DIC->ctrl()->setParameter($this, self::REDIRECTION_CMD_PARAMETER, self::CMD_SHOW_USER_PROVIDER);

        $provider = $this->fetchProvider();
        $providers = [$provider->getId() => $provider];

        if (!$this->validateProviderDeletionSelection($providers)) {
            $DIC->ctrl()->redirect($this, self::CMD_SHOW_USER_PROVIDER);
        }

        $this->confirmDeleteProviders($providers, self::CMD_SHOW_USER_PROVIDER);
    }

    protected function validateProviderDeletionSelection(array $providers): bool
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        if (!count($providers)) {
            $this->main_tpl->setOnScreenMessage('failure', $DIC->language()->txt('lti_no_provider_selected'), true);
            return false;
        }

        $providerList = $this->getProviderListForIds(array_keys($providers));

        foreach ($providers as $provider) {
            if ($providerList->hasUsages($provider->getId())) {
                $this->main_tpl->setOnScreenMessage('failure', $DIC->language()->txt('lti_at_least_one_prov_has_usages'), true);
                return false;
            }
        }

        return true;
    }

    /**
     * @throws ilCtrlException
     */
    protected function confirmDeleteProviders(array $providers, string $cancelCommand): void
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $confirmationGUI = new ilConfirmationGUI();

        $confirmationGUI->setFormAction($DIC->ctrl()->getFormAction($this));
        $confirmationGUI->setCancel($DIC->language()->txt('cancel'), $cancelCommand);
        $confirmationGUI->setConfirm($DIC->language()->txt('confirm'), self::CMD_PERFORM_DELETE_PROVIDERS);

        $confirmationGUI->setHeaderText($DIC->language()->txt('lti_confirm_delete_providers'));

        foreach ($providers as $provider) {
            /* @var ilLTIConsumeProvider $provider */

            if ($provider->getProviderIcon()->exists()) {
                $providerIcon = $provider->getProviderIcon()->getAbsoluteFilePath();
            } else {
                $providerIcon = ilObject::_getIcon(0, "small", "lti");
            }

            $confirmationGUI->addItem(
                'provider_ids[]',
                (string) $provider->getId(),
                $provider->getTitle(),
                $providerIcon
            );
        }

        $DIC->ui()->mainTemplate()->setContent($confirmationGUI->getHTML());
    }

    /**
     * @throws ilCtrlException
     */
    protected function performDeleteProvidersCmd(): void
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $providers = $this->fetchProviderMulti();

        if ($this->validateProviderDeletionSelection($providers)) {
            foreach ($providers as $provider) {
                $provider->delete();
            }

            $this->main_tpl->setOnScreenMessage('success', $DIC->language()->txt('lti_success_delete_provider'), true);
        }

        $DIC->ctrl()->redirect($this, $DIC->http()->wrapper()->query()->retrieve(self::REDIRECTION_CMD_PARAMETER, $DIC->refinery()->kindlyTo()->string()));
    }

    protected function buildProviderTable(ilLTIConsumerAdministrationGUI $parentGui, string $parentCmd): \ilLTIConsumerProviderTableGUI
    {
        $table = new ilLTIConsumerProviderTableGUI(
            $parentGui,
            $parentCmd
        );

        $table->setFilterCommand(self::CMD_APPLY_GLOBAL_PROVIDER_FILTER);
        $table->setResetCommand(self::CMD_RESET_GLOBAL_PROVIDER_FILTER);

        $table->setAvailabilityColumnEnabled(true);
        $table->setProviderCreatorColumnEnabled(true);

        $table->setActionsColumnEnabled(true);
        $table->setDetailedUsagesEnabled(true);

        return $table;
    }

    protected function showUsagesCmd(): void
    {
        global $DIC;

        $DIC->tabs()->activateSubTab('usage');

        $providerList = new ilLTIConsumeProviderList();
        $providerList->setScopeFilter(ilLTIConsumeProviderList::SCOPE_GLOBAL);
        $providerList->load();

        $table = new ilLTIConsumerProviderUsageTableGUI($this, self::CMD_SHOW_USAGES);
        $table->setData($providerList->getTableDataUsedBy());
        $table->init();

        $DIC->ui()->mainTemplate()->setContent($table->getHTML());
    }

    /**
     * @throws ilCtrlException
     */
    protected function buildProviderForm(ilLTIConsumeProvider $provider, string $saveCmd, string $cancelCmd): \ilLTIConsumeProviderFormGUI
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $form = new ilLTIConsumeProviderFormGUI($provider);
        $form->setAdminContext(true);
        $form->initForm($DIC->ctrl()->getFormAction($this), $saveCmd, $cancelCmd);

        return $form;
    }

    /**
     * @throws \ILIAS\Filesystem\Exception\IOException
     */
    protected function fetchProvider(): \ilLTIConsumeProvider
    {
        global $DIC;

        if ($DIC->http()->wrapper()->query()->has('provider_id')) {
            $provider = new ilLTIConsumeProvider(
                (int) $DIC->http()->wrapper()->query()->retrieve('provider_id', $DIC->refinery()->kindlyTo()->int())
            );
        } else {
            $provider = new ilLTIConsumeProvider();
        }
        return $provider;
    }

    /**
     * @return ilLTIConsumeProvider[]
     * @throws \ILIAS\Filesystem\Exception\IOException
     */
    protected function fetchProviderMulti(): array
    {
        global $DIC;
        $providers = [];

        if (!$DIC->http()->wrapper()->post()->has('provider_ids') ||
            !$DIC->http()->wrapper()->post()->retrieve('provider_ids', $DIC->refinery()->kindlyTo()->listOf($DIC->refinery()->kindlyTo()->int()))
        ) {
            return $providers;
        }
        $provider_ids = $DIC->http()->wrapper()->post()->retrieve('provider_ids', $DIC->refinery()->kindlyTo()->listOf($DIC->refinery()->kindlyTo()->int()));

        foreach ($provider_ids as $providerId) {
            $providers[(int) $providerId] = new ilLTIConsumeProvider((int) $providerId);
        }

        return $providers;
    }


    protected function showSettingsCmd(?ilPropertyFormGUI $form = null): void
    {
//        todo - check
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        return; // no settings at all currently

        $DIC->tabs()->activateSubTab('settings');

        if ($form === null) {
            $form = $this->buildSettingsForm();
        }

        $DIC->ui()->mainTemplate()->setContent($form->getHTML());
    }

    protected function saveSettingsCmd(): void
    {
//        todo - check
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        return; // no settings at all currently

        $form = $this->buildSettingsForm();

        if (!$form->checkInput()) {
            $this->showSettingsCmd($form);
            return;
        }

        $DIC->ctrl()->redirect($this, self::CMD_SHOW_SETTINGS);
    }

    /**
     * @throws ilCtrlException
     */
    protected function buildSettingsForm(): \ilPropertyFormGUI
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $form = new ilPropertyFormGUI();

        $form->setFormAction($DIC->ctrl()->getFormAction($this));
        $form->addCommandButton(self::CMD_SAVE_SETTINGS, $DIC->language()->txt('save'));
        $form->setTitle($DIC->language()->txt('lti_global_settings_form'));

        return $form;
    }

    protected function getProviderListForIds(array $providerIds): ilLTIConsumeProviderList
    {
        $providerList = new ilLTIConsumeProviderList();
        $providerList->setIdsFilter($providerIds);
        $providerList->load();
        $providerList->loadUsages();
        return $providerList;
    }
}
