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

use ILIAS\UI\Component\Input\Container\Form\Standard as Form;

/**
 * The tab of an ILIAS object that releases it to the platforms allowed for its type, with the local roles
 * the users of each platform get.
 *
 * The name and the methods are fixed: the objects that can be released (courses, groups, tests, ...) create
 * this class, add its tab and forward to it.
 *
 * @author Saúl Díaz <sdiaz@surlabs.com>
 */
class ilLTIProviderObjectSettingGUI
{
    public const string CMD_SHOW = 'settings';
    public const string CMD_SAVE = 'updateSettings';

    private readonly ILIAS\DI\Container $dic;
    /**
     * @var array the local roles of the object users of a platform can get
     */
    private array $roles = [];

    public function __construct(private readonly int $ref_id)
    {
        global $DIC;

        $this->dic = $DIC;
        $this->dic->language()->loadLanguageModule('lti');
    }

    /**
     * True when the object can be released to some platform and the user may release objects.
     */
    public function hasSettingsAccess(): bool
    {
        $admin_ref_id = ilObjLTIAdministrationAccess::lookupRefId();

        return $admin_ref_id !== null
            && ilLTIRelease::getPlatformsForType(ilObject::_lookupType($this->ref_id, true)) !== []
            && $this->dic->rbac()->system()->checkAccess('release_objects', $admin_ref_id);
    }

    /**
     * An object without local roles gets LTI roles of its own, when the administration has created the
     * global LTI role.
     *
     * @param array $role_ids
     */
    public function setCustomRolesForSelection(array $role_ids): void
    {
        if ($role_ids === []) {
            $this->createLocalRoles();
            $role_ids = $this->dic->rbac()->review()->getLocalRoles($this->ref_id);
        }
        $this->roles = $role_ids;
    }

    /**
     * Kept for the objects that call it, the roles offered are always the ones given above.
     */
    public function offerLTIRolesForSelection(bool $offer): void
    {
    }

    /**
     * @throws ilCtrlException
     */
    public function executeCommand(): void
    {
        if (!$this->hasSettingsAccess()) {
            $this->dic->ui()->mainTemplate()->setOnScreenMessage('failure', $this->dic->language()->txt('permission_denied'), true);
            $this->dic->ctrl()->redirectByClass(ilRepositoryGUI::class);
        }

        match ($this->dic->ctrl()->getCmd(self::CMD_SHOW)) {
            self::CMD_SAVE => $this->save(),
            default => $this->show(),
        };
    }

    private function show(?Form $form = null): void
    {
        $this->dic->ui()->mainTemplate()->setContent($this->dic->ui()->renderer()->render($form ?? $this->buildForm()));
    }

    /**
     * @throws ilCtrlException
     */
    private function save(): void
    {
        $form = $this->buildForm()->withRequest($this->dic->http()->request());
        $data = $form->getData();
        if ($data === null) {
            $this->show($form);
            return;
        }

        foreach ($this->getPlatforms() as $platform_id => $title) {
            $release = $data['platforms']['platform_' . $platform_id] ?? null;
            if ($release !== null) {
                new ilLTIRelease($this->ref_id, $platform_id)->save(
                    (int) $release['admin'],
                    (int) $release['tutor'],
                    (int) $release['member']
                );
            }
            new ilLTI1p1ProviderObjectCredentials($this->ref_id, $platform_id)->save($release ?? [], $release !== null);
        }

        $this->dic->ui()->mainTemplate()->setOnScreenMessage('success', $this->dic->language()->txt('settings_saved'), true);
        $this->dic->ctrl()->redirect($this, self::CMD_SHOW);
    }

    /**
     * One optional group per platform: releasing the object to it and the roles its users get.
     *
     * @throws ilCtrlException
     */
    private function buildForm(): Form
    {
        $lng = $this->dic->language();
        $factory = $this->dic->ui()->factory();
        $field = $factory->input()->field();

        $options = [];
        foreach ($this->roles as $role_id) {
            $options[(string) $role_id] = ilObjRole::_getTranslation(ilObjRole::_lookupTitle((int) $role_id));
        }

        $inputs = [];
        foreach ($this->getPlatforms() as $platform_id => $title) {
            $release = new ilLTIRelease($this->ref_id, $platform_id);
            $credentials = new ilLTI1p1ProviderObjectCredentials($this->ref_id, $platform_id);
            $role = fn(string $txt, int $value) => $field->select($lng->txt($txt), $options)
                ->withValue(isset($options[(string) $value]) ? (string) $value : null);

            $group = $field->optionalGroup([
                'admin' => $role('lti_admin', $release->getAdminRole()),
                'tutor' => $role('lti_tutor', $release->getTutorRole()),
                'member' => $role('lti_member', $release->getMemberRole()),
            ] + $credentials->getInputs($lng, $factory), $title);

            $inputs['platform_' . $platform_id] = $credentials->isEnabled() ? $group : $group->withValue(null);
        }

        return $factory->input()->container()->form()->standard(
            $this->dic->ctrl()->getFormAction($this, self::CMD_SAVE),
            ['platforms' => $factory->input()->field()->section($inputs, $lng->txt('lti_object_release_settings_form'))]
        );
    }

    /**
     * The platforms the object can be released to with LTI 1.1.
     *
     * @return array titles by id
     */
    private function getPlatforms(): array
    {
        return array_filter(
            ilLTIRelease::getPlatformsForType(ilObject::_lookupType($this->ref_id, true)),
            fn(int $platform_id): bool => ilLTIAdministrationPlatformForm::lookupVersion(
                $this->dic->database(),
                $platform_id
            ) === ilLTIAdministrationPlatformForm::VERSION_1P1,
            ARRAY_FILTER_USE_KEY
        );
    }

    private function createLocalRoles(): void
    {
        if (ilObject::_getIdsForTitle('il_lti_global_role', 'role') === []
            || $this->dic->rbac()->review()->getRolesOfObject($this->ref_id, false) !== []) {
            return;
        }

        $type = ilObject::_lookupType($this->ref_id, true);
        $this->createLocalRole('il_lti_learner', 'LTI Learner', $type, ['visible', 'read']);

        if (in_array($type, ['sahs', 'lm', 'svy', 'tst'], true)) {
            $operations = ['visible', 'read', 'read_learning_progress'];
            if ($type === 'svy') {
                $operations[] = 'read_results';
            }
            if ($type === 'tst') {
                $operations[] = 'tst_results';
            }
            $this->createLocalRole('il_lti_instructor', 'LTI Instructor', $type, $operations);
        }
    }

    /**
     * @param array $operations
     */
    private function createLocalRole(string $title, string $description, string $type, array $operations): void
    {
        $role = new ilObjRole();
        $role->setTitle($title);
        $role->setDescription($description . ' of ' . $type . ' obj_no.' . ilObject::_lookupObjectId($this->ref_id));
        $role->create();

        $this->dic->rbac()->admin()->assignRoleToFolder($role->getId(), $this->ref_id, 'y');
        $this->dic->rbac()->admin()->grantPermission(
            $role->getId(),
            ilRbacReview::_getOperationIdsByName($operations),
            $this->ref_id
        );
    }
}
