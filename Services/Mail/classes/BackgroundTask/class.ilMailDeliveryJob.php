<?php
use ILIAS\BackgroundTasks\Implementation\Tasks\AbstractJob;
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\BooleanValue;
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\IntegerValue;
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\StringValue;
use ILIAS\BackgroundTasks\Observer;
use ILIAS\BackgroundTasks\Types\SingleType;

/**
 * Class ilMailDeliveryJob
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMailDeliveryJob extends AbstractJob
{
	/**
	 * @inheritdoc
	 */
	public function run(array $input, Observer $observer)
	{
		global $DIC;

		$DIC->logger()->mail()->info(sprintf(
			'Mail delivery background task for input: %s',
			json_encode([
				$input[0]->getValue(),
				$input[1]->getValue(),
				$input[2]->getValue(),
				$input[3]->getValue(),
				$input[4]->getValue(),
				$input[5]->getValue(),
				$input[6]->getValue(),
			], JSON_PRETTY_PRINT)
		));

		/** @var ilMailMimeSenderFactory $senderFactory */
		$senderFactory = $GLOBALS['DIC']["mail.mime.sender.factory"];
		$sender = $senderFactory->getSenderByUsrId($input[0]->getValue());

		$mmail = new ilMimeMail();
		$mmail->From($sender);
		$mmail->To(explode(',', $input[1]->getValue()));
		$mmail->Subject($input[4]->getValue());
		$mmail->Body($input[5]->getValue());

		$cc = $input[2]->getValue();
		if (strlen($cc) > 0) {
			$mmail->Cc(explode(',', $cc));
		}

		$bcc = $input[3]->getValue();
		if (strlen($bcc) > 0) {
			$mmail->Bcc(explode(',', $bcc));
		}

		$attach = $input[6]->getValue();
		if (strlen($attach) > 0) {
			$authUserFileData = new \ilFileDataMail($DIC->user()->getId());
			$anonymousFileData = new \ilFileDataMail(ANONYMOUS_USER_ID);

			// mjansen: switched separator from "," to "#:#" because of mantis bug #6039
			// for backward compatibility we have to check if the substring "#:#" exists as leading separator
			// otherwise we should use ";" 
			if (strpos($attach, '#:#') === 0) {
				$attach      = substr($attach, strlen('#:#'));
				$attachments = explode('#:#', $attach);
			} else {
				$attachments = explode(',', $attach);
			}

			foreach ($attachments as $attachment) {
				$final_filename = null;
				$filename       = basename($attachment);
				if (strlen($filename) > 0) {
					// #17740
					$final_filename = preg_replace('/^(\d+?_)(.*)/', '$2', $filename);
				}

				$allowedPathPrefixes = [
					$authUserFileData->getAbsoluteAttachmentPoolPathPrefix()
				];

				if ($input[0]->getValue() == ANONYMOUS_USER_ID) {
					$allowedPathPrefixes[] = $anonymousFileData->getAbsoluteAttachmentPoolPathPrefix();
				}

				$absoluteAttachmentPath = realpath($attachment);

				$matchedPathPrefixes = array_filter($allowedPathPrefixes, function($path) use ($absoluteAttachmentPath) {
					return strpos($absoluteAttachmentPath, $path) === 0;
				});

				if (count($matchedPathPrefixes) > 0) {
					$mmail->Attach($attachment, '', 'inline', $final_filename);
					$DIC->logger()->mail()->debug(sprintf("Accepted attachment: %s", $attachment));
				} else {
					$DIC->logger()->mail()->warning(sprintf(
						"Ignored attachment when sending message via SOAP: Given path '%s' is not in allowed prefix list: %s",
						$absoluteAttachmentPath,
						implode(', ', $allowedPathPrefixes)
					));
				}
			}
		}

		$mmail->Send();

		$output = new BooleanValue();
		$output->setValue(true);

		return $output;
	}

	/**
	 * @inheritdoc
	 */
	public function isStateless()
	{
		return true;
	}

	/**
	 * @inheritdoc
	 */
	public function getExpectedTimeOfTaskInSeconds()
	{
		return 30;
	}

	/**
	 * @inheritdoc
	 */
	public function getInputTypes()
	{
		return [
			new SingleType(IntegerValue::class),
			new SingleType(StringValue::class),
			new SingleType(StringValue::class),
			new SingleType(StringValue::class),
			new SingleType(StringValue::class),
			new SingleType(StringValue::class),
			new SingleType(StringValue::class)
		];
	}

	/**
	 * @inheritdoc
	 */
	public function getOutputType()
	{
		return new SingleType(BooleanValue::class);
	}
}