<?php
namespace App\Test;

use PHPUnit\Framework\TestCase;
use App\Command\DataImporterCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;


use App\Writer\JsonWriter;
use App\Writer\CsvWriter;
 
class DataImporterCommandTest extends TestCase
{
    /** @var CommandTester */
    private $commandTester;
 
    protected function setUp(): void
    {
        parent::setUp();
        $application = new Application();
        $application->add(new DataImporterCommand());
        // $command = $application->find('customer');
        $command = $application->find('app:xml-data-importer');
        
        $this->commandTester = new CommandTester($command);
    }
 
    protected function tearDown(): void
    {
        $this->customerRepositoryMock = null;
        $this->commandTester = null;
    }
 
    public function testExecute()
    {
        // $this->commandTester->execute([
        //     // pass arguments to the helper
        //     '--fetch' => 'server',
        //     '--to' => 'JSON',

        //     // prefix the key with two dashes when passing options,
        //     // e.g: '--some-option' => 'option_value',
        // ]);
 
        // //$this->assertEquals('Name 1', trim($this->commandTester->getDisplay()));
        $this->testCSVFileWriting();
        $this->testJSONFileWriting();
    }

    public function testFileWriting(): void
    {
        $file = 'output'.date('m-d-Y_H:i:s').'.csv';
        // insert the headers and the rows in the CSV file
        // $csv = Writer::createFromPath($file, 'w');
        // $csv->insertOne($headers);
        // $csv->insertAll($formattedData);

        $writer = new CsvWriter($file, ',', '"', '\\', false);
        $writer->open();
        $outputArray = array(['1','2','3'],['1','2','3'],['1','2','3'],['1','2','3'],['1','2','3'],['1','2','3']);
        foreach($outputArray as $outputdata){
            $writer->write($outputdata);
        }

        $this->assertEquals(
            6,
            $writer->getImportCount()
        );
    }
}
