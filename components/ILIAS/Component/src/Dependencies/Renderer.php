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

namespace ILIAS\Component\Dependencies;

/**
 * This takes a (hopefully resolved...) dependency tree and renders it in PHP to
 * be used for initialisation.
 */
class Renderer
{
    public function render(OfComponent ...$components): string
    {
        $component_lookup = array_flip(
            array_map(
                fn($c) => $c->getComponentName(),
                $components
            )
        );

        return
            $this->renderHeader() .
            join("\n", array_map(
                fn($c) => $this->renderComponent($component_lookup, $c),
                $components
            ));
    }

    protected function renderHeader(): string
    {
        return <<<PHP
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

\$null_dic = new ILIAS\Component\Dependencies\NullDIC();


PHP;
    }

    protected function renderComponent(array $component_lookup, OfComponent $component): string
    {
        $me = $component_lookup[$component->getComponentName()];
        $use = $this->renderUse($component_lookup, $component);
        $seek = $this->renderSeek($component_lookup, $component);
        $pull = $this->renderPull($component_lookup, $component);
        return <<<PHP

\$component_$me = new {$component->getComponentName()}();

\$implement_$me = new ILIAS\Component\Dependencies\RenamingDIC(new Pimple\Container());
\$use = new Pimple\Container();{$use}
\$contribute_$me = new ILIAS\Component\Dependencies\RenamingDIC(new Pimple\Container());
\$seek = new Pimple\Container();{$seek}
\$provide_$me = new Pimple\Container();
\$pull = new Pimple\Container();{$pull}
\$internal = new Pimple\Container();

\$component_{$me}->init(\$null_dic, \$implement_$me, \$use, \$contribute_$me, \$seek, \$provide_$me, \$pull, \$internal);

PHP;
    }

    protected function renderUse(array $component_lookup, OfComponent $component): string
    {
        $use = "";
        foreach ($component->getInDependenciesOf(InType::USE) as $in) {
            $r = $in->getResolvedBy()[0];
            $p = $r->aux["position"];
            $o = $component_lookup[$r->getComponent()->getComponentName()];
            $use .= "\n" . <<<PHP
\$use[{$in->getName()}::class] = fn() => \$implement_{$o}[{$r->getName()}::class . "_{$p}"];
PHP;
        }
        return $use;
    }

    protected function renderSeek(array $component_lookup, OfComponent $component): string
    {
        $seek = "";
        foreach ($component->getInDependenciesOf(InType::SEEK) as $in) {
            $rs = $in->getResolvedBy();
            $u = [];
            $a = "";
            foreach ($rs as $r) {
                $p = $r->aux["position"];
                $o = $component_lookup[$r->getComponent()->getComponentName()];
                $u[] = "\$contribute_{$o}";
                $a .= "\n" . <<<PHP
        \$contribute_{$o}[{$r->getName()}::class . "_{$p}"],
PHP;
            }
            $u = join(", ", array_unique($u));
            $seek .= "\n" . <<<PHP
\$seek[{$in->getName()}::class] = function () use ({$u}) {
    return [{$a}
    ];
};
PHP;
        }
        return $seek;
    }

    protected function renderPull(array $component_lookup, OfComponent $component): string
    {
        $pull = "";
        foreach ($component->getInDependenciesOf(InType::PULL) as $in) {
            $r = $in->getResolvedBy()[0];
            $o = $component_lookup[$r->getComponent()->getComponentName()];
            $pull .= "\n" . <<<PHP
\$pull[{$in->getName()}::class] = fn() => \$provide_{$o}[{$r->getName()}::class];
PHP;
        }
        return $pull;
    }
}
