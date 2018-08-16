<?php
require_once 'libs/composer/vendor/autoload.php';

require_once 'Services/TermsOfService/test/ilTermsOfServiceBaseTest.php';
require_once 'Services/TermsOfService/test/criteria/ilTermsOfServiceCriterionBaseTest.php';
require_once 'Services/TermsOfService/test/evaluation/ilTermsOfServiceEvaluationBaseTest.php';

require_once 'Services/TermsOfService/test/factories/ilTermsOfServiceEntityFactoryTest.php';
require_once 'Services/TermsOfService/test/factories/ilTermsOfServiceDataGatewayFactoryTest.php';
require_once 'Services/TermsOfService/test/factories/ilTermsOfServiceTableDataProviderFactoryTest.php';
require_once 'Services/TermsOfService/test/factories/ilTermsOfServiceCriterionTypeFactoryTest.php';
require_once 'Services/TermsOfService/test/provider/ilTermsOfServiceAcceptanceHistoryProviderTest.php';
require_once 'Services/TermsOfService/test/entities/ilTermsOfServiceAcceptanceEntityTest.php';
require_once 'Services/TermsOfService/test/gateways/ilTermsOfServiceAcceptanceDatabaseGatewayTest.php';
require_once 'Services/TermsOfService/test/evaluation/ilTermsOfServiceDocumentEvaluationTest.php';
require_once 'Services/TermsOfService/test/evaluation/ilTermsOfServiceDocumentCriteriaEvaluationTest.php';
require_once 'Services/TermsOfService/test/criteria/ilTermsOfServiceUserHasLanguageCriterionTest.php';
require_once 'Services/TermsOfService/test/criteria/ilTermsOfServiceUserHasGlobalRoleCriterionTest.php';
require_once 'Services/TermsOfService/test/criteria/ilTermsOfServiceNullCriterionTest.php';
require_once 'Services/TermsOfService/test/criteria/ilTermsOfServiceCriterionConfigTest.php';