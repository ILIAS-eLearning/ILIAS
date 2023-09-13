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

class Resolver
{
    /**
     * Resolves dependencies of all components. This is unambigous for all types of
     * dependencies but the use/implement-pair. If there would be ambiguities, these
     * can be disambiguated by the first argument.
     *
     * The structure of the first argument is as such: keys are components that use
     * services ("dependant") that need disambiguation, value for each dependant is
     * an array where the key is the definition ("dependency") and the value is the
     * implementation ("implementation") to be used.
     *
     * The entry "*" for the dependant will define fallbacks to be used for all
     * components that have no explicit disambiguation.
     *
     * So, the array might look as such:
     *
     * [
     *    "*" => [
     *      "ILIAS\Logger\Logger" => ILIAS\Logger\DBLogger
     *    ],
     *    "ILIAS\Database\DB" => [
     *      "ILIAS\Logger\Logger" => ILIAS\Logger\StdErrLogger
     *    ]
     * ]
     *
     * @param array<string, array<string, string>> $disambiguation
     * @param OfComponent[]
     * @return OfComponent[]
     */
    public function resolveDependencies(array $disambiguation, OfComponent ...$components): array
    {
        foreach ($components as $component) {
            foreach ($component->getInDependencies() as $d) {
                switch ($d->getType()) {
                    case InType::PULL:
                        $this->resolvePull($d, $components);
                        break;
                    case InType::SEEK:
                        $this->resolveSeek($d, $components);
                        break;
                    case InType::USE:
                        $this->resolveUse($component, $disambiguation, $d, $components);
                        break;
                }
            }
        }

        return $components;
    }

    protected function resolvePull(In $in, array &$others): void
    {
        $candidate = null;

        foreach ($others as $other) {
            if ($other->offsetExists("PROVIDE: " . $in->getName())) {
                if (!is_null($candidate)) {
                    throw new \LogicException(
                        "Dependency {$in->getName()} is provided (at least) twice."
                    );
                }
                // For PROVIDEd dependencies, there only ever is one implementation.
                $candidate = $other["PROVIDE: " . $in->getName()][0];
            }
        }

        if (is_null($candidate)) {
            throw new \LogicException("Could not resolve dependency for: " . (string) $in);
        }

        $in->addResolution($candidate);
    }

    protected function resolveSeek(In $in, array &$others): void
    {
        foreach ($others as $other) {
            if ($other->offsetExists("CONTRIBUTE: " . $in->getName())) {
                // For CONTRIBUTEd, we just use all contributions.
                foreach ($other["CONTRIBUTE: " . $in->getName()] as $o) {
                    $in->addResolution($o);
                }
            }
        }
    }

    protected function resolveUse(OfComponent $component, array &$disambiguation, In $in, array &$others): void
    {
        $candidates = [];

        foreach ($others as $other) {
            if ($other->offsetExists("IMPLEMENT: " . $in->getName())) {
                // For IMPLEMENTed dependencies, we need to make choice.
                $candidates[] = $other["IMPLEMENT: " . $in->getName()];
            }
        }

        $candidates = array_merge(...$candidates);

        if (empty($candidates)) {
            throw new \LogicException("Could not resolve dependency for: " . (string) $in);
        }

        if (count($candidates) === 1) {
            $in->addResolution($candidates[0]);
            return;
        }

        $preferred_class = $this->disambiguate($component, $disambiguation, $in);
        if (is_null($preferred_class)) {
            throw new \LogicException(
                "Dependency {$in->getName()} is provided (at least) twice, " .
                "no disambiguation for {$component->getComponentName()}."
            );
        }
        foreach ($candidates as $candidate) {
            if ($candidate->aux["class"] === $preferred_class) {
                $in->addResolution($candidate);
                return;
            }
        }
        throw new \LogicException(
            "Dependency $preferred_class for service {$in->getName()} " .
            "for {$component->getComponentName()} could not be located."
        );
    }

    protected function disambiguate(OfComponent $component, array &$disambiguation, In $in): ?string
    {
        $service_name = (string) $in->getName();
        foreach ([$component->getComponentName(), "*"] as $c) {
            if (isset($disambiguation[$c]) && isset($disambiguation[$c][$service_name])) {
                return $disambiguation[$c][$service_name];
            }
        }
        return null;
    }
}
