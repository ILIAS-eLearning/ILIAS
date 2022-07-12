<?php declare(strict_types=1);
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Calendar cache
 * @author  Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 * @ingroup ServicesCalendar
 */
class ilCalendarCache extends ilCache
{
    private static ?ilCalendarCache $instance = null;

    public function __construct()
    {
        parent::__construct('ServicesCalendar', 'Calendar', true);
        $this->setExpiresAfter(60 * ilCalendarSettings::_getInstance()->getCacheMinutes());
    }

    public static function getInstance() : self
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Get cached entry if cache is active
     */
    protected function readEntry(string $a_id) : bool
    {
        if (!ilCalendarSettings::_getInstance()->isCacheUsed()) {
            return false;
        }
        return parent::readEntry($a_id);
    }

    public function storeEntry(
        string $a_id,
        string $a_value,
        ?int $a_int_key1 = null,
        ?int $a_int_key2 = null,
        ?string $a_text_key1 = null,
        ?string $a_text_key2 = null
    ) : void {
        if (!ilCalendarSettings::_getInstance()->isCacheUsed()) {
            return;
        }
        parent::storeEntry($a_id, $a_value, $a_int_key1, $a_int_key2, $a_text_key1, $a_text_key2);
    }

    /**
     * Store an entry without an expired time (one year)
     */
    public function storeUnlimitedEntry(
        string $a_entry_id,
        string $a_value,
        ?int $a_key1 = 0,
        ?int $a_key2 = 0,
        ?string $a_key3 = '',
        ?string $a_key4 = ''
    ) : void {
        if (!ilCalendarSettings::_getInstance()->isCacheUsed()) {
            return;
        }
        // Unlimited is a year
        $this->setExpiresAfter(60 * 60 * 24 * 365);
        parent::storeEntry($a_entry_id, $a_value, $a_key1, $a_key2, $a_key3, $a_key4);
        $this->setExpiresAfter(ilCalendarSettings::_getInstance()->getCacheMinutes());
    }

    /**
     * Delete user entries in cache
     */
    public function deleteUserEntries(int $a_user_id) : void
    {
        $this->deleteByAdditionalKeys($a_user_id);
    }
}
