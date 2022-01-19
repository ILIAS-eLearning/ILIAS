<?php
/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
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
     * The available renderers.
     */
    private static ?array $renderers = null;

    /**
     * Gets an array containing all available preview renderers.
     *
     * @return array All available preview renderers.
     */
    public static function getRenderers() : array
    {
        self::loadAvailableRenderers();
        return self::$renderers ?? [];
    }

    /**
     * Gets the renderer that is able to create a preview for the specified preview object.
     *
     * @param ilPReview $preview The preview to get the renderer for.
     * @return ilPreviewRenderer A renderer or null if no renderer matches the preview object.
     */
    public static function getRenderer(\ilPReview $preview) : ?ilPreviewRenderer
    {
        $renderers = self::getRenderers();

        // check each renderer if it supports that preview object
        foreach ($renderers as $renderer) {
            if ($renderer->supports($preview)) {
                return $renderer;
            }
        }

        // no matching renderer was found
        return null;
    }

    private static function loadAvailableRenderers() : void
    {
        // already loaded?
        if (self::$renderers != null) {
            return;
        }

        $r = [];

        // get registered and active plugins
        $r[] = new ilImageMagickRenderer();
        if (ilGhostscriptRenderer::isGhostscriptInstalled()) {
            $r[] = new ilGhostscriptRenderer();
        }

        self::$renderers = $r;
    }
}
