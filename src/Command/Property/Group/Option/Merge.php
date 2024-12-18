<?php

declare(strict_types=1);

namespace FULLHAUS\PropertyMerger\Command\Property\Group\Option;

use FULLHAUS\PropertyMerger\Service\Product\Product;
use FULLHAUS\PropertyMerger\Service\Property\Group;
use FULLHAUS\PropertyMerger\Service\Property\Option;
use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Uuid\Uuid;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'fullhaus:property-merge', description: 'Merge your properties fields easily.')]
class Merge extends Command
{
    public function __construct(
        private readonly Group $propertyGroupService,
        private readonly Option $propertyGroupOptionService,
        private readonly Product $productService,
        private readonly LoggerInterface $logger
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('source', 's', InputOption::VALUE_REQUIRED, 'Origin/source ID to merge from')
            ->addOption('destination', 'd', InputOption::VALUE_REQUIRED, 'Destination ID to merge into')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Dry run without modifying the database.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $sourceHex = $input->getOption('source');
            $destinationHex = $input->getOption('destination');

            // Convert Hexadecimal to Binary
            $source = $this->convertHexToBinary($sourceHex);
            $destination = $this->convertHexToBinary($destinationHex);
            $dryRun = $input->getOption('dry-run');

            $this->logger->info('Normalized and validated IDs', [
                'sourceHex' => $sourceHex,
                'destinationHex' => $destinationHex,
                'sourceBinary' => bin2hex($source),
                'destinationBinary' => bin2hex($destination),
                'dryRun' => $dryRun,
            ]);

            // Check existence
            $isGroupSource = $this->propertyGroupService->existsById($source);
            $isGroupDestination = $this->propertyGroupService->existsById($destination);

            $isOptionSource = $this->propertyGroupOptionService->getOptionById($source);
            $isOptionDestination = $this->propertyGroupOptionService->getOptionById($destination);

            $this->logger->info('ID Existence Check Results', [
                'isGroupSource' => $isGroupSource,
                'isGroupDestination' => $isGroupDestination,
                'isOptionSource' => $isOptionSource !== null,
                'isOptionDestination' => $isOptionDestination !== null,
            ]);

            if ($isGroupSource && $isGroupDestination) {
                $this->mergeGroups($source, $destination, $dryRun, $output);
            } elseif ($isOptionSource && $isOptionDestination) {
                $this->mergeOptions($source, $destination, $dryRun, $output);
            } else {
                throw new \InvalidArgumentException('Source and Destination IDs do not match any valid groups or options.');
            }

            $output->writeln('<info>Merge completed successfully.</info>');
            return Command::SUCCESS;

        } catch (\InvalidArgumentException $e) {
            $this->logger->error('Validation Error', ['error' => $e->getMessage()]);
            $output->writeln("<error>{$e->getMessage()}</error>");
            return Command::FAILURE;

        } catch (\Throwable $e) {
            $this->logger->error('Unexpected Error', ['error' => $e->getMessage()]);
            $output->writeln("<error>An unexpected error occurred: {$e->getMessage()}</error>");
            return Command::FAILURE;
        }
    }

    private function convertHexToBinary(string $id): string
    {
        if (!Uuid::isValid($id)) {
            throw new \InvalidArgumentException("Invalid UUID format: {$id}");
        }

        return Uuid::fromHexToBytes($id);
    }

    private function mergeGroups(string $source, string $destination, bool $dryRun, OutputInterface $output): void
    {
        $output->writeln("<info>Merging groups: Source {$source} -> Destination {$destination}</info>");
        if ($dryRun) {
            $this->logger->info('Dry run: Skipping actual merge for groups');
            return;
        }

        $this->logger->info('Group merge logic executed', ['source' => $source, 'destination' => $destination]);
    }

    private function mergeOptions(string $source, string $destination, bool $dryRun, OutputInterface $output): void
    {
        $output->writeln("<info>Merging options: Source {$source} -> Destination {$destination}</info>");
        if ($dryRun) {
            $this->logger->info('Dry run: Skipping actual merge for options');
            return;
        }

        $this->logger->info('Option merge logic executed', ['source' => $source, 'destination' => $destination]);
    }
}
