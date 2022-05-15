<?php

declare(strict_types=1);

namespace App\Command;

use App\Command\MyDependency;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Psr\Log\LogLevel;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Question\ChoiceQuestion;
use App\Service\XMLDataImporter;


// the "name" and "description" arguments of AsCommand replace the
// static $defaultName and $defaultDescription properties
#[AsCommand(
    name: 'app:xml-data-importer',
    description: 'XML data importer to (CSV,JSON,Google Sheet or SQlite)',
    hidden: false,
    aliases: ['app:xml-data-importer']
)]

class DataImporterCommand extends Command
{

    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:xml-data-importer';
    // the command description shown when running "php bin/console list"
    protected static $defaultDescription = 'XML data importer to (CSV,JSON,GoogleSheet or SQlite)';
   
    public function __construct(
        private XMLDataImporter $xmlDataImporter,
    ) {

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            // the command help shown when running the command with the "--help" option
            ->setHelp('This command allows you to Import a XML file into (CSV, JSON, Google Sheet & SQlite)')
            ->addOption(
                'fetch',
                null,
                InputOption::VALUE_REQUIRED,
                'fetch XML from local or server?',
                'local' // this is the new default value, instead of null
            )
            ->addOption(
                'to',
                null,
                InputOption::VALUE_REQUIRED,
                'want to store the data? (SQlite, Google Spreadsheet, JSON file etc).',
                'CSV' // this is the new default value, instead of null
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {       
        $fetch =  $input->getOption('fetch');
        $to = $input->getOption('to');

        $verbosityLevelMap = [
            LogLevel::NOTICE => OutputInterface::VERBOSITY_NORMAL,
            LogLevel::INFO   => OutputInterface::VERBOSITY_NORMAL,
        ];
        
        $logger = new ConsoleLogger($output, $verbosityLevelMap);

        $myDependency = new MyDependency($logger);

        $output->writeln("Fetching XML from $fetch");
        $result = $this->xmlDataImporter->convert($fetch,$to,$myDependency);
        if(!$result){
            return Command::FAILURE;
        }
    
        return Command::SUCCESS;
    }   
}