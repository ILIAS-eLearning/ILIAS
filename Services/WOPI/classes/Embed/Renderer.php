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

namespace ILIAS\Services\WOPI\Embed;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class Renderer
{
    private \ILIAS\UI\Factory $ui_factory;

    public function __construct(
        private EmbeddedApplication $embedded_application
    ) {
        global $DIC;
        $this->ui_factory = $DIC->ui()->factory();
    }

    public function getComponent(): \ILIAS\UI\Component\Component
    {
        $tpl = new \ilTemplate('tpl.wopi_container.html', true, true, 'Services/WOPI');
        //$tpl->setVariable('EDITOR_URL', (string) $this->embedded_application->getActionLauncherURL());
        // --- Language forwarding (generic BCP47) ---------------------------------
        global $DIC;
	$lang_key = $DIC->language()->getLangKey() ?: 'en'; // e.g. de, en, fr-CH
	if (strpos($lang_key, '-') !== false) {
    	$locale = $lang_key; // already BCP47-like
	} else {
    	$lang_key = strtolower($lang_key);
    	$locale = $lang_key . '-' . strtoupper($lang_key);
	}
	$ui = rawurlencode($locale);

	$editor_url = (string) $this->embedded_application->getActionLauncherURL();
	$separator = (strpos($editor_url, '?') === false) ? '?' : '&';
	$editor_url .= $separator . 'ui=' . $ui;

	$tpl->setVariable('EDITOR_URL', $editor_url);


	$tpl->setVariable('INLINE', (string) (int) $this->embedded_application->isInline());
        $tpl->setVariable('TOKEN', (string) $this->embedded_application->getToken());
        $tpl->setVariable('TTL', (string) (time() + $this->embedded_application->getTTL()) * 1000); // in milliseconds

        return $this->ui_factory->legacy($tpl->get());
    }
}
