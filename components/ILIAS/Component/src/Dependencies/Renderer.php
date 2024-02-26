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
            )) .
            $this->renderEntryPointsSection($component_lookup, ...$components);
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

require_once(__DIR__ . "/../vendor/composer/vendor/autoload.php");

function entry_point(string \$name)
{
    \$null_dic = new ILIAS\Component\Dependencies\NullDIC();
    \$implement = new Pimple\Container();
    \$contribute = new Pimple\Container();
    \$provide = new Pimple\Container();


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

    \$implement[$me] = new ILIAS\Component\Dependencies\RenamingDIC(new Pimple\Container());
    \$use = new Pimple\Container();{$use}
    \$contribute[$me] = new ILIAS\Component\Dependencies\RenamingDIC(new Pimple\Container());
    \$seek = new Pimple\Container();{$seek}
    \$provide[$me] = new Pimple\Container();
    \$pull = new Pimple\Container();{$pull}
    \$internal = new Pimple\Container();

    \$component_{$me}->init(\$null_dic, \$implement[$me], \$use, \$contribute[$me], \$seek, \$provide[$me], \$pull, \$internal);

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
    \$use[{$in->getName()}::class] = fn() => \$implement[{$o}][{$r->getName()}::class . "_{$p}"];
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
        \$contribute[$o][{$r->getName()}::class . "_{$p}"],
PHP;
            }
            $u = join(", ", array_unique($u));
            $seek .= "\n" . <<<PHP
    \$seek[{$in->getName()}::class] = fn() => [{$a}
    ];
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
    \$pull[{$in->getName()}::class] = fn() => \$provide[{$o}][{$r->getName()}::class];
PHP;
        }
        return $pull;
    }

    protected function renderEntryPointsSection(array $component_lookup, OfComponent ...$components): string
    {
        $entry_points = "";
        foreach ($components as $component) {
            $p = $component_lookup[$component->getComponentName()];
            $entry_points .= $this->renderEntryPoints($p, $component);
        }
        return <<<PHP


    \$entry_points = [{$entry_points}
    ];

    if (!isset(\$entry_points[\$name])) {
        throw new \\LogicException("Unknown entry point: \$name.");
    }

    \$entry_points[\$name]()->enter();
}

PHP;
    }

    protected function renderEntryPoints(int $me, OfComponent $component): string
    {
        $entry_points = "";
        foreach ($component->getOutDependenciesOf(OutType::CONTRIBUTE) as $out) {
            if ($out->getName() !== \ILIAS\Component\EntryPoint::class) {
                continue;
            }
            $p = $out->aux["position"];
            $n = str_replace("\"", "\\\"", $out->aux["entry_point_name"]);
            $entry_points .= "\n" . <<<PHP
        "$n" => fn() => \$contribute[{$me}][ILIAS\Component\EntryPoint::class . "_{$p}"],
PHP;
        }

        return $entry_points;
        PHP;
    }
}
