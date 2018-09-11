<?php

namespace SAML2\Assertion;

use Psr\Log\LoggerInterface;
use SAML2\Assertion\Transformer\DecodeBase64Transformer;
use SAML2\Assertion\Transformer\NameIdDecryptionTransformer;
use SAML2\Assertion\Transformer\TransformerChain;
use SAML2\Assertion\Validation\AssertionValidator;
use SAML2\Assertion\Validation\ConstraintValidator\NotBefore;
use SAML2\Assertion\Validation\ConstraintValidator\NotOnOrAfter;
use SAML2\Assertion\Validation\ConstraintValidator\SessionNotOnOrAfter;
use SAML2\Assertion\Validation\ConstraintValidator\SpIsValidAudience;
use SAML2\Assertion\Validation\ConstraintValidator\SubjectConfirmationMethod;
use SAML2\Assertion\Validation\ConstraintValidator\SubjectConfirmationNotBefore;
use SAML2\Assertion\Validation\ConstraintValidator\SubjectConfirmationNotOnOrAfter;
use SAML2\Assertion\Validation\ConstraintValidator\SubjectConfirmationRecipientMatches;
use SAML2\Assertion\Validation\ConstraintValidator\SubjectConfirmationResponseToMatches;
use SAML2\Assertion\Validation\SubjectConfirmationValidator;
use SAML2\Certificate\PrivateKeyLoader;
use SAML2\Configuration\Destination;
use SAML2\Configuration\IdentityProvider;
use SAML2\Configuration\ServiceProvider;
use SAML2\Response;
use SAML2\Signature\Validator;

/**
 * Simple Builder that allows to build a new Assertion Processor.
 *
 * This is an excellent candidate for refactoring towards dependency injection
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProcessorBuilder
{
    public static function build(
        LoggerInterface $logger,
        Validator $signatureValidator,
        Destination $currentDestination,
        IdentityProvider $identityProvider,
        ServiceProvider $serviceProvider,
        Response $response
    ) {
        $keyloader = new PrivateKeyLoader();
        $decrypter = new Decrypter($logger, $identityProvider, $serviceProvider, $keyloader);
        $assertionValidator = self::createAssertionValidator($identityProvider, $serviceProvider);
        $subjectConfirmationValidator = self::createSubjectConfirmationValidator(
            $identityProvider,
            $serviceProvider,
            $currentDestination,
            $response
        );

        $transformerChain = self::createAssertionTransformerChain(
            $logger,
            $keyloader,
            $identityProvider,
            $serviceProvider
        );

        return new Processor(
            $decrypter,
            $signatureValidator,
            $assertionValidator,
            $subjectConfirmationValidator,
            $transformerChain,
            $identityProvider,
            $logger
        );
    }

    private static function createAssertionValidator(
        IdentityProvider $identityProvider,
        ServiceProvider $serviceProvider
    ) {
        $validator = new AssertionValidator($identityProvider, $serviceProvider);
        $validator->addConstraintValidator(new NotBefore());
        $validator->addConstraintValidator(new NotOnOrAfter());
        $validator->addConstraintValidator(new SessionNotOnOrAfter());
        $validator->addConstraintValidator(new SpIsValidAudience());

        return $validator;
    }

    private static function createSubjectConfirmationValidator(
        IdentityProvider $identityProvider,
        ServiceProvider $serviceProvider,
        Destination $currentDestination,
        Response $response
    ) {
        $validator = new SubjectConfirmationValidator($identityProvider, $serviceProvider);
        $validator->addConstraintValidator(
            new SubjectConfirmationMethod()
        );
        $validator->addConstraintValidator(
            new SubjectConfirmationNotBefore()
        );
        $validator->addConstraintValidator(
            new SubjectConfirmationNotOnOrAfter()
        );
        $validator->addConstraintValidator(
            new SubjectConfirmationRecipientMatches(
                $currentDestination
            )
        );
        $validator->addConstraintValidator(
            new SubjectConfirmationResponseToMatches(
                $response
            )
        );

        return $validator;
    }

    private static function createAssertionTransformerChain(
        LoggerInterface $logger,
        PrivateKeyLoader $keyloader,
        IdentityProvider $identityProvider,
        ServiceProvider $serviceProvider
    ) {
        $chain = new TransformerChain($identityProvider, $serviceProvider);
        $chain->addTransformerStep(new DecodeBase64Transformer());
        $chain->addTransformerStep(
            new NameIdDecryptionTransformer($logger, $keyloader)
        );

        return $chain;
    }
}
