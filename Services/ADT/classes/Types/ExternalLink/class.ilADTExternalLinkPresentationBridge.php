<?php

declare(strict_types=1);

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

/**
 * external link presentation bridge
 * @author  Stefan Meyer <meyer@leifos.com>
 * @ingroup ServicesADT
 */
class ilADTExternalLinkPresentationBridge extends ilADTPresentationBridge
{
    /**
     * Is valid type
     * @param ilADT $a_adt
     * @return bool
     */
    protected function isValidADT(ilADT $a_adt): bool
    {
        return $a_adt instanceof ilADTExternalLink;
    }

    /**
     * Get html
     * @return string
     */
    public function getHTML(): string
    {
        if ($this->getADT()->isNull()) {
            return '';
        }

        $presentation_value = $this->getADT()->getUrl();
        $presentation_clickable = ilUtil::makeClickable($presentation_value);

        if (!strlen($this->getADT()->getTitle())) {
            return $this->decorate($presentation_clickable);
        }

        /*
         * BT 35874: Until the refinery (or some other service) can provide
         * links with titles, we have to do some surgery here.
         */
        $presentation_clickable = preg_replace(
            '/>.*<\/a>/',
            '>' . $this->getADT()->getTitle() . '</a>',
            $presentation_clickable
        );
        return $this->decorate($presentation_clickable);
    }

    /**
     * Get soratable
     * @return
     */
    public function getSortable()
    {
        if (!$this->getADT()->isNull()) {
            return $this->getADT()->getUrl();
        }
        return '';
    }
}
