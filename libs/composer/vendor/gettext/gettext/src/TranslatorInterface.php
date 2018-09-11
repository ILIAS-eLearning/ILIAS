<?php

namespace Gettext;

/**
 * Interface used by all translators.
 */
interface TranslatorInterface
{
    /**
     * Register this translator as global, to use with the gettext functions __(), p__(), etc.
     */
    public function register();

    /**
     * Gets a translation using the original string.
     *
     * @param string $original
     *
     * @return string
     */
    public function gettext($original);

    /**
     * Gets a translation checking the plural form.
     *
     * @param string $original
     * @param string $plural
     * @param string $value
     *
     * @return string
     */
    public function ngettext($original, $plural, $value);

    /**
     * Gets a translation checking the domain and the plural form.
     *
     * @param string $domain
     * @param string $original
     * @param string $plural
     * @param string $value
     *
     * @return string
     */
    public function dngettext($domain, $original, $plural, $value);

    /**
     * Gets a translation checking the context and the plural form.
     *
     * @param string $context
     * @param string $original
     * @param string $plural
     * @param string $value
     *
     * @return string
     */
    public function npgettext($context, $original, $plural, $value);

    /**
     * Gets a translation checking the context.
     *
     * @param string $context
     * @param string $original
     *
     * @return string
     */
    public function pgettext($context, $original);

    /**
     * Gets a translation checking the domain.
     *
     * @param string $domain
     * @param string $original
     *
     * @return string
     */
    public function dgettext($domain, $original);

    /**
     * Gets a translation checking the domain and context.
     *
     * @param string $domain
     * @param string $context
     * @param string $original
     *
     * @return string
     */
    public function dpgettext($domain, $context, $original);

    /**
     * Gets a translation checking the domain, the context and the plural form.
     *
     * @param string $domain
     * @param string $context
     * @param string $original
     * @param string $plural
     * @param string $value
     */
    public function dnpgettext($domain, $context, $original, $plural, $value);
}
