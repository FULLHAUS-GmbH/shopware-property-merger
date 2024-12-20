<?php

declare(strict_types=1);

namespace FULLHAUS\PropertyMerger\Service\Property;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\Framework\Uuid\Uuid;

readonly class Group
{
    private Context $context;

    public function __construct(private EntityRepository $groupRepository)
    {
        $this->context = Context::createDefaultContext();
    }

    public function getContext(): Context
    {
        return $this->context;
    }

    public function getAll(): array
    {
        return $this->getByIds();
    }

    public function getByIds(array $ids = []): array
    {
        $criteria = new Criteria();

        if (!empty($ids)) {
            $binaryIds = [];
            foreach ($ids as $id) {
                if (!Uuid::isValid($id)) {
                    throw new \InvalidArgumentException("Invalid UUID format: {$id}");
                }
                $binaryIds[] = Uuid::fromHexToBytes($id);
            }
            $criteria->setIds($binaryIds);
        }

        $criteria->addAssociation('options.group')
            ->addSorting(new FieldSorting('name', FieldSorting::ASCENDING));

        return $this->groupRepository
            ->search($criteria, $this->getContext())
            ->getElements();
    }

    public function existsById(string $id): bool
    {
        try {
            // Normalize: Check if ID is already in binary format
            $binaryId = strlen($id) === 32 ? Uuid::fromHexToBytes($id) : $id;

            // Validate UUID length and format
            if (!is_string($binaryId) || strlen($binaryId) !== 16) {
                throw new \InvalidArgumentException("Invalid UUID format: {$id}");
            }

            // Search using the binary ID
            $criteria = new Criteria([$binaryId]);
            return $this->groupRepository->search($criteria, $this->getContext())->getTotal() > 0;

        } catch (\Throwable $e) {
            throw new \InvalidArgumentException("Invalid UUID format: {$id}");
        }
    }

    public function delete(string $id): void
    {
        $this->groupRepository->delete([
            [
                'id' => $id,
            ]
        ], $this->getContext());
    }
}
