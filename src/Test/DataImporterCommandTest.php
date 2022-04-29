<?php
namespace App\Test;

use PHPUnit\Framework\TestCase;
use App\Command\DataImporterCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
 
class DataImporterCommandTest extends TestCase
{
    /** @var CommandTester */
    private $commandTester;
 
    protected function setUp()
    {
 
        $application = new Application();
        $application->add(new DataImporterCommand($this->customerRepositoryMock));
        $command = $application->find('customer');
        $this->commandTester = new CommandTester($command);
    }
 
    protected function tearDown()
    {
        $this->customerRepositoryMock = null;
        $this->commandTester = null;
    }
 
    public function testExecute()
    {
        $id = 1;
        $customer = new Customer();
        $customer->setId($id);
        $customer->setName('Name 1');
        $customer->setDob(new DateTime());
 
        $this->customerRepositoryMock
            ->expects($this->once())
            ->method('findOneById')
            ->with($id)
            ->willReturn($customer);
 
        $this->commandTester->execute(['--id' => $id]);
 
        $this->assertEquals('Name 1', trim($this->commandTester->getDisplay()));
    }
 
    public function testExecuteShouldThrowExceptionForInvalidCustomerId()
    {
        $id = 666;
 
        $this->customerRepositoryMock
            ->expects($this->once())
            ->method('findOneById')
            ->with($id)
            ->willReturn(null);
 
        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage(sprintf('Customer with id [%s] not found', $id));
 
        $this->commandTester->execute(['--id' => $id]);
    }
}
