<?php

declare(strict_types=1);

namespace FULLHAUS\PropertyMerger\Service\Product;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;

readonly class Configurator
{
  private Context $context;

  public function __construct(private EntityRepository $productConfigurationRepository)
  {
    $this->context = Context::createDefaultContext();
  }

  public function getContext(): Context
  {
    return $this->context;
  }

  public function delete(string $id): void
  {
    $this->productConfigurationRepository->delete([
      [
          'id' => $id,
      ]
    ], $this->getContext());
  }
}
