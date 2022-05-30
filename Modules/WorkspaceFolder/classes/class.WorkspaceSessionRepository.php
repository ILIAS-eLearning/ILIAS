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

namespace ILIAS\PersonalWorkspace;

/**
 * Workspace clipboard session repository
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class WorkspaceSessionRepository
{
    protected const BASE_SESSION_KEY = 'wsp_';

    public function __construct()
    {
    }

    public function clearClipboard() : void
    {
        \ilSession::clear(self::BASE_SESSION_KEY . "_clip_wsp2repo");
        \ilSession::clear(self::BASE_SESSION_KEY . "_clip_source_ids");
        \ilSession::clear(self::BASE_SESSION_KEY . "_clip_cmd");
        \ilSession::clear(self::BASE_SESSION_KEY . "_clip_shared");
    }

    public function isClipboardEmpty() : bool
    {
        return !($this->getClipboardCmd() !== "" && count($this->getClipboardSourceIds()) > 0);
    }

    public function setClipboardWsp2Repo(bool $wsp2repo) : void
    {
        \ilSession::set(self::BASE_SESSION_KEY . "_clip_wsp2repo", $wsp2repo);
    }

    public function getClipboardWsp2Repo() : bool
    {
        return \ilSession::get(self::BASE_SESSION_KEY . "_clip_wsp2repo") ?? false;
    }

    public function setClipboardSourceIds(array $ids) : void
    {
        \ilSession::set(self::BASE_SESSION_KEY . "_clip_source_ids", $ids);
    }

    public function getClipboardSourceIds() : array
    {
        return \ilSession::get(self::BASE_SESSION_KEY . "_clip_source_ids") ?? [];
    }

    public function setClipboardCmd(string $cmd) : void
    {
        \ilSession::set(self::BASE_SESSION_KEY . "_clip_cmd", $cmd);
    }

    public function getClipboardCmd() : string
    {
        return \ilSession::get(self::BASE_SESSION_KEY . "_clip_cmd") ?? "";
    }

    public function setClipboardShared(bool $shared) : void
    {
        \ilSession::set(self::BASE_SESSION_KEY . "_clip_shared", $shared);
    }

    public function getClipboardShared() : bool
    {
        return \ilSession::get(self::BASE_SESSION_KEY . "_clip_shared") ?? false;
    }
}
