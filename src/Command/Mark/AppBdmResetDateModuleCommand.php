<?php

namespace App\Command\Mark;

use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use App\Entity\Catalog\Mark\Awards;
use App\Entity\Catalog\Mark\BGroupSegment;
use App\Entity\Catalog\Mark\BrandSegment;
use App\Entity\Catalog\Mark\Category1Segment;
use App\Entity\Catalog\Mark\Category2Segment;
use App\Entity\Catalog\Mark\Category3Segment;
use App\Entity\Catalog\Mark\CategoryB2BSegment;
use App\Entity\Catalog\Mark\CategorySportSegment;
use App\Entity\Catalog\Mark\CollectionSegment;
use App\Entity\Catalog\Mark\GenderSegment;
use App\Entity\Catalog\Mark\InfoModuleBdm;
use App\Entity\Catalog\Mark\MiscLabelsSegment;
use App\Entity\Catalog\Mark\Package;
use App\Entity\Catalog\Mark\PackageProduct;
use App\Entity\Catalog\Mark\Product;
use App\Entity\Catalog\Mark\ProductMedia;
use App\Entity\Catalog\Mark\Season;
use App\Entity\Catalog\Mark\SpecDefinitionSegment;
use App\Entity\Catalog\Mark\SpecLabelSegment;
use App\Entity\Catalog\Mark\TechnoMedia;
use App\Entity\Catalog\Mark\TechnoSegment;
use App\Entity\Catalog\Mark\TypesB2CSegment;
use App\Entity\Catalog\Mark\TypeSegment;
use Doctrine\ORM\EntityManagerInterface;


class AppBdmResetDateModuleCommand extends Command
{
    protected static $defaultName = 'app:bdm:import-reset';

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var OutputInterface
     */
    protected $output;

    public function __construct(
        EntityManagerInterface $entityManager
    )
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function configure()
    {
        $this
            ->setName(self::$defaultName)
            ->setDescription('Reset the updating date of one specific module - BDM - DEBUG')
            ->addOption('module', 'm', InputOption::VALUE_OPTIONAL, 'Targeted module', null)
            ->setHelp(
                'This command launches Reset the updating date of one specific BDM module. 
                For debugging purposes only.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->entityManager->getConnection()->getConfiguration()->setSQLLogger(null);
        $this->output = $output;

        $moduleSelected = $input->getOption('module');

        if ($moduleSelected === null) {
            $this->displayHelp();
            return;
        }

        if ($moduleSelected === '0') {
            $modulesSelected = array_values($this->getModulesMapping());
        } else {
            $modulesSelected = [$this->getModulesMapping()[$moduleSelected]];
        }

        $output->writeln("/!\ This will update 'updated_at' column for every row in app_entity for selected(s) module(s), are you sure you want to proceed? [yes/no](no)");
        $prompt = strtoupper(mb_convert_encoding(rtrim(fgets(STDIN)), 'UTF-8'));

        if ($prompt !== 'YES') {
            $output->writeln("Aborted");
            return;
        }

        // -- Update last sync date
        foreach ($modulesSelected as $moduleInput) {

            /** @var InfoModuleBdm $lastLocalUpdate */
            $lastLocalUpdate = $this->entityManager->getRepository(InfoModuleBdm::class)
                ->findOneBy(
                    ['moduleNumber' => $moduleInput]
                );

            $lastLocalUpdate->setLastUpdateDate(null);
            $this->entityManager->persist($lastLocalUpdate);
        }
        $this->entityManager->flush();

        // -- Update last BDM update on every BDM entity
        $rawSql = "UPDATE `entity` SET `updated_at` = '1970-01-01 00:00:00'";

        if ($moduleSelected !== '0') {
            $rawSql .= " WHERE `entity_type` = '{$modulesSelected[0]}'";
        }

        try {
            $stmt = $this->entityManager->getConnection()->prepare($rawSql);

            if (!$stmt->execute()) {
                $output->writeln('[ERROR]An error occured while executing database query.');
            }

        } catch (\Exception $e) {
            $output->writeln("[ERROR] Exception occured: ");
            dd($e);

        }

        $output->writeln("Done !");
    }

    /**
     * Displays help
     */
    protected function displayHelp(): void
    {
        $this->output->writeln(
            PHP_EOL .
            '**************************************************************' . PHP_EOL .
            '******* Select a module to reset with option "module"  *******' . PHP_EOL .
            '**************************************************************' . PHP_EOL .

            PHP_EOL .
            '0 => All Modules' . PHP_EOL .
            '1 => CategorySportSegment' . PHP_EOL .
            '2 => GenderSegment' . PHP_EOL .
            '3 => BGroupSegment' . PHP_EOL .
            '4 => TypeSegment' . PHP_EOL .
            '5 => TypesB2CSegment' . PHP_EOL .
            '6 => Category1Segment' . PHP_EOL .
            '7 => CategoryB2BSegment' . PHP_EOL .
            '8 => Category3Segment' . PHP_EOL .
            '9 => CollectionSegment' . PHP_EOL .
            '10 => SpecLabelSegment' . PHP_EOL .
            '11 => MiscLabelsSegment' . PHP_EOL .
            '12 => SpecDefinitionSegmentRepository' . PHP_EOL .
            '13 => Category2Segment' . PHP_EOL .
            '14 => TechnoSegment' . PHP_EOL .
            '15 => TechnoMedia' . PHP_EOL .
            '16 => Season' . PHP_EOL .
            '17 => BrandSegment' . PHP_EOL .
            '18 => Awards' . PHP_EOL .
            '19 => Package' . PHP_EOL .
            '20 => Product' . PHP_EOL .
            '21 => PackageProduct' . PHP_EOL .
            '22 => ProductMedia' . PHP_EOL
        );
    }

    /**
     * @return array
     */
    protected function getModulesMapping(): array
    {
        return [
            "1" => CategorySportSegment::getBdmModuleNumber(),
            "2" => GenderSegment::getBdmModuleNumber(),
            "3" => BGroupSegment::getBdmModuleNumber(),
            "4" => TypeSegment::getBdmModuleNumber(),
            "5" => TypesB2CSegment::getBdmModuleNumber(),
            "6" => Category1Segment::getBdmModuleNumber(),
            "7" => CategoryB2BSegment::getBdmModuleNumber(),
            "8" => Category3Segment::getBdmModuleNumber(),
            "9" => CollectionSegment::getBdmModuleNumber(),
            "10" => SpecLabelSegment::getBdmModuleNumber(),
            "11" => MiscLabelsSegment::getBdmModuleNumber(),
            "12" => SpecDefinitionSegment::getBdmModuleNumber(),
            "13" => Category2Segment::getBdmModuleNumber(),
            "14" => TechnoSegment::getBdmModuleNumber(),
            "15" => TechnoMedia::getBdmModuleNumber(),
            "16" => Season::getBdmModuleNumber(),
            "17" => BrandSegment::getBdmModuleNumber(),
            "18" => Awards::getBdmModuleNumber(),
            "19" => Package::getBdmModuleNumber(),
            "20" => Product::getBdmModuleNumber(),
            "21" => PackageProduct::getBdmModuleNumber(),
            "22" => ProductMedia::getBdmModuleNumber(),
        ];
    }
}
