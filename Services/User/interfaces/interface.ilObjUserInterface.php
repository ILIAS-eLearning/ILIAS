<?php

use ILIAS\UI\Component\Symbol\Avatar\Avatar;

/**
 * User class
 *
 * @author    Sascha Hofmann <saschahofmann@gmx.de>
 * @author    Stefan Meyer <meyer@leifos.com>
 * @author    Peter Gabriel <pgabriel@databay.de>
 */
interface ilObjUserInterface
{
    /**
     * @throws ilObjectNotFoundException
     * @throws ilObjectTypeMismatchException
     * @throws ilSystemStyleException
     */
    public function read(): void;
    
    public function getPasswordEncodingType(): string;
    
    public function setPasswordEncodingType(string $password_encryption_type): void;
    
    public function getPasswordSalt(): ?string;
    
    public function setPasswordSalt(?string $password_salt): void;
    
    /**
     * loads a record "user" from array
     */
    public function assignData(array $a_data): void;
    
    /**
     * @throws ilPasswordException
     * @throws ilUserException
     * @todo drop fields last_update & create_date. redundant data in object_data!
     */
    public function saveAsNew(): void;
    
    public function update();
    
    /**
     * write accept date of user agreement
     */
    public function writeAccepted(): void;
    
    /**
     * updates the login data of a "user"
     *
     * @todo set date with now() should be enough
     */
    public function refreshLogin(): void;
    
    /**
     * Resets the user password
     *
     * @param string $raw        Password as plaintext
     * @param string $raw_retype Retyped password as plaintext
     * @return    bool    true on success otherwise false
     * @throws ilPasswordException
     * @throws ilUserException
     */
    public function resetPassword(string $raw, string $raw_retype): bool;
    
    /**
     * update login name
     *
     * @param string    new login
     * @return    bool    true on success; otherwise false
     * @throws ilDateTimeException
     * @throws ilUserException
     */
    public function updateLogin(string $a_login): bool;
    
    public function writePref(string $a_keyword, string $a_value): void;
    
    public function deletePref(string $a_keyword): void;
    
    public function writePrefs(): void;
    
    public function getTimeZone(): string;
    
    public function getTimeFormat(): string;
    
    public function getDateFormat(): string;
    
    public function setPref(string $a_keyword, ?string $a_value): void;
    
    public function getPref(string $a_keyword): ?string;
    
    public function readPrefs(): void;
    
    public function delete();
    
    /**
     * builds a string with title + firstname + lastname
     * method is used to build fullname in member variable $this->fullname. But you
     * may use the function in static manner.
     */
    public function setFullname(): void;
    
    /**
     * @param int $a_max_strlen max. string length to return (optional)
     *                          if string length of fullname is greater than given a_max_strlen
     *                          the name is shortened in the following way:
     *                          1. abreviate firstname (-> Dr. J. Smith)
     *                          if fullname is still too long
     *                          2. drop title (-> John Smith)
     *                          if fullname is still too long
     *                          3. drop title and abreviate first name (J. Smith)
     *                          if fullname is still too long
     *                          4. drop title and firstname and shorten lastname to max length (--> Smith)
     */
    public function getFullname(int $a_max_strlen = 0): string;
    
    public function setLogin(string $a_str): void;
    
    public function getLogin(): string;
    
    public function setPasswd(string $a_str, string $a_type = ilObjUser::PASSWD_PLAIN): void;
    
    /**
     * @return string The password is encoded depending on the current password type.
     */
    public function getPasswd(): string;
    
    /**
     * @return string password type (ilObjUser::PASSWD_PLAIN, ilObjUser::PASSWD_CRYPTED).
     */
    public function getPasswdType(): string;
    
    public function setGender(string $a_str): void;
    
    public function getGender(): string;
    
    /**
     * set user title
     * (note: don't mix up this method with setTitle() that is derived from
     * ilObject and sets the user object's title)
     */
    public function setUTitle(string $a_str): void;
    
    public function getUTitle(): string;
    
    public function setFirstname(string $a_str): void;
    
    public function getFirstname(): string;
    
    public function setLastname(string $a_str): void;
    
    public function getLastname(): string;
    
    public function setInstitution(string $a_str): void;
    
    public function getInstitution(): string;
    
    public function setDepartment(string $a_str): void;
    
    public function getDepartment(): string;
    
    public function setStreet(string $a_str): void;
    
    public function getStreet(): string;
    
    public function setCity(string $a_str): void;
    
    public function getCity(): string;
    
    public function setZipcode(string $a_str): void;
    
    public function getZipcode(): string;
    
    public function setCountry(string $a_str): void;
    
    public function getCountry(): string;
    
    /**
     * Set selected country (selection drop down)
     */
    public function setSelectedCountry(string $a_val): void;
    
    /**
     * Get selected country (selection drop down)
     */
    public function getSelectedCountry(): string;
    
    public function setPhoneOffice(string $a_str): void;
    
    public function getPhoneOffice(): string;
    
    public function setPhoneHome(string $a_str): void;
    
    public function getPhoneHome(): string;
    
    public function setPhoneMobile(string $a_str): void;
    
    public function getPhoneMobile(): string;
    
    public function setFax(string $a_str): void;
    
    public function getFax(): string;
    
    public function setClientIP(string $a_str): void;
    
    public function getClientIP(): string;
    
    public function setMatriculation(string $a_str): void;
    
    public function getMatriculation(): string;
    
    public function setEmail(string $a_str): void;
    
    public function getEmail(): string;
    
    public function getSecondEmail(): ?string;
    
    public function setSecondEmail(?string $second_email): void;
    
    public function setHobby(string $a_str): void;
    
    public function getHobby(): string;
    
    public function setLanguage(string $a_str): void;
    
    public function getLanguage(): string;
    
    public function setLastPasswordChangeTS(int $a_last_password_change_ts): void;
    
    public function getLastPasswordChangeTS(): int;
    
    public function getPasswordPolicyResetStatus(): bool;
    
    public function setPasswordPolicyResetStatus(bool $status): void;
    
    /**
     * returns the current language (may differ from user's pref setting!)
     */
    public function getCurrentLanguage(): string;
    
    /**
     * Set current language
     */
    public function setCurrentLanguage(string $a_val): void;
    
    public function setLastLogin(string $a_str): void;
    
    public function getLastLogin(): string;
    
    public function setFirstLogin(string $a_str): void;
    
    public function getFirstLogin(): string;
    
    public function setLastProfilePrompt(string $a_str): void;
    
    public function getLastProfilePrompt(): string;
    
    public function setLastUpdate(string $a_str): void;
    
    public function getLastUpdate(): string;
    
    public function setComment(string $a_str): void;
    
    public function getComment(): string;
    
    /**
     * set date the user account was activated
     * null indicates that the user has not yet been activated
     */
    public function setApproveDate(?string $a_str): void;
    
    public function getApproveDate(): ?string;
    
    public function getAgreeDate(): ?string;
    
    public function setAgreeDate(?string $a_str);
    
    /**
     * set user active state and updates system fields appropriately
     *
     * @param int $a_owner the id of the person who approved the account, defaults to 6 (root)
     */
    public function setActive(bool $a_active, int $a_owner = 0): void;
    
    public function getActive(): bool;
    
    /**
     * synchronizes current and stored user active values
     * for the owner value to be set correctly, this function should only be called
     * when an admin is approving a user account
     */
    public function syncActive(): void;
    
    /**
     * get user active state
     */
    public function getStoredActive(int $a_id): bool;
    
    public function setSkin(string $a_str): void;
    
    public function setTimeLimitOwner(int $a_owner): void;
    
    public function getTimeLimitOwner(): int;
    
    public function setTimeLimitFrom(?int $a_from): void;
    
    public function getTimeLimitFrom(): ?int;
    
    public function setTimeLimitUntil(?int $a_until): void;
    
    public function getTimeLimitUntil(): ?int;
    
    public function setTimeLimitUnlimited(bool $a_unlimited): void;
    
    public function getTimeLimitUnlimited(): bool;
    
    public function setTimeLimitMessage(string $a_time_limit_message): void;
    
    public function getTimeLimitMessage(): string;
    
    public function setLoginAttempts(int $a_login_attempts): void;
    
    public function getLoginAttempts(): int;
    
    public function checkTimeLimit(): bool;
    
    public function setProfileIncomplete(bool $a_prof_inc): void;
    
    public function getProfileIncomplete(): bool;
    
    public function isPasswordChangeDemanded(): bool;
    
    public function isPasswordExpired(): bool;
    
    public function getPasswordAge(): int;
    
    public function setLastPasswordChangeToNow(): bool;
    
    public function resetLastPasswordChange(): bool;
    
    public function setLatitude(?string $a_latitude): void;
    
    public function getLatitude(): ?string;
    
    public function setLongitude(?string $a_longitude): void;
    
    public function getLongitude(): ?string;
    
    public function setLocationZoom(?int $a_locationzoom): void;
    
    public function getLocationZoom(): ?int;
    
    /**
     * check user id with login name
     */
    public function checkUserId(): bool;
    
    public function isCurrentUserActive(): bool;
    
    public function getLoginByUserId(int $a_userid): ?string;
    
    /**
     * add an item to user's personal clipboard
     *
     * @param int    $a_item_id           ref_id for objects, that are in the main tree
     *                                    (learning modules, forums) obj_id for others
     * @param string $a_type              object type
     */
    public function addObjectToClipboard(
        int $a_item_id,
        string $a_type,
        string $a_title,
        int $a_parent = 0,
        int $a_time = 0,
        int $a_order_nr = 0
    ): void;
    
    /**
     * Add a page content item to PC clipboard (should go to another class)
     *
     * @todo move to COPage service
     */
    public function addToPCClipboard(string $a_content, string $a_time, int $a_nr): void;
    
    /**
     * Add a page content item to PC clipboard (should go to another class)
     *
     * @todo move to COPage service
     */
    public function getPCClipboardContent(): array;
    
    /**
     * Check whether clipboard has objects of a certain type
     */
    public function clipboardHasObjectsOfType(string $a_type): bool;
    
    public function clipboardDeleteObjectsOfType(string $a_type): void;
    
    public function clipboardDeleteAll(): void;
    
    /**
     * get all clipboard objects of user and specified type
     */
    public function getClipboardObjects(string $a_type = "", bool $a_top_nodes_only = false): array;
    
    /**
     * Get children of an item
     */
    public function getClipboardChilds(int $a_parent, string $a_insert_time): array;
    
    public function removeObjectFromClipboard(int $a_item_id, string $a_type): void;
    
    public function getOrgUnitsRepresentation(): string;
    
    public function setAuthMode(string $a_str): void;
    
    public function getAuthMode(bool $a_auth_key = false): string;
    
    public function setExternalAccount(?string $a_str): void;
    
    public function getExternalAccount(): ?string;
    
    /**
     * @param string $a_size "small", "xsmall" or "xxsmall"
     * @throws ilWACException
     */
    public function getPersonalPicturePath(string $a_size = "small", bool $a_force_pic = false): string;
    
    public function getAvatar(): Avatar;
    
    public function removeUserPicture(bool $a_do_update = true): void;
    
    public function setUserDefinedData(array $a_data): void;
    
    public function getUserDefinedData(): array;
    
    public function updateUserDefinedFields(): void;
    
    public function readUserDefinedFields(): void;
    
    public function deleteUserDefinedFieldEntries(): void;
    
    /**
     * Get formatted mail body text of user profile data.
     *
     * @throws ilDateTimeException
     */
    public function getProfileAsString(ilLanguage $language): string;
    
    /**
     * returns true if public is profile, false otherwise
     */
    public function hasPublicProfile(): bool;
    
    /**
     * returns firstname lastname and login if profile is public, login otherwise
     */
    public function getPublicName(): string;
    
    public function setBirthday(?string $a_birthday): void;
    
    public function getBirthday(): ?string;
    
    public function resetOwner(): void;
    
    public function isCaptchaVerified(): bool;
    
    public function setCaptchaVerified(bool $a_val): void;
    
    public function exportPersonalData(): void;
    
    public function getPersonalDataExportFile(): string;
    
    public function sendPersonalDataFile(): void;
    
    public function importPersonalData(
        array $a_file,
        bool $a_profile_data,
        bool $a_settings,
        bool $a_notes,
        bool $a_calendar
    ): void;
    
    public function setInactivationDate(?string $inactivation_date): void;
    
    public function getInactivationDate(): ?string;
    
    public function hasToAcceptTermsOfService(): bool;
    
    public function hasToAcceptTermsOfServiceInSession(?bool $status = null): bool;
    
    public function isAnonymous(): bool;
    
    public function activateDeletionFlag(): void;
    
    public function removeDeletionFlag(): void;
    
    public function hasDeletionFlag(): bool;
    
    public function setIsSelfRegistered(bool $status): void;
    
    public function isSelfRegistered(): bool;
    
    public function setGeneralInterests(?array $value = null): void;
    
    public function getGeneralInterests(): ?array;
    
    /**
     * Get general interests as plain text
     */
    public function getGeneralInterestsAsText(): string;
    
    public function setOfferingHelp(?array $value = null): void;
    
    public function getOfferingHelp(): ?array;
    
    /**
     * Get help offering as plain text
     */
    public function getOfferingHelpAsText(): string;
    
    public function setLookingForHelp(?array $value = null): void;
    
    public function getLookingForHelp(): ?array;
    
    /**
     * Get help looking for as plain text
     */
    public function getLookingForHelpAsText(): string;
    
    public function updateMultiTextFields(bool $a_create = false): void;
}
