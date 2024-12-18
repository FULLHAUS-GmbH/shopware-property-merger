<?php

declare(strict_types=1);

namespace FULLHAUS\PropertyMerger\Command\Property\Group;

use FULLHAUS\PropertyMerger\Service\Property\Group;
use Shopware\Core\Framework\Uuid\Uuid;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'fullhaus:property-list', description: 'Show all properties groups with their options and IDs.')]
class Show extends Command
{
    public function __construct(private readonly Group $propertyGroupService)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $propertyGroups = $this->propertyGroupService->getAll();

        if (count($propertyGroups) > 0) {
            $table = new Table($output);
            $table->setHeaders(['Group ID', 'Option ID', 'Option Name']);

            foreach ($propertyGroups as $group) {
                $groupId = $this->convertToHex($group->getId());
                foreach ($group->getOptions()->getElements() as $option) {
                    $table->addRow([
                        $groupId,                          // Group ID in hex format
                        $this->convertToHex($option->getId()), // Option ID in hex format
                        $option->getName(),
                    ]);
                }
            }

            $table->render();
        } else {
            $output->writeln('No property groups or options found.');
        }

        return Command::SUCCESS;
    }

    private function convertToHex(string $id): string
    {
        if (strlen($id) === 16) {
            // Convert binary to hex
            return Uuid::fromBytesToHex($id);
        }
        return $id; // Assume it's already hex if not binary
    }
}
