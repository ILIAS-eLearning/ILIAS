<?php declare(strict_types=1);

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
 * Abstract class wrapping the HTMLPurifier instance
 * @author Michael Jansen <mjansen@databay.de>
 */
abstract class ilHtmlPurifierAbstractLibWrapper implements ilHtmlPurifierInterface
{
    protected HTMLPurifier $purifier;

    /**
     * ilHtmlPurifierAbstractLibWrapper constructor.
     */
    public function __construct()
    {
        $this->setPurifier(
            new HTMLPurifier($this->getPurifierConfigInstance())
        );
    }

    final public function purify(string $html) : string
    {
        return $this->purifier->purify($html);
    }

    final public function purifyArray(array $htmlCollection) : array
    {
        foreach ($htmlCollection as $key => $html) {
            if (!is_string($html)) {
                throw new InvalidArgumentException(sprintf(
                    'The element on index %s is not of type string: %s',
                    $key,
                    print_r($html, true)
                ));
            }
        }

        return $this->purifier->purifyArray($htmlCollection);
    }

    abstract protected function getPurifierConfigInstance() : HTMLPurifier_Config;

    final protected function setPurifier(HTMLPurifier $purifier) : self
    {
        $this->purifier = $purifier;
        return $this;
    }

    final protected function getPurifier() : HTMLPurifier
    {
        return $this->purifier;
    }

    final public static function _getCacheDirectory() : string
    {
        if (!is_dir(ilFileUtils::getDataDir() . '/HTMLPurifier')) {
            ilFileUtils::makeDirParents(ilFileUtils::getDataDir() . '/HTMLPurifier');
        }

        return ilFileUtils::getDataDir() . '/HTMLPurifier';
    }

    /**
     * Removes all unsupported elements
     * @param string[] $elements
     * @return string[]
     */
    final protected function removeUnsupportedElements(array $elements) : array
    {
        $supportedElements = [];

        $notSupportedTags = [
            'rp',
            'rt',
            'rb',
            'rtc',
            'rbc',
            'ruby',
            'u',
            'strike',
            'param',
            'object'
        ];

        foreach ($elements as $element) {
            if (!in_array($element, $notSupportedTags)) {
                $supportedElements[] = $element;
            }
        }

        return $supportedElements;
    }

    /**
     * @param string[] $elements
     * @return string[]
     */
    final protected function makeElementListTinyMceCompliant(array $elements) : array
    {
        // Bugfix #5945: Necessary because TinyMCE does not use the "u"
        // html element but <span style="text-decoration: underline">E</span>

        if (in_array('u', $elements) && !in_array('span', $elements)) {
            $elements[] = 'span';
        }

        return $elements;
    }
}
