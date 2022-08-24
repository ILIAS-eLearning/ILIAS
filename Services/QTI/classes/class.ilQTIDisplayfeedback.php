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
 ********************************************************************
 */

/**
* QTI displayfeedback class
*
* @author Helmut Schottmüller <hschottm@gmx.de>
* @version $Id$
*
* @package assessment
*/
class ilQTIDisplayfeedback
{
    public ?string $feedbacktype = null;
    public string $linkrefid = '';
    public ?string $content = null;

    public function setFeedbacktype(string $a_feedbacktype): void
    {
        $this->feedbacktype = $a_feedbacktype;
    }

    public function getFeedbacktype(): ?string
    {
        return $this->feedbacktype;
    }

    public function setLinkrefid(string $a_linkrefid): void
    {
        $this->linkrefid = $a_linkrefid;
    }

    public function getLinkrefid(): string
    {
        return $this->linkrefid;
    }

    public function setContent(string $a_content): void
    {
        $this->content = $a_content;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }
}
