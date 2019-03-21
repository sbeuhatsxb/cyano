<?php

namespace App\Command\Mark;

use App\Service\Mark\ImportOneModuleService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class AppBdmImportModuleCommand extends Command
{
    protected static $defaultName = 'app:bdm:import-module';

    /**
     * @var ImportOneModuleService
     */
    protected $bdmImportOneModuleService;


    public function __construct(
        ImportOneModuleService $bdmImportOneModuleService
    ) {
        parent::__construct();
        $this->bdmImportOneModuleService = $bdmImportOneModuleService;
    }

    protected function configure()
    {
        $this
            ->setName(self::$defaultName)
            ->setDescription('Import one specified module from BDM import')
            ->addOption('module-id')
            ->setHelp(
                'This command launches the import of one single module from the Rossignol\'s Makerting Database. 
                Second parameter will be the chunck number if your last import has crashed.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->bdmImportOneModuleService->execute();
        $this->bdmImportOneModuleService->execute($input->getOption('module-id', null));
    }
}
