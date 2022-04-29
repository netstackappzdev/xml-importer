<?php
require __DIR__.'/vendor/autoload.php';

use App\Command\DataImporterCommand;
use Symfony\Component\Console\Application;

$application = new Application('echo', '1.0.0');
$command = new DataImporterCommand();

$application->add($command);

//$application->setDefaultCommand($command->getName(), true);
$application->run();