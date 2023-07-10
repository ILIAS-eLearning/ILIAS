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

namespace ILIAS\COPage;

/**
 * Injects resources into a template
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ResourcesInjector
{
    protected ResourcesCollector $collector;

    public function __construct(ResourcesCollector $collector)
    {
        $this->collector = $collector;
    }

    /**
     * Inject into template
     */
    public function inject(\ilGlobalTemplateInterface $tpl): void
    {
        $resource_collector = $this->collector;

        foreach ($resource_collector->getCssFiles() as $css) {
            $tpl->addCss($css);
        }

        foreach ($resource_collector->getJavascriptFiles() as $js) {
            $batch = 3;
            if (is_int(strpos($js, "jquery"))) {
                $batch = 1;
            }
            if (is_int(strpos($js, "Basic.js"))) {
                $batch = 2;
            }
            $tpl->addJavaScript($js, false, $batch);
        }

        foreach ($resource_collector->getOnloadCode() as $code) {
            $tpl->addOnLoadCode($code);
        }
    }
}
