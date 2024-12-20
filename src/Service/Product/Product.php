<?php

declare(strict_types=1);

namespace FULLHAUS\PropertyMerger\Service\Product;

use Shopware\Core\Content\Product\Aggregate\ProductConfiguratorSetting\ProductConfiguratorSettingEntity;
use Shopware\Core\Content\Property\Aggregate\PropertyGroupOption\PropertyGroupOptionEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\ContainsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use FULLHAUS\PropertyMerger\Service\Property\Product as PropertyProduct;

readonly class Product
{
  private Context $context;

  public function __construct(
      private EntityRepository $productRepository,
      private PropertyProduct  $productPropertyService,
      private Option           $productOptionService,
      private Configurator     $productConfiguratiorService
  )
  {
    $this->context = Context::createDefaultContext();
  }

  public function getContext(): Context
  {
    return $this->context;
  }

  public function findByGroupOption(string $id): EntitySearchResult
  {
    $criteria = new Criteria();
    $criteria->addFilter(
        new ContainsFilter('propertyIds', $id),
    );

    return $this->productRepository->search($criteria, $this->getContext());
  }

  public function findOptions(string $optionId): EntitySearchResult
  {
    $criteria = new Criteria();
    $criteria->addFilter(
        new EqualsFilter('options.id', $optionId),
    );

    return $this->productRepository->search($criteria, $this->getContext());
  }

  public function findConfiguration(string $optionId): EntitySearchResult
  {
    $criteria = new Criteria();
    $criteria->addAssociation('configuratorSettings');
    $criteria->addAssociation('media');
    $criteria->addFilter(
        new EqualsFilter('configuratorSettings.optionId', $optionId),
    );

    return $this->productRepository->search($criteria, $this->getContext());
  }

  public function findConfigurationWithProduct(string $optionId, string $productId): EntitySearchResult
  {
    $criteria = new Criteria();
    $criteria->addAssociation('configuratorSettings');
    $criteria->addAssociation('media');
    $criteria->addFilter(new MultiFilter(
        MultiFilter::CONNECTION_AND,
        [
          new EqualsFilter('configuratorSettings.optionId', $optionId),
          new EqualsFilter('id', $productId),
        ]
    ));

    return $this->productRepository->search($criteria, $this->getContext());
  }

  public function updateProperty(string $id, string $sourceId, array $properties): void
  {
    $this->productPropertyService->delete($id, $sourceId);

    $this->productRepository->update([
      [
          'id' => $id,
          'properties' => $properties,
      ]
    ], $this->getContext());
  }

  public function updateOptions(string $id, string $sourceId, PropertyGroupOptionEntity $option): void
  {
    $this->productOptionService->delete($id, $sourceId);

    $this->productRepository->update([
      [
          'id' => $id,
          'options' => [
            [
              'id' => $option->getId(),
            ]
          ],
      ]
    ], $this->getContext());
  }

  public function updateConfigurator(string $id, PropertyGroupOptionEntity $option, ProductConfiguratorSettingEntity $configurator): void
  {
    $this->productConfiguratiorService->delete($configurator->getId());

    if (0 === $this->findConfigurationWithProduct($option->getId(), $id)->getTotal()) {
      $this->productRepository->update([
        [
            'id' => $id,
            'configuratorSettings' => [
              [
                'id' => $configurator->getId(),
                'optionId' => $option->getId(),
                'price' => $configurator->getPrice(),
                'position' => $configurator->getPosition(),
                'mediaId' => ($configurator->getMedia()) ? $configurator->getMedia()->getId() : null,
                'customFields' => $configurator->getCustomFields(),
              ]
            ],
        ]
      ], $this->getContext());
    }
  }
}
