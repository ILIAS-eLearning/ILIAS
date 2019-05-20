<?php

namespace App\Infrastructure\CommandHandler\Orm\Customer;

use App\Domain\Command\Customer\DeleteCustomerCommand;
use App\Domain\CommandHandler\Customer\DeleteCustomerCommandHandlerInterface;
use App\Infrastructure\Exception\Customer\CustomerNotFoundException;
use App\Infrastructure\Orm\Repository\CustomerRepository;
use ILIAS\Modules\Course\Domain\Command\AddParticipantCommand;

class AddParticipantCommandHandler implements DeleteCustomerCommandHandlerInterface
{
	/**
	 * @var CustomerRepository
	 */
	private $customerRepository;

	public function __construct(CustomerRepository $customerRepository)
	{
		$this->customerRepository = $customerRepository;
	}

	/**
	 * @param DeleteCustomerCommand $deleteCustomerCommand
	 *
	 * @throws CustomerNotFoundException
	 * @throws \Doctrine\ORM\ORMException
	 * @throws \Doctrine\ORM\OptimisticLockException
	 */
	public function __invoke(AddParticipantCommandCommand $addParticipantCommandCommand)
	{
		$customer = $this->customerRepository->findOneBy(['id' => $deleteCustomerCommand->getId()]);

		if (!$customer) {
			throw new CustomerNotFoundException('Customer not found');
		}

		$this->customerRepository->remove($customer);
	}
}