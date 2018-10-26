<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Exception that will be thrown if the user has not obtained
 * the certificate yet.
 * This exception SHOULD be thrown in an instance of
 * `ilCertificatePlaceholderValues` if the conditions are
 * defined.
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateNotObtainedException extends ilException
{
}
