<?php
/**
 * This class extends the Twig_Environment class.
 *
 * It allows you to keep the configuration options passed to the constructor of the environment, so that you can
 * configure not only twig but also its extensions in one single place that's easily available.
 *
 * @author Jaime Pérez Crespo
 */
namespace JaimePerez\TwigConfigurableI18n\Twig;

class Environment extends \Twig_Environment
{

    /**
     * @var array The array of options passed to the constructor.
     */
    protected $options = array();


    /**
     * Extended constructor.
     *
     * Additional options supported:
     *
     *  * translation_function: the name of a function to translate a message in singular.
     *
     *  * translation_function_plural: the name of a function to translate a message in plural.
     *
     * @see \Twig_Environment::__construct()
     * @param \Twig_LoaderInterface|null $loader A Twig_LoaderInterface instance.
     * @param array                      $options An array of options.
     */
    public function __construct(\Twig_LoaderInterface $loader = null, $options = array())
    {
        parent::__construct($loader, $options);
        $this->options = $options;
    }


    /**
     * Gets the array of options used in this environment.
     *
     * @return array An array of options.
     */
    public function getOptions()
    {
        return $this->options;
    }
}
