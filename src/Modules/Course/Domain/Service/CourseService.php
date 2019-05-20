<?php

namespace ILIAS\Modules\Course\Domain\Service;

use App\Domain\Command\Customer\DeleteCustomerCommand;
use App\Domain\Exception\Customer\CriteriaNotAllowedException;
use App\Domain\Query\Customer\GetCustomerListQuery;
use App\Domain\Query\Customer\GetCustomerQuery;

use Symfony\Component\Messenger\MessageBusInterface;

class CourseService implements CustomerServiceInterface
{
	/** @var MessageBusInterface  */
	private $messageBus;

	public function __construct(
		MessageBusInterface $messageBus
	) {
		$this->messageBus = $messageBus;
	}

	/**
	 * @param array $criteria
	 *
	 * @return mixed
	 *
	 * @throws CriteriaNotAllowedException
	 */
	public function getByCriteria(array $criteria)
	{
		$getCustomerQueryList = new GetCustomerListQuery();

		foreach ($criteria as $key => $value) {
			$method = 'set' . ucfirst($key);

			if (!method_exists($getCustomerQueryList, $method)) {
				throw new CriteriaNotAllowedException(sprintf('Parameter %s not allowed', $key));
			}

			$getCustomerQueryList->$method($value);
		}

		return $this->messageBus->dispatch($getCustomerQueryList);
	}

	public function get(string $id)
	{
		return $this->messageBus->dispatch(
			new GetCustomerQuery($id)
		);
	}

	public function delete(string $id)
	{
		$this->messageBus->dispatch(
			new DeleteCustomerCommand($id)
		);
	}
}