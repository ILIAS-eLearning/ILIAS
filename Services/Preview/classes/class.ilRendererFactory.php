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

/**
 * Factory that provides access to all available preview renderers.
 *
 * @author Stefan Born <stefan.born@phzh.ch>
 * @version $Id$
 *
 * @ingroup ServicesPreview
 */
final class ilRendererFactory
{
    /**
     * @var ilFilePreviewRenderer[]
     */
    private array $renderers = [];

    /**
     * @param ilFilePreviewRenderer[] $additional_renderers
     */
    public function __construct(array $additional_renderers = [])
    {
        $base_renderers = [
            new ilImageMagickRenderer(),
            new ilGhostscriptRenderer()
        ];
        $this->renderers = array_merge($additional_renderers, $base_renderers);
    }

    /**
     * Gets an array containing all available preview renderers.
     *
     * @return ilFilePreviewRenderer[] All available preview renderers.
     */
    public function getRenderers(): array
    {
        return $this->renderers;
    }

    /**
     * Gets the renderer that is able to create a preview for the specified preview object.
     *
     * @param ilPReview $preview The preview to get the renderer for.
     * @return ilPreviewRenderer A renderer or null if no renderer matches the preview object.
     */
    public function getRenderer(\ilPreview $preview): ?ilPreviewRenderer
    {
        // check each renderer if it supports that preview object
        foreach ($this->getRenderers() as $renderer) {
            if ($renderer->supports($preview)) {
                return $renderer;
            }
        }

        // no matching renderer was found
        return null;
    }
}
