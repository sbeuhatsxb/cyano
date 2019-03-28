<?php

namespace App\Command\Mark;

use App\Service\Mark\MediaService;
use App\Service\Mark\QueryService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class AppBdmImportAllCommand extends Command
{
    protected static $defaultName = 'app:bdm:import-all';

    /**
     * @var $entityManager
     */
    protected $entityManager;

    /**
     * @var QueryService
     */
    protected $bdmQueryService;


    public function __construct(
        QueryService $bdmQueryService,
        EntityManagerInterface $entityManager
    )
    {
        parent::__construct();
        $this->bdmQueryService = $bdmQueryService;
        $this->entityManager = $entityManager;
    }

    protected function configure()
    {
        $this
            ->setName(self::$defaultName)
            ->setDescription('Launch full BDM import')
            ->setHelp('This command launches the full import of the Rossignol\'s Makerting Database');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->entityManager->getConnection()->getConfiguration()->setSQLLogger(null);


        foreach (QueryService::$modules as $class) {

            $this->bdmQueryService->updateBdmSegments([$class::getBdmModuleNumber()]);
        }
    }
}
