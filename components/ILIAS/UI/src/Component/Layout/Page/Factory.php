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

namespace ILIAS\UI\Component\Layout\Page;

use ILIAS\UI\Component\Legacy\Content;
use ILIAS\Data\Link;
use ILIAS\UI\Component\MainControls\Mainbar;
use ILIAS\UI\Component\MainControls\MetaBar;
use ILIAS\UI\Component\Breadcrumbs\Breadcrumbs;
use ILIAS\UI\Component\Image\Image;
use ILIAS\UI\Component\Toast\Container;
use ILIAS\UI\Component\MainControls\Footer;

/**
 * This is what a factory for pages looks like.
 */
interface Factory
{
    /**
     * ---
     * description:
     *   purpose: >
     *     The Standard Page is the regular view upon ILIAS.
     *
     *   composition: >
     *     The main parts of a Page are the Metabar, the Mainbar providing
     *     main navigation, the logo, title, breadcrumbs and, of course, the pages's
     *     content. "Content" in this case is the part of the page that is
     *     not Mainbar, Metabar, Footer or Locator, but e.g. the Repository-Listing,
     *     an object's view or edit form, etc.
     *     The locator (in form of breadcrumbs), the logo and titles are optional.
     *     Finally, there are short- and view title. The short title is usually used
     *     to identify the installation of ILIAS, while the view-title gives a very
     *     short  reference to the current view. Both short title and view title are
     *     put into the title-tag of the page so they show up in the browser's tab.
     *
     * featurewiki:
     *   - Desktop: https://docu.ilias.de/goto_docu_wiki_wpage_4563_1357.html
     *   - Mobile: https://docu.ilias.de/goto_docu_wiki_wpage_5095_1357.html
     *
     * rules:
     *   usage:
     *     1: The Standard Page MUST be rendered with content, i.e. the actual view on the context.
     *     2: The Standard Page MUST be rendered with a Metabar.
     *     3: The Standard Page MUST be rendered with a Mainbar.
     *     4: The Standard Page SHOULD be rendered with Breadcrumbs.
     *     5: The Standard Page SHOULD be rendered with a Logo.
     *     6: The Standard Page SHOULD be rendered with a Title.
     *     7: The Standard Page's short title SHOULD reference the current ILIAS installation.
     *     8: The Standard Page's view title SHOULD give a good hint to the current view.
     *
     *   accessibility:
     *     1: >
     *        Scrollable areas of the Standard Page MUST be scrollable by only using
     *        the keyboard.
     *     2: The content area of the Standard Page MUST be focused on page load.
     *     3: >
     *        For the content area of the Standard Page, the HTML tag < main > MUST be used
     *        to be identified as the ARIA Landmark Role "Main".
     *     4: >
     *        For the Header of the Standard Page, where Logo and Title are placed, the HTML tag
     *        < header > MUST be used to be identified as the ARIA Landmark Role "Banner".
     *     5: >
     *        For the Footer of the Standard Page, the HTML tag < footer > MUST be used
     *        to be identified as the ARIA Landmark Role "Contentinfo". As long as
     *        the Footer is nested in the HTML element "main", the HTML element of the
     *        Footer MUST additionally be declared with the ARIA role "Contentinfo".
     * ----
     * @param  \ILIAS\UI\Component\Component[] $content
     * @param  \ILIAS\UI\Component\MainControls\MetaBar $metabar
     * @param  \ILIAS\UI\Component\MainControls\MetaBar $mainbar
     * @param  \ILIAS\UI\Component\Breadcrumbs\Breadcrumbs $locator
     * @param  \ILIAS\UI\Component\Image\Image $logo
     * @param  \ILIAS\UI\Component\Image\Image $responsive_logo
     * @param  \ILIAS\UI\Component\Toast\Container $overlay
     * @param  \ILIAS\UI\Component\MainControls\Footer $footer
     * @param  string $title
     * @param  string $short_title
     * @param  string $view_title
     * @return \ILIAS\UI\Component\Layout\Page\Standard
     */
    public function standard(
        array $content,
        ?MetaBar $metabar = null,
        ?MainBar $mainbar = null,
        ?Breadcrumbs $locator = null,
        ?Image $logo = null,
        ?Image $responsive_logo = null,
        string $favicon_path = '',
        ?Container $overlay = null,
        ?Footer $footer = null,
        string $title = '',
        string $short_title = '',
        string $view_title = ''
    ): Standard;

    /**
     * ---
     * description:
     *   purpose: The Mail Page is used to render HTML content for mails.
     *   composition: >
     *    The Mail Page consists of HTML content, a header and a footer.
     *    The header contains the logo and the installation-text.
     *    The footer contains the installation-text and a link to ILIAS.
     *    The stylesheet path refers to the CSS file that is used to style the page and will be included in the HTML.
     *
     * rules:
     *   usage:
     *     1: The Mail Page MUST be rendered with content.
     *     2: >
     *        The Mail Page MUST be rendered with a logo.
     *        The logo must be either
     *        a cid URL (see https://datatracker.ietf.org/doc/html/rfc2392)
     *        or a data URL (see https://developer.mozilla.org/en-US/docs/Web/URI/Reference/Schemes/data).
     *     3: The Mail Page MUST be rendered with a footer.
     *     4: The Mail Page MUST be rendered with a stylesheet path.
     *     5: The Header of the Mail Page MUST contain the logo and the installation-text.
     *     6: The Footer of the Mail Page MUST contain the installation-text and a link to ILIAS.
     *     7: The Mail Page's stylesheet path MUST include all necessary styles to render the page correctly.
     * ----
     * @param string $stylesheet_path
     * @param string $logo_url
     * @param string $installation_title
     * @param \ILIAS\UI\Component\Legacy\Content $html_content
     * @param Link $footer_url
     * @return \ILIAS\UI\Component\Layout\Page\Mail
     */
    public function mail(
        string $stylesheet_path,
        string $logo_url,
        string $installation_title,
        Content $html_content,
        Link $footer_url,
    ): Mail;
}
