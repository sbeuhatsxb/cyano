<?php

namespace App\Command\Mark;

use App\Entity\Catalog\Mark\Product;
use App\Service\Mark\QueryService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class AppBdmImportSingleProductCommand extends Command
{
    protected static $defaultName = 'app:bdm:import-product';

    /**
     * @var $entityManager
     */
    protected $entityManager;

    /**
     * @var QueryService
     */
    protected $bdmQueryService;


    public function __construct(
        EntityManagerInterface $entityManager,
        QueryService $bdmQueryService
    )
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->bdmQueryService = $bdmQueryService;
    }

    protected function configure()
    {
        $this
            ->setName(self::$defaultName)
            ->setDescription('Import or display one specified PRODUCT from BDM import')
            ->setHelp(
                'This command launches the import of one single PRODUCT from the Rossignol\'s Makerting Database.'
            )
            ->addArgument('product-code', InputArgument::REQUIRED, 'The product code (i.e. RLFMY24 or GRHMH05)');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->entityManager->getConnection()->getConfiguration()->setSQLLogger(null);
        $code = $input->getArgument('product-code');
        $this->bdmQueryService->runClientForOneProduct($code, Product::$bdm_module);
    }
}
