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

namespace ILIAS\COPage\Link;

class ExtLinkMapper
{
    protected array $ilias_url;

    public function __construct(
        protected \ilObjectDefinition $object_def,
        string $ilias_http_path,
        protected array $mapping,
        protected $client_id = ""
    ) {
        $this->ilias_url = parse_url($ilias_http_path);
        if ($client_id === "") {
            $this->client_id = CLIENT_ID;
        }
    }

    public function getRefId(string $href): int
    {
        $url = parse_url($href);

        // only handle links on same host
        if (($url["host"] ?? "") !== "" && $url["host"] !== $this->ilias_url["host"]) {
            return 0;
        }

        $ref_id = 0;

        // get parameters
        $par = [];
        $type = "";

        // links ending with .html, e.g. goto_client_cat_581.html
        if (substr($href, strlen($href) - 5) === ".html") {
            $parts = explode(
                "_",
                basename(
                    substr($url["path"], 0, strlen($url["path"]) - 5)
                )
            );
            if (array_shift($parts) !== "goto") {
                return 0;
            }
            $par["client_id"] = array_shift($parts);
            $par["target"] = implode("_", $parts);  // e.g. cat_581
            $t = explode("_", $par["target"]);
            $type = $t[0] ?? "";
            $ref_id = (int) ($t[1] ?? 0);
        } elseif (is_int($p = strpos($href, "/goto.php/"))) {
            $parts = explode("/", substr($href, $p + 10));
            $ref_id = (int) ($parts[1] ?? 0);
            $type = ($parts[0] ?? "");
        } elseif (is_int($p = strpos($href, "/go/"))) {
            $parts = explode("/", substr($href, $p + 4));
            $ref_id = (int) ($parts[1] ?? 0);
            $type = ($parts[0] ?? "");
        } else {
            // extract all query parameter
            foreach (explode("&", ($url["query"] ?? "")) as $p) {
                $p = explode("=", $p);
                if (isset($p[0]) && isset($p[1])) {
                    $par[$p[0]] = $p[1];
                }
            }
            $ref_id = (int) ($par["ref_id"] ?? 0);
        }

        if ($ref_id > 0 && $type !== "" && !$this->object_def->isRBACObject($type)) {
            $ref_id = 0;
        }

        // we have a client and it's the wrong client -> return ""
        $target_client_id = $par["client_id"] ?? "";
        if ($target_client_id !== "" && $target_client_id !== $this->client_id) {
            return 0;
        }
        return $ref_id;
    }

    public function getNewHref(int $ref_id): string
    {
        if ($ref_id > 0) {
            $new_ref_id = ($this->mapping[$ref_id] ?? 0);
            if ($new_ref_id > 0) {
                return \ilLink::_getLink($new_ref_id);
            }
        }
        return "";
    }
}
