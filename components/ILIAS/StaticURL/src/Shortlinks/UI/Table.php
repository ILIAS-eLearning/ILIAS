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

namespace ILIAS\StaticURL\Shortlinks\UI;

use ILIAS\UI\Factory;
use ILIAS\GlobalScreen\GUI\I18n\Translator;
use Psr\Http\Message\ServerRequestInterface;
use Generator;
use ILIAS\GlobalScreen\GUI\Pons;
use ILIAS\StaticURL\Shortlinks\Shortlink\Repository;
use ILIAS\UI\Component\Table\OrderingRetrieval;
use ILIAS\UI\Component\Table\OrderingRowBuilder;
use ILIAS\Data\URI;
use ILIAS\GlobalScreen\GUI\Input\TokenContainer;
use ILIAS\StaticURL\Shortlinks\TargetLinkResolver;
use ILIAS\StaticURL\Configuration;
use ILIAS\StaticURL\Config;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class Table implements OrderingRetrieval, toComponent
{
    public const string F_ALIAS = 'alias';
    public const string F_ACTIVE = 'active';
    public const string ACTION_TOGGLE = 'toggle';
    public const string ACTION_DELETE = 'delete';
    public const string ACTION_EDIT = 'edit';
    public const string F_PREFIX = 'prefix';
    public const string F_TARGET_LINK = 'target_link';
    private Factory $ui_factory;
    private Translator $i18n;
    private ServerRequestInterface $request;
    private string $link_prefix;

    public function __construct(
        private Pons $pons,
        private Repository $repository,
        private TargetLinkResolver $link_resolver,
        private URI $ordering_target,
        private TokenContainer $token,
        Configuration $config,
        private bool $can_edit = false,
    ) {
        $this->ui_factory = $pons->out()->ui()->factory();
        $this->i18n = $pons->i18n();
        $this->request = $pons->in()->request();
        $this->link_prefix = $config->get(Config::ULTRA_SHORT)
            ? ''
            : $config->get(Config::STATIC_LINK_ENDPOINT) . $config->get(Config::SHORTLINK_NAMESPACE) . '/';
    }

    public function getRows(
        OrderingRowBuilder $row_builder,
        array $visible_column_ids
    ): Generator {
        foreach ($this->repository->getAll() as $shortlink) {
            /**
             * @var $shortlink Shortlink
             */

            yield $row_builder->buildOrderingRow(
                $this->pons->in()->hash($shortlink->getId()),
                [
                    self::F_PREFIX => $this->link_prefix,
                    self::F_ALIAS => $this
                        ->ui_factory
                        ->link()
                        ->standard(
                            $shortlink->getAliasForPresentation($this->link_prefix),
                            $this->link_prefix . $shortlink->getAlias()
                        )
                        ->withOpenInNewViewport(true),
                    self::F_TARGET_LINK => $this
                        ->link_resolver
                        ->resolveLink($shortlink)
                        ?->withOpenInNewViewport(true),
                    self::F_ACTIVE => $shortlink->isActive(),
                ]
            )->withDisabledAction(
                self::ACTION_EDIT,
                !$this->can_edit
            )->withDisabledAction(
                self::ACTION_TOGGLE,
                !$this->can_edit
            )->withDisabledAction(
                self::ACTION_DELETE,
                !$this->can_edit
            );
        }
    }

    public function get(): \Generator
    {
        if ($this->can_edit) {
            $actions = [
                self::ACTION_TOGGLE => $this
                    ->ui_factory
                    ->table()
                    ->action()
                    ->standard(
                        $this->i18n->t(self::ACTION_TOGGLE, 'action'),
                        $this->token->builder()->withURI(
                            $this->pons->flow()->getHereAsURI(\ShortlinkAdministrationGUI::CMD_CONFIRM_TOGGLE)
                        ),
                        $this->token->token()
                    )
                    ->withAsync(true),
                self::ACTION_EDIT => $this
                    ->ui_factory
                    ->table()
                    ->action()
                    ->standard(
                        $this->i18n->t(self::ACTION_EDIT, 'action'),
                        $this->token->builder()->withURI(
                            $this->pons->flow()->getHereAsURI(\ShortlinkAdministrationGUI::CMD_FORM)
                        ),
                        $this->token->token()
                    )
                    ->withAsync(true),
                self::ACTION_DELETE => $this
                    ->ui_factory
                    ->table()
                    ->action()
                    ->standard(
                        $this->i18n->t(self::ACTION_DELETE, 'action'),
                        $this->token->builder()->withURI(
                            $this->pons->flow()->getHereAsURI(\ShortlinkAdministrationGUI::CMD_CONFIRM_DELETE)
                        ),
                        $this->token->token()
                    )
                    ->withAsync(true),
            ];
        } else {
            $actions = [];
        }

        yield $this
            ->ui_factory
            ->table()
            ->ordering(
                $this,
                $this->ordering_target,
                $this->i18n->t('shortlinks'),
                [
                    /*self::F_PREFIX => $this->ui_factory->table()->column()->text(
                        $this->i18n->t(self::F_PREFIX),
                    ),*/
                    self::F_ALIAS => $this->ui_factory->table()->column()->link(
                        $this->i18n->t(self::F_ALIAS),
                    ),
                    self::F_TARGET_LINK => $this->ui_factory->table()->column()->link(
                        $this->i18n->t(self::F_TARGET_LINK),
                    ),
                    self::F_ACTIVE => $this->ui_factory->table()->column()->boolean(
                        $this->i18n->t(self::F_ACTIVE),
                        $this->pons->out()->ok(),
                        $this->pons->out()->nok(),
                    )
                ]
            )
            ->withOrderingDisabled(
                !$this->can_edit
            )
            ->withRequest($this->request)
            ->withActions($actions);
    }

}
