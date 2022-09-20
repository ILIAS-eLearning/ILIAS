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

namespace ILIAS\COPage\Editor\Components\Paragraph;

use ILIAS\COPage\Editor\Server;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ParagraphResponseFactory
{
    protected \ilLogger $log;

    public function __construct()
    {
        $this->log = \ilLoggerFactory::getLogger("copg");
    }

    /**
     * Get response data object
     * @param bool|array|string $updated
     * @throws \ilDateTimeException
     */
    public function getResponseObject(
        \ilPageObjectGUI $page_gui,
        $updated,
        string $pcid
    ): Server\Response {
        $error = null;
        $rendered_content = null;
        $last_change = null;

        if ($updated !== true) {
            $this->log->debug(print_r($updated, true));
            if (is_array($updated)) {
                $error = "";
                foreach ($updated as $msg) {
                    if (is_array($msg)) {
                        $error .= implode("<br />", $msg);
                    } else {
                        $error .= $msg;
                    }
                }
            } elseif (is_string($updated)) {
                $error = $updated;
            } else {
                $error = print_r($updated, true);
            }
        } else {
            $page_gui->setDefaultLinkXml(); // fixes #31087
            $rendered_content = $this->getParagraphOutput($page_gui, $pcid);
            $last_change = $page_gui->getPageObject()->getLastChange();
        }

        $data = new \stdClass();
        $data->renderedContent = $rendered_content;
        $data->error = $error;
        $data->last_update = null;
        if ($last_change) {
            $lu = new \ilDateTime($last_change, IL_CAL_DATETIME);
            \ilDatePresentation::setUseRelativeDates(false);
            $data->last_update = \ilDatePresentation::formatDate($lu, true);
        }

        return new Server\Response($data);
    }

    /**
     * Get response data object for multi actions
     * @param bool|array|string $updated
     * @throws \ilDateTimeException
     */
    public function getResponseObjectMulti(
        \ilPageObjectGUI $page_gui,
        $updated,
        array $pcids
    ): Server\Response {
        $error = null;
        $rendered_content = null;
        $last_change = null;

        if ($updated !== true) {
            if (is_array($updated)) {
                $error = implode("<br />", $updated);
            } elseif (is_string($updated)) {
                $error = $updated;
            } else {
                $error = print_r($updated, true);
            }
        } else {
            foreach ($pcids as $pcid) {
                $rendered_content[$pcid] = $this->getParagraphOutput($page_gui, $pcid);
            }
            $last_change = $page_gui->getPageObject()->getLastChange();
        }

        $data = new \stdClass();
        $data->renderedContent = $rendered_content;
        $data->error = $error;
        $data->last_update = null;
        if ($last_change) {
            $lu = new \ilDateTime($last_change, IL_CAL_DATETIME);
            \ilDatePresentation::setUseRelativeDates(false);
            $data->last_update = \ilDatePresentation::formatDate($lu, true);
        }

        return new Server\Response($data);
    }

    protected function getParagraphOutput(
        \ilPageObjectGUI $page_gui,
        string $pcid
    ): string {
        /** @var \ILIAS\DI\Container $DIC */
        global $DIC;

        $page_gui->setRawPageContent(true);
        $page_gui->setAbstractOnly(true, $pcid);
        $page_gui->setOutputMode(\ilPageObjectGUI::PRESENTATION);
        $page_gui->setEnabledHref(false);
        //$html = $page_gui->showPage();
        $html = $DIC->ctrl()->getHTML($page_gui);

        $pos = strrpos($html, "<!--COPage-PageTop-->");
        if ($pos > 0) {
            $html = substr($html, $pos + 21);
        }
        return $html;
    }
}
