<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilMailMimeTest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMailTest extends \ilMailBaseTest
{
	/**
	 * @inheritdoc
	 */
	protected function setUp(): void
	{
		parent::setUp();
	}

	/**
	 * 
	 */
	public function testExternalMailDeliveryToLocalRecipientsWorksAsExpected()
	{
		$senderUsrId = 666;
		$loginToIdMap = [
			'phpunit1' => 1,
			'phpunit2' => 2,
			'phpunit3' => 3,
			'phpunit4' => 4,
			'phpunit5' => 5,
			'phpunit6' => 6,
			'phpunit7' => 7,
		];
		$userInstanceById = [];
		$mailOptionsById = [];
		foreach ($loginToIdMap as $login => $usrId) {
			$user = $this
				->getMockBuilder(\ilObjUser::class)
				->disableOriginalConstructor()
				->setMethods(['getId', 'hasToAcceptTermsOfService', 'checkTimeLimit', 'getActive'])
				->getMock();
			$user->expects($this->any())->method('getId')->willReturn($usrId);
			$user->expects($this->any())->method('getActive')->willReturn(true);
			$user->expects($this->any())->method('hasToAcceptTermsOfService')->willReturn(false);
			$user->expects($this->any())->method('checkTimeLimit')->willReturn(true);
			$userInstanceById[$usrId] = $user;

			$mailOptions = $this
				->getMockBuilder(\ilMailOptions::class)
				->disableOriginalConstructor()
				->setMethods(['getExternalEmailAddresses', 'getIncomingType'])
				->getMock();
			$mailOptions->expects($this->any())->method('getExternalEmailAddresses')->willReturn([
				'phpunit' . $usrId . '@ilias.de',
			]);
			$mailOptions->expects($this->any())->method('getIncomingType')->willReturn(\ilMailOptions::INCOMING_EMAIL);
			$mailOptionsById[$usrId] = $mailOptions;
		}

		$user = $this->getMockBuilder(\ilObjUser::class)->disableOriginalConstructor()->getMock();
		\ilMailMimeSenderUserById::addUserToCache($senderUsrId, $user);

		$addressTypeFactory = $this
			->getMockBuilder(\ilMailAddressTypeFactory::class)
			->disableOriginalConstructor()
			->setMethods(['getByPrefix'])
			->getMock();
		$addressTypeFactory->expects($this->any())->method('getByPrefix')->will($this->returnCallback(function ($arg) use ($loginToIdMap) {
			return new class($arg, $loginToIdMap) implements \ilMailAddressType {
				protected $loginToIdMap = [];
				protected $address;

				public function __construct(\ilMailAddress $address, $loginToIdMap)
				{
					$this->address = $address;
					$this->loginToIdMap = array_map(function($usrId) {
						return [$usrId];
					}, $loginToIdMap);
				}

				public function resolve(): array
				{
					return $this->loginToIdMap[$this->address->getMailbox()] ?? [];
				}

				public function validate(int $senderId): bool
				{
					return true;
				}

				public function getErrors(): array
				{
					return [];
				}

				public function getAddress(): \ilMailAddress
				{
					return $this->address;
				}
			};
		}));

		$db = $this->getMockBuilder(\ilDBInterface::class)->getMock();
		$nextId = 0;
		$db->expects($this->any())->method('nextId')->will($this->returnCallback(function() use (&$nextId) {
			++$nextId;

			return $nextId;
		}));

		$eventHandler = $this->getMockBuilder(\ilAppEventHandler::class)->disableOriginalConstructor()->getMock();
		$logger = $this->getMockBuilder(\ilLogger::class)->disableOriginalConstructor()->getMock();
		$lng = $this->getMockBuilder(\ilLanguage::class)->disableOriginalConstructor()->getMock();
		$settings = $this->getMockBuilder(\ilSetting::class)->disableOriginalConstructor()->getMock();
		$this->setGlobalVariable('ilSetting', $settings);

		$mailFileData = $this->getMockBuilder(\ilFileDataMail::class)->disableOriginalConstructor()->getMock();
		$mailOptions = $this->getMockBuilder(\ilMailOptions::class)->disableOriginalConstructor()->getMock();
		$mailBox = $this->getMockBuilder(\ilMailbox::class)->disableOriginalConstructor()->getMock();

		$mailService = new \ilMail(
			$senderUsrId,
			$addressTypeFactory,
			new \ilMailRfc822AddressParserFactory(),
			$eventHandler,
			$logger,
			$db,
			$lng,
			$mailFileData,
			$mailOptions,
			$mailBox,
			new ilMailMimeSenderFactory($settings),
			function (string $login) use ($loginToIdMap) {
				return $loginToIdMap[$login] ?? 0;
			},
			4711
		);

		$oldTransport = \ilMimeMail::getDefaultTransport();

		$mailTransport = $this
			->getMockBuilder(\ilMailMimeTransport::class)
			->getMock();
		$mailTransport->expects($this->exactly(1))->method('send')->with($this->callback(function(\ilMimeMail $mailer) use($loginToIdMap) {
			$totalBcc = [];
			foreach ($mailer->getBcc() as $bcc) {
				$totalBcc = array_filter(array_map('trim', explode(',', $bcc))) + $totalBcc;
			}

			return count($totalBcc) === count($loginToIdMap);
		}))->willReturn(true);
		\ilMimeMail::setDefaultTransport($mailTransport);

		$mailService->setUserInstanceById($userInstanceById);
		$mailService->setMailOptionsByUserIdMap($mailOptionsById);

		$mailService->sendMail(
			implode(',', array_slice(array_keys($loginToIdMap), 0, 3)),
			implode(',', array_slice(array_keys($loginToIdMap), 3, 2)),
			implode(',', array_slice(array_keys($loginToIdMap), 5, 2)),
			'Subject',
			'Message',
			[],
			false
		);

		\ilMimeMail::setDefaultTransport($oldTransport);
	}
}