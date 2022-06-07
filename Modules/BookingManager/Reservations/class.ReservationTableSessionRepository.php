<?php declare(strict_types = 1);

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

namespace ILIAS\BookingManager\Reservation;

/**
 * Reservation table related session data
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ReservationTableSessionRepository
{
    public const KEY_BASE = "repo_clip";

    public function __construct()
    {
    }

    public function setObjectFilter(string $form_id, string $filter_value) : void
    {
        $form_array = [];
        if (\ilSession::has("form_" . $form_id)) {
            $form_array = \ilSession::get("form_" . $form_id);
        }
        $form_array["object"] = $filter_value;
        \ilSession::set("form_" . $form_id, $form_array);
    }

    public function setFromToFilter(string $form_id, string $filter_value) : void
    {
        $form_array = [];
        if (\ilSession::has("form_" . $form_id)) {
            $form_array = \ilSession::get("form_" . $form_id);
        }
        $form_array["fromto"] = $filter_value;
        \ilSession::set("form_" . $form_id, $form_array);
    }

    public function hasFromToFilter(string $form_id) : bool
    {
        if (\ilSession::has("form_" . $form_id)) {
            $form_array = \ilSession::get("form_" . $form_id);
            return isset($form_array["fromto"]);
        }
        return false;
    }
}
