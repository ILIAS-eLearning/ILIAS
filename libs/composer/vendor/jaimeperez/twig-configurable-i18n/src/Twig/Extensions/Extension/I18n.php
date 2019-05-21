<?php
/**
 * An internationalization extension for Twig that allows you to specify the functions to use for translation.
 *
 * @author Jaime Pérez Crespo
 */
namespace JaimePerez\TwigConfigurableI18n\Twig\Extensions\Extension;

use JaimePerez\TwigConfigurableI18n\Twig\Extensions\TokenParser\Trans;
use Twig_Extensions_Extension_I18n;

class I18n extends Twig_Extensions_Extension_I18n
{

    /** @var array */
    protected $filters = [];


    /**
     * Build a new I18N extension.
     *
     * Two filters, "trans" and "transchoice" are registered by default. These two will allow you to translate
     * singular and plural sentences, respectively.
     */
    public function __construct()
    {
        $this->filters = array(
            new \Twig_SimpleFilter('trans', array($this, 'translateSingular'), ['needs_environment' => true]),
            new \Twig_SimpleFilter('transchoice', array($this, 'translatePlural'), ['needs_environment' => true]),
        );
    }


    /**
     * Returns the token parser instances to add to the existing list.
     *
     * @return array An array of Twig_TokenParserInterface or Twig_TokenParserBrokerInterface instances
     */
    public function getTokenParsers()
    {
        return array(new Trans());
    }


    /**
     * Returns a list of filters to add to the existing list.
     *
     * @return array An array of filters
     */
    public function getFilters()
    {
        return $this->filters;
    }


    /**
     * Wrapper around the given callable we have to use to translate singular strings.
     *
     * Defaults to gettext().
     *
     * @return string
     */
    public function translateSingular()
    {
        $singular = 'gettext';
        $args = func_get_args();

        /** @var \JaimePerez\TwigConfigurableI18n\Twig\Environment $env */
        $env = array_shift($args);
        $options = $env->getOptions();
        if (array_key_exists('translation_function', $options) &&
            is_callable($options['translation_function'], false, $callable)
        ) {
            $singular = $options['translation_function'];
        }
        return call_user_func_array($singular, $args);
    }


    /**
     * Wrapper around the given callable we have to use to translate plural strings.
     *
     * Defaults to ngettext().
     *
     * @return string
     */
    public function translatePlural()
    {
        $plural = 'ngettext';
        $args = func_get_args();

        /** @var \JaimePerez\TwigConfigurableI18n\Twig\Environment $env */
        $env = array_shift($args);
        $options = $env->getOptions();

        if (array_key_exists('translation_function_plural', $options) &&
            is_callable($options['translation_function_plural'])
        ) {
            $plural = $options['translation_function_plural'];
        }
        return call_user_func_array($plural, $args);
    }
}
