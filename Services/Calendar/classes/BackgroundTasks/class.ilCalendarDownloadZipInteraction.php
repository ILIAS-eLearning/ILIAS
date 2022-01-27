<?php declare(strict_types=1);

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\BackgroundTasks\Implementation\Tasks\AbstractUserInteraction;
use ILIAS\BackgroundTasks\Types\SingleType;
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\StringValue;
use ILIAS\BackgroundTasks\Implementation\Tasks\UserInteraction\UserInteractionOption;
use ILIAS\BackgroundTasks\Task\UserInteraction\Option;
use ILIAS\BackgroundTasks\Bucket;
use ILIAS\BackgroundTasks\Types\Type;
use ILIAS\BackgroundTasks\Value;
use ILIAS\Filesystem\Util\LegacyPathHelper;
use ILIAS\FileUpload\MimeType;

/**
 * Description of class class
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilCalendarDownloadZipInteraction extends AbstractUserInteraction
{
    protected const OPTION_DOWNLOAD = 'download';
    protected const OPTION_CANCEL = 'cancel';

    private ilLogger $logger;

    public function __construct()
    {
        global $DIC;
        $this->logger = $DIC->logger()->cal();
    }

    /**
     * @inheritdoc
     */
    public function getInputTypes() : array
    {
        return [
            new SingleType(StringValue::class),
            new SingleType(StringValue::class),
        ];
    }

    /**
     * @inheritDoc
     */
    public function getRemoveOption() : Option
    {
        return new UserInteractionOption('remove', self::OPTION_CANCEL);
    }

    /**
     * @inheritDoc
     */
    public function getOutputType() : Type
    {
        return new SingleType(StringValue::class);
    }

    /**
     * @inheritDoc
     */
    public function getOptions(array $input) : array
    {
        return [
            new UserInteractionOption('download', self::OPTION_DOWNLOAD),
        ];
    }

    /**
     * @inheritDoc
     */
    public function interaction(array $input, Option $user_selected_option, Bucket $bucket) : Value
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

            // @todo what kind of value is desired
            return $download_name;
        }

        $this->logger->info("Delivering File.");

        ilFileDelivery::deliverFileAttached(
            $download_name->getValue(),
            $zip_name->getValue(),
            MimeType::APPLICATION__ZIP
        );

        // @todo what kind of value is desired
        return $download_name;
    }
}
