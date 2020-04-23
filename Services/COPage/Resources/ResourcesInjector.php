<?php

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\COPage;

/**
 * Injects resources into a template
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ResourcesInjector
{
    /**
     * @var ResourcesCollector
     */
    protected $collector;

    /**
     * ResourcesInjector constructor.
     * @param ResourcesCollector $collector
     */
    public function __construct(ResourcesCollector $collector)
    {
        $this->collector = $collector;
    }

    /**
     * Inject into template
     * @param \ilGlobalTemplateInterface $tpl
     */
    public function inject(\ilGlobalTemplateInterface $tpl)
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
