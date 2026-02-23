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

use ILIAS\UI\Component\Input\Container\Form\Standard;
use ILIAS\UI\Factory;
use ILIAS\GlobalScreen\GUI\Input\TokenContainer;
use ILIAS\GlobalScreen\GUI\AbstractPonsGUI;
use ILIAS\GlobalScreen\GUI\Flow\Command;
use ILIAS\GlobalScreen\GUI\Pons;
use ILIAS\StaticURL\Shortlinks\UI\Table;
use ILIAS\StaticURL\Shortlinks\Shortlink\Repository;
use ILIAS\StaticURL\Shortlinks\UI\Form;
use ILIAS\StaticURL\Shortlinks\TargetLinkResolver;
use ILIAS\GlobalScreen\GUI\Input\Input;
use ILIAS\StaticURL\Shortlinks\Shortlink\Shortlink;
use ILIAS\GlobalScreen\GUI\Hasher;
use ILIAS\StaticURL\Shortlinks\Shortlink\RepositoryDB;
use ILIAS\StaticURL\Shortlinks\Shortlink\Target\ILIASTypeDataResolver;
use ILIAS\StaticURL\Configuration;

/**
 * @author            Fabian Schmid <fabian@sr.solutions>
 *
 * @ilCtrl_IsCalledBy ShortlinkAdministrationGUI: ilObjStaticUrlServiceGUI
 */
class ShortlinkAdministrationGUI extends AbstractPonsGUI
{
    use Hasher;

    public const string CMD_FORM = 'form';
    public const string CMD_SAVE = 'save';
    public const string CMD_CONFIRM_DELETE = 'confirmDelete';
    public const string CMD_DELETE = 'delete';
    public const string CMD_CONFIRM_TOGGLE = 'confirmToggle';
    public const string CMD_TOGGLE = 'toggle';
    public const string CMD_SAVE_ORDERING = 'saveOrdering';
    private ilToolbarGUI $toolbar;
    private Factory $ui_factory;
    private Repository $repository;
    private TokenContainer $token;
    private TargetLinkResolver $link_resolver;
    private Configuration $configuration;

    public function __construct(Pons $pons)
    {
        global $DIC;
        parent::__construct($pons);
        $this->toolbar = $pons->out()->toolbar();
        $this->ui_factory = $pons->out()->ui()->factory();
        $this->repository = new RepositoryDB(
            $DIC->database(),
            new ILIASTypeDataResolver(
                $DIC->repositoryTree()
            )
        );
        $this->link_resolver = new TargetLinkResolver(
            $DIC['static_url']->builder(),
            $DIC[\ILIAS\Data\Factory::class],
            $this->ui_factory,
        );
        $this->token = $this->pons->in()->buildToken('alias', 'id');
        $this->configuration = $DIC['static_url.config'];
    }

    private function getForm(): Form
    {
        $id_from_request = $this->pons->in()->getFirstFromRequest($this->token);

        if ($this->repository->hasId($id_from_request)) {
            $shortlink = $this->repository->getById(
                $id_from_request
            );
        } else {
            $shortlink = $this->repository->blank();
        }

        return new Form(
            $this->pons,
            $this->repository,
            $this->pons->flow()->getHereAsURI(self::CMD_SAVE),
            $shortlink
        );
    }

    public function executeCommand(): bool
    {
        if ($this->pons->handle(ilObjStaticUrlServiceGUI::TAB_INDEX)) {
            return true;
        }

        match ($cmd = $this->pons->flow()->getCommand(self::CMD_DEFAULT)) {
            self::CMD_DEFAULT => $this->index(),
            self::CMD_FORM => $this->form(),
            self::CMD_SAVE => $this->save(),
            self::CMD_CONFIRM_DELETE => $this->confirmDelete(),
            self::CMD_DELETE => $this->delete(),
            self::CMD_CONFIRM_TOGGLE => $this->confirmToggle(),
            self::CMD_TOGGLE => $this->toggle(),
            self::CMD_SAVE_ORDERING => $this->saveOrdering(),
            default => $this->pons->out()->outString(
                'Unknown command: ' . $cmd
            ),
        };
        return true;
    }

    public function getTokensToKeep(): array
    {
        return [];
    }

    #[Command('write')]
    private function save(): void
    {
        $form = $this->getForm();
        if (($out_form = $form->save()) instanceof Standard) {
            $this->pons->out()->out(
                $out_form
            );
            return;
        }
        $this
            ->pons
            ->out()
            ->success(
                $this->pons->i18n()->t(
                    'stus_stored_sucessfully'
                )
            );

        $this
            ->pons
            ->flow()
            ->redirect(
                self::CMD_DEFAULT
            );
    }

    #[Command('write')]
    private function form(): void
    {
        $form = $this->getForm();

        $this->pons->in()->keep(
            $this->token
        );

        $this->pons->out()->outAsyncAsModal(
            $this->pons->i18n()->t('shortlink'),
            $this->pons->flow()->getHereAsURI(self::CMD_SAVE),
            ...iterator_to_array($form->get())
        );
    }

    #[Command('read')]
    private function index(): void
    {
        if ($this->pons->access()->hasUserPermissionTo('write')) {
            $this->toolbar->addComponent(
                $modal = $this->ui_factory->modal()->roundtrip(
                    $this->pons->i18n()->t('create_shortlink'),
                    null,
                    [],
                    null
                )->withAsyncRenderUrl(
                    (string) $this->pons->flow()->getHereAsURI(self::CMD_FORM),
                )
            );

            $this->toolbar->addComponent(
                $button = $this->ui_factory->button()->primary(
                    $this->pons->i18n()->t('create_shortlink'),
                    (string) $this->pons->flow()->getHereAsURI(self::CMD_FORM), // TODO make async again
                )->withOnClick(
                    $modal->getShowSignal()
                )
            );
        }

        $table = new Table(
            $this->pons,
            $this->repository,
            $this->link_resolver,
            $this->pons->flow()->getHereAsURI(self::CMD_SAVE_ORDERING),
            $this->token,
            $this->configuration,
            $this->pons->access()->hasUserPermissionTo('write'),
        );

        $this->pons->out()->out(...iterator_to_array($table->get()));
    }

    #[Command('write')]
    private function confirm(
        string $target_command,
        string $title,
        string $message,
        string $button_title,
    ): void {
        $items = [];
        $from_request = $this->getAllIds();

        $this->pons->out()->nok();
        $this->pons->out()->ok();

        foreach ($from_request as $id) {
            $shortlink = $this->repository->getById($id);
            if ($shortlink === null) {
                continue;
            }
            $id = $this->hash($id);

            $items[] = $this->ui_factory->modal()->interruptiveItem()->standard(
                $id,
                $shortlink->getAlias(),
            );
        }

        $this->pons->out()->outAsyncAsConfirmation(
            $title,
            $message,
            $button_title,
            $this->pons->flow()->getHereAsURI($target_command),
            ...$items
        );
    }

    #[Command('write')]
    private function confirmDelete(): void
    {
        $this->confirm(
            self::CMD_DELETE,
            $this->pons->i18n()->t('delete_shortlink'),
            $this->pons->i18n()->t('delete_shortlink_msg'),
            $this->pons->i18n()->t('delete')
        );
    }

    #[Command('write')]
    private function delete(): void
    {
        $from_request = $this->getAllIds();
        foreach ($from_request as $id) {
            $shortlink = $this->repository->getById($id);
            if ($shortlink === null) {
                continue;
            }
            $this->repository->delete($shortlink);
        }

        $this->pons->out()->success($this->pons->i18n()->t('shortlinks_deleted'));
        $this->pons->flow()->redirect(self::CMD_DEFAULT);
    }

    #[Command('write')]
    private function confirmToggle(): void
    {
        $this->confirm(
            self::CMD_TOGGLE,
            $this->pons->i18n()->t('toggle_shortlink'),
            $this->pons->i18n()->t('toggle_shortlink_msg'),
            $this->pons->i18n()->t('toggle')
        );
    }

    #[Command('write')]
    private function toggle(): void
    {
        $from_request = $this->getAllIds();
        foreach ($from_request as $id) {
            $shortlink = $this->repository->getById($id);
            if ($shortlink === null) {
                continue;
            }
            $shortlink = $shortlink->withActive(
                !$shortlink->isActive()
            );

            $this->repository->store($shortlink);
        }

        $this->pons->out()->success($this->pons->i18n()->t('shortlinks_toggled'));
        $this->pons->flow()->redirect(self::CMD_DEFAULT);
    }

    #[Command('write')]
    private function saveOrdering(): void
    {
        foreach ($this->pons->in()->request()->getParsedBody() as $hashed_id => $position) {
            $item = $this->repository->getById($this->unhash($hashed_id));
            $item = $item->withPosition((int) $position);
            $this->repository->store($item);
        }
        $this->pons->out()->success($this->pons->i18n()->translate('order_saved'));
        $this->pons->flow()->redirect(self::CMD_DEFAULT);
    }

    private function getAllIds(): array
    {
        $from_request = $this->pons->in()->getAllFromRequest($this->token);
        if (($from_request[0] ?? null) === Input::ALL_OBJECTS) {
            return array_map(
                static fn(Shortlink $s): string => $s->getId(),
                iterator_to_array($this->repository->getAll())
            );
        }
        return array_unique($from_request);
    }

}
