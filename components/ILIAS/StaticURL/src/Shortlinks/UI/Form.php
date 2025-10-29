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
use ILIAS\UI\Component\Input\Container\Form\Standard;
use Generator;
use ILIAS\GlobalScreen\GUI\Pons;
use ILIAS\StaticURL\Shortlinks\Shortlink\Repository;
use ILIAS\StaticURL\Shortlinks\Shortlink\Shortlink;
use ILIAS\Data\URI;
use ILIAS\StaticURL\Shortlinks\Shortlink\Validator;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class Form implements toComponent
{
    public const string F_ALIAS = 'alias';
    public const string F_ACTIVE = 'active';
    public const string ACTION_TOGGLE = 'toggle';
    public const string ACTION_DELETE = 'delete';
    public const string ACTION_EDIT = 'edit';
    public const string F_TARGET_REF_ID = 'target_ref_id';
    private Factory $ui_factory;
    private Translator $i18n;
    private ServerRequestInterface $request;
    private \ILIAS\Refinery\Factory $refinery;
    private Validator $validator;

    public function __construct(
        Pons $pons,
        private Repository $repository,
        private URI $target,
        private Shortlink $shortlink,
    ) {
        $this->ui_factory = $pons->out()->ui()->factory();
        $this->i18n = $pons->i18n();
        $this->request = $pons->in()->request();
        $this->refinery = $pons->in()->refinery();
        $this->validator = new Validator();
    }

    protected function getFields(): array
    {
        // Tsrget Ref-ID Selection
        $node_retrieval = new \NodeRetrievalGUI();
        $current_ref_id_value = $this->shortlink->getTargetData()['ref_id'] ?? null;
        $tree_select = $this
            ->ui_factory
            ->input()
            ->field()
            ->treeSelect(
                $node_retrieval->getNodeRetrieval(),
                $this->i18n->t(self::F_TARGET_REF_ID),
                $this->i18n->t(self::F_TARGET_REF_ID, 'info'),
            )
            // ->withRequired(true) //Currently not possible due to tree select implementation
            ->withAdditionalTransformation(
                $this->refinery->custom()->constraint(
                    fn($d): bool => !empty($d),
                    $this->i18n->t('target_ref_id_required')
                )
            )
            ->withAdditionalTransformation(
                $this->refinery->custom()->transformation(
                    fn($d): Shortlink => $this->shortlink = $this
                        ->shortlink
                        ->withTargetData(
                            $this
                                ->repository
                                ->typeDataRevolver()
                                ->resolveForRefId(
                                    (int) ($d[0])
                                )
                        )
                )
            );

        if (!empty($current_ref_id_value)) {
            $tree_select = $tree_select->withValue(
                $current_ref_id_value
            );
        }

        // Alias aka Shortlink-name
        $alias = $this
            ->ui_factory
            ->input()
            ->field()
            ->text(
                $this->i18n->t(self::F_ALIAS),
                $this->i18n->t(self::F_ALIAS, 'info')
            )
            ->withValue(
                $this->shortlink->getAlias()
            );

        if (empty($this->shortlink->getId())) {
            $alias = $alias
                ->withAdditionalTransformation(
                    $this->refinery->custom()->constraint(
                        fn($d): bool => !$this->repository->has($d),
                        $this->i18n->t('alias_already_exists')
                    )
                );
        }

        $alias = $alias
            ->withAdditionalTransformation(
                $this->refinery->custom()->constraint(
                    fn($d): bool => $this->validator->isValid($d),
                    $this->i18n->t('alias_invalid')
                )
            )
            ->withAdditionalTransformation(
                $this->refinery->custom()->transformation(
                    fn($d): Shortlink => $this->shortlink = $this->shortlink->withAlias($d)
                )
            );

        // Active status
        $active = $this
            ->ui_factory
            ->input()
            ->field()
            ->checkbox(
                $this->i18n->t(self::F_ACTIVE),
                $this->i18n->t(self::F_ACTIVE, 'info')
            )
            ->withValue(
                $this->shortlink->isActive()
            )
            ->withAdditionalTransformation(
                $this->refinery->custom()->transformation(
                    fn($d): Shortlink => $this->shortlink = $this->shortlink->withActive($d)
                )
            );

        return [
            self::F_ALIAS => $alias,
            self::F_TARGET_REF_ID => $tree_select,
            self::F_ACTIVE => $active
        ];
    }

    protected function getForm(): Standard
    {
        return $this
            ->ui_factory
            ->input()
            ->container()
            ->form()
            ->standard(
                (string) $this->target,
                $this->getFields()
            )->withAdditionalTransformation(
                $this->refinery->custom()->transformation(fn($d): Shortlink => $this->shortlink)
            );
    }

    public function save(): ?Standard
    {
        $form = $this
            ->getForm()
            ->withRequest(
                $this->request
            );

        $data = $form->getData();
        if ($data instanceof Shortlink) {
            $this->repository->store(
                $data
            );
            return null;
        }
        return $form;
    }

    public function get(): \Generator
    {
        yield $this->getForm();
    }

}
