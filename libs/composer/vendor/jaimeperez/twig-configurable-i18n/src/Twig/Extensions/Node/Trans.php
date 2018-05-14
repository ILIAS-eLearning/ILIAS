<?php
/**
 * A class for translation nodes that can be translated with customizable functions.
 *
 * @author Jaime Pérez Crespo
 */

namespace JaimePerez\TwigConfigurableI18n\Twig\Extensions\Node;

use ReflectionClass;
use Twig_Compiler;
use Twig_Extensions_Node_Trans;

class Trans extends Twig_Extensions_Node_Trans
{
    /**
     * Compiles the node to PHP.
     *
     * If JaimePerez\TwigConfigurableI18n\Twig\Environment was used to configure Twig, and the version of
     * Twig_Extensions_Extension_I18n allows it, we will try to change all calls to the default translation methods
     * to whatever is configured in the environment.
     *
     * @param Twig_Compiler $compiler A Twig_Compiler instance
     */
    public function compile(Twig_Compiler $compiler)
    {
        parent::compile($compiler);

        // get the reflection class for Twig_Compiler and evaluate if we can parasite it
        $class = new ReflectionClass('Twig_Compiler');
        if (!$class->hasProperty('source')) {
            // the source must have changed, we don't have the "source" property, so nothing we can do here...
            return;
        }

        // looks doable, set the "source" property accessible
        $property = $class->getProperty('source');
        $property->setAccessible(true);

        // now, if we have proper configuration, rename the calls to gettext with the ones configured in the environment
        $env = $compiler->getEnvironment();
        if (is_a($env, '\JaimePerez\TwigConfigurableI18n\Twig\Environment')) {
            /** @var \JaimePerez\TwigConfigurableI18n\Twig\Environment $env */
            $options = $env->getOptions();
            $source = $compiler->getSource();
            if (array_key_exists('translation_function', $options) &&
                is_callable($options['translation_function'], false, $callable)
            ) {
                $source = preg_replace('/([^\w_$])gettext\(/', '$1'.$callable.'(', $source);
                $property->setValue($compiler, $source);
            }
            if (array_key_exists('translation_function_plural', $options) &&
                is_callable($options['translation_function_plural'], false, $callable)
            ) {
                $source = preg_replace(
                    '/([^\w_$])ngettext\(/',
                    '$1'.$callable.'(',
                    $source
                );
                $property->setValue($compiler, $source);
            }
        }
    }
}
