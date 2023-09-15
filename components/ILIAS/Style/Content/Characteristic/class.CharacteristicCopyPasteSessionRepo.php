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

namespace ILIAS\Style\Content;

use ilSession;
use stdClass;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class CharacteristicCopyPasteSessionRepo
{
    protected const SESSION_KEY = "sty_copy";

    protected Session $session;

    public function __construct(Session $session = null)
    {
        $this->session = ($session)
            ?: new class () implements Session {
                public function set(string $key, string $value): void
                {
                    ilSession::set($key, $value);
                }

                public function get(string $key): string
                {
                    return (string) ilSession::get($key);
                }

                public function clear(string $key): void
                {
                    ilSession::clear($key);
                }
            };
    }

    /**
     * Set characteristics
     */
    public function set(int $style_id, string $style_type, array $characteristics): void
    {
        $style_cp = implode("::", $characteristics);
        $style_cp = $style_id . ":::" . $style_type . ":::" . $style_cp;
        $this->session->set(self::SESSION_KEY, $style_cp);
    }

    public function getData(): stdClass
    {
        $st_c = explode(":::", $this->getValue());
        $data = new stdClass();
        $data->style_id = $st_c[0] ?? 0;
        $data->style_type = $st_c[1] ?? "";
        $data->characteristics = explode("::", $st_c[2] ?? "");
        return $data;
    }

    protected function getValue(): string
    {
        return $this->session->get(self::SESSION_KEY);
    }

    public function hasEntries(string $style_type): bool
    {
        $val = $this->getValue();
        if ($val != "") {
            $style_cp = explode(":::", $val);
            if ($style_cp[1] == $style_type) {
                return true;
            }
        }
        return false;
    }

    public function clear(): void
    {
        $this->session->clear(self::SESSION_KEY);
    }
}
