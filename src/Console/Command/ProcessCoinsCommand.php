<?php

declare(strict_types=1);

namespace App\Console\Command;

use App\Hurma\CoinProcessor;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name       : 'hurma:process-coins',
    description: 'automatically process coins from google sheet'
)]
class ProcessCoinsCommand extends Command
{
    public function __construct(
        private readonly CoinProcessor $coinProcessor,
        string $name = null
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this
            ->setDefinition(
                new InputDefinition([
                    new InputArgument('sheetId', InputArgument::REQUIRED),
                    new InputArgument('range', InputArgument::REQUIRED),
                    new InputArgument('sheetName', InputArgument::OPTIONAL),
                    new InputOption('dry-run', mode: InputOption::VALUE_OPTIONAL),
                ])
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $result = $this->coinProcessor->process(
            $input->getArgument('sheetId'),
            $input->hasArgument('sheetName') ? $input->getArgument('sheetName') : null,
            $input->getArgument('range'),
            $input->hasOption('dry-run')
        );

        $table = new Table($output);
        $table->setHeaders(['name', 'result']);
        foreach ($result as $row) {
            $table->addRow([
                explode('|', $row)[0] ?? '--',
                explode('|', $row)[1] ?? '--',
            ]);
        }
        $table->render();

        return Command::SUCCESS;
    }
}
