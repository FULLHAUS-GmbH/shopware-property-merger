<?php

declare(strict_types=1);

namespace FULLHAUS\PropertyMerger\Service\Property;

use Shopware\Core\Content\Property\Aggregate\PropertyGroupOption\PropertyGroupOptionEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;

readonly class Option
{
  private Context $context;

  public function __construct(private EntityRepository $groupOptionRepository)
  {
    $this->context = Context::createDefaultContext();
  }

  public function getContext(): Context
  {
    return $this->context;
  }

    public function getOptionById(string $id): ?PropertyGroupOptionEntity
    {
        $criteria = new Criteria([$id]);
        return $this->groupOptionRepository->search($criteria, $this->getContext())->first();
    }

  public function update(string $groupId, string $optionId): void
  {
    $this->groupOptionRepository->update([
      [
          'id' => $optionId,
          'groupId' => $groupId,
      ]
    ], $this->getContext());
  }

  public function delete(string $id): void
  {
    $this->groupOptionRepository->delete([
      [
          'id' => $id,
      ]
    ], $this->getContext());
  }
}
