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
 */

namespace ILIAS\UI\Component\Meta;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface Factory
{
    /**
     * ---
     * description:
     *   purpose: >
     *     this component is used to add metadata to an HTML document, that only consists
     *     of key => value pairs.
     *   composition: >
     *     this component consists of an HTML <meta> tag.
     *   effect: >
     *     mostly provides metadata about the page that is used by search engines or the browser.
     *   rivals:
     *     Rival 1: Complex >
     *          Complex should be used instead, if the specification allows a content to be provided.
     *
     * context:
     *     - this component will only be used in the <head> element of a Page component.
     *
     * rules:
     *   usage:
     *     1: this component must only be used when building a Page.
     *     2: this component must only be used these attributes-keys:
     *          - charset
     *          - media
     *   composition:
     *     1: <meta key=value />
     *
     * ---
     * @param string $key
     * @param string $value
     * @return \ILIAS\UI\Component\Meta\Standard
     */
    public function standard(string $key, string $value) : Standard;

    /**
     * ---
     * description:
     *   purpose: >
     *     this component is used to add metadata to an HTML document, that consists of key => value
     *     pairs which also must be provided with a content.
     *   composition: >
     *     this component consists of an HTML <meta> tag.
     *   effect: >
     *     mostly provides metadata about the page that is used by search engines or the browser.
     *   rivals:
     *     Rival 1: Standard >
     *          Standard should be used instead, if the specification only allows key => value pairs.
     *
     * context:
     *     - this component will only be used in the <head> element of a Page component.
     *
     * rules:
     *   usage:
     *     1: this component must only be used when building a Page.
     *     2: this component must only be used these attributes-keys:
     *          - name
     *          - http-equiv
     *          - itemprop
     *      3: this component can also be used for the attribute-key 'property' when exposing data
     *         according to the open-graph-protocol (https://ogp.me).
     *   composition:
     *     1: <meta key=value content="content" />
     *
     * ---
     * @param string $key
     * @param string $value
     * @param string $content
     * @return \ILIAS\UI\Component\Meta\Complex
     */
    public function complex(string $key, string $value, string $content) : Complex;
}
