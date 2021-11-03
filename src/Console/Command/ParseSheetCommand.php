<?php

declare(strict_types=1);

namespace App\Console\Command;

use App\Google\Sheets;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name       : 'google:parse-sheet',
    description: 'google: parse sheet'
)]
class ParseSheetCommand extends Command
{
    public function __construct(
        private readonly Sheets $sheets,
        string $name = null
    ) {
        parent::__construct($name);
    }

    // ----------------------------------------

    protected function configure(): void
    {
        $this
            ->setDefinition(
                new InputDefinition([
                    new InputArgument('spreadsheetId', InputArgument::REQUIRED),
                    new InputArgument('range', InputArgument::REQUIRED),
                    new InputArgument('sheetName', InputArgument::OPTIONAL),
                ])
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $range = $this->sheets->getRange(
            $input->getArgument('spreadsheetId'),
            $input->hasArgument('sheetName') ? $input->getArgument('sheetName') : null,
            $input->getArgument('range'),
        );

        $table = new Table($output);
        $table->setHeaders($range[0]);
        foreach (array_slice($range, 1) as $row) {
            $table->addRow($row);
        }
        $table->render();

        return Command::SUCCESS;
    }
}
