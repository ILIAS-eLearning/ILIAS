<?php
/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

use ILIAS\BackgroundTasks\Bucket;
use ILIAS\BackgroundTasks\Implementation\Tasks\AbstractUserInteraction;
use ILIAS\BackgroundTasks\Implementation\Tasks\UserInteraction\UserInteractionOption;
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\StringValue;
use ILIAS\BackgroundTasks\Implementation\Values\ThunkValue;
use ILIAS\BackgroundTasks\Task\UserInteraction\Option;
use ILIAS\BackgroundTasks\Types\SingleType;
use ILIAS\BackgroundTasks\Types\Type;
use ILIAS\BackgroundTasks\Value;
use ILIAS\Filesystem\Util\LegacyPathHelper;

/**
 * Description of class class
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 */
class ilDownloadZipInteraction extends AbstractUserInteraction
{
    public const OPTION_DOWNLOAD = 'download';
    public const OPTION_CANCEL = 'cancel';
    private ?ilLogger $logger;


    public function __construct()
    {
        global $DIC;
        $this->logger = $DIC->logger()->cal();
    }


    /**
     * @inheritdoc
     */
    public function getInputTypes(): array
    {
        return [
            new SingleType(StringValue::class),
            new SingleType(StringValue::class),
        ];
    }


    /**
     * @inheritDoc
     */
    public function getRemoveOption(): Option
    {
        return new UserInteractionOption('remove', self::OPTION_CANCEL);
    }


    /**
     * @inheritDoc
     */
    public function getOutputType(): Type
    {
        return new SingleType(StringValue::class);
    }


    /**
     * @inheritDoc
     */
    public function getOptions(array $input): array
    {
        return [
            new UserInteractionOption('download', self::OPTION_DOWNLOAD),
        ];
    }


    /**
     * @inheritDoc
     */
    public function interaction(array $input, Option $user_selected_option, Bucket $bucket): Value
    {
        global $DIC;
        $zip_name = $input[1];
        $download_name = $input[0];

        $this->logger->debug('User interaction download zip ' . $input[0]->getValue() . ' as '
            . $input[1]->getValue());

        if ($user_selected_option->getValue() != self::OPTION_DOWNLOAD) {
            $this->logger->info('Download canceled');
            // delete zip file
            $filesystem = $DIC->filesystem()->temp();

            try {
                $path = LegacyPathHelper::createRelativePath($zip_name->getValue());
            } catch (InvalidArgumentException $e) {
                $path = null;
            }
            if (!is_null($path) && $filesystem->has($path)) {
                $filesystem->deleteDir(dirname($path));
            }

            return new ThunkValue();
        }

        $this->logger->info("Delivering File.");

        ilFileDelivery::deliverFileAttached($download_name->getValue(), $zip_name->getValue());

        return new ThunkValue();
    }
}
