<?php

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\COPage\Editor\Components\Paragraph;

use ILIAS\COPage\Editor\Server;

/**
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ParagraphResponseFactory
{
    /**
     * @var \ilLogger
     */
    protected $log;
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->log = \ilLoggerFactory::getLogger("copg");
    }

    /**
     * Get reponse data object
     * @param \ilPageObjectGUI $page_gui
     * @param                  $updated
     * @param string           $pcid
     * @return Server\Response
     */
    public function getResponseObject(\ilPageObjectGUI $page_gui, $updated, string $pcid) : Server\Response
    {
        $error = null;
        $rendered_content = null;
        $last_change = null;

        if ($updated !== true) {
            $this->log->debug(print_r($updated, true));
            if (is_array($updated)) {
                $error = "";
                foreach ($updated as $msg) {
                    if (is_array($msg)) {
                        $error.= implode("<br />", $msg);
                    } else {
                        $error.= (string) $msg;
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
     * Get reponse data object
     * @param \ilPageObjectGUI $page_gui
     * @param                  $updated
     * @param string           $pcid
     * @return Server\Response
     */
    public function getResponseObjectMulti(\ilPageObjectGUI $page_gui, $updated, array $pcids) : Server\Response
    {
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

    /**
     * Get paragraph output
     * @param
     * @return
     */
    protected function getParagraphOutput(\ilPageObjectGUI $page_gui, $pcid)
    {
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
