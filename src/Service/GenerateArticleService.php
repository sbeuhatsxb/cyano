<?php
/**
 * Created by PhpStorm.
 * User: sebastien
 * Date: 05/03/19
 * Time: 16:23
 */

namespace App\Service;

use App\Entity\Article;
use App\Entity\Brand;
use App\Entity\Catalog\Mark\Product;
use App\Entity\Catalog\Mark\TechnoMedia;
use App\Entity\Category;
use App\Entity\Currency;
use App\Entity\Image;
use App\Entity\PriceModifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class GenerateArticleService
{

    /**
     * @var EntityManagerInterface $entityManager
     */
    protected $entityManager;

    function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function generate()
    {
        $images = $this->entityManager->getRepository(Image::class)->findAll();

        $i = 0;
        foreach ($images as $image) {
            $linkedProduct = $image->getText();

            $product = $this->entityManager->getRepository(Product::class)->find($linkedProduct);

            //Create categorie
            $randomCategory = $this->categoryInventor();
            $categoryRepo = $this->entityManager->getRepository(Category::class)->findOneBy(['name' => $randomCategory]);
            if (!isset($categoryRepo)) {
                $category = new Category();
                $category->setName($randomCategory);
                $this->entityManager->persist($category);
                $this->entityManager->flush();
            } else {
                $category = $categoryRepo;
            }

            $emphase = $this->emphase();

            $priceMod = $this->priceModifierConverter('EUR');
            $ratio = $priceMod->getRatio();
            $price = $this->generatePrice() * $ratio;
            if ($this->brandConverter($product->getBrand())->getName() != null) {
                $title = $this->articleStr($emphase) . $emphase . ' ' . $this->brandConverter($product->getBrand())->getName() . ' ' . $product->getModelCode();
            } else {
                $title = $this->articleStr($emphase) . $emphase . ' sorti dont ne sait où ' . $product->getModelCode();
            };

            $input = new Article();
            $input->setContent($this->randParagraph());
            $input->setTitle($title);
            $input->setSlug($title);
            $input->addLinkedBrand($this->brandConverter($product->getBrand()));
            $input->addLinkedCurrency($this->currencyConverter('EUR'));
            $input->setPrice($this->priceCalculator($input->getLinkedCurrency()[0]));
            $input->addLinkedCategory($category);
            $input->setLinkedImage($image);
            $input->setIsPublished(true);

            $this->entityManager->persist($input);

            $i++;

            if ($i >= 20) {
                $this->entityManager->flush();
                $this->entityManager->clear();
                $i = 0;
            }
        }
    }

    private function randParagraph()
    {
        $finder = new Finder();
        $finder->in('public/')->files()->name('alrdtp.txt');
        foreach ($finder as $file) {
            $contents = $file->getContents();
            $strings = explode("\r\n", $contents);
        }

        $randomParagraph = rand(0, count($strings));

        if ($randomParagraph != "") {
            return $strings[$randomParagraph];
        } else {
            $this->randParagraph();
        }
    }

    private function emphase()
    {
        $emphaticArray = [
            'admirable',
            'adorable',
            'élégant',
            'épatant',
            'étonnant',
            'étourdissant',
            'beau',
            'bon',
            'charmant',
            'délicieux',
            'divin',
            'doré',
            'enchanteur',
            'enivrant',
            'euphorique',
            'excellent',
            'excentrique',
            'exquis',
            'extra',
            'extraordinaire',
            'fabuleux',
            'féerie',
            'féerique',
            'fantasmagorique',
            'fantastique',
            'idéal',
            'impressionnant',
            'incroyable',
            'inouï',
            'joli',
            'magique',
            'magnifique',
            'merveilleux',
            'miraculeux',
            'mirifique',
            'mirobolant',
            'muscadin',
            'paradisiaque',
            'parfait',
            'phénoménal',
            'prestigieux',
            'prodigieux',
            'rare',
            'ravissant',
            'remarquable',
            'romanesque',
            'saisissant',
            'satané',
            'sensationnel',
            'souverain',
            'splendide',
            'stupéfiant',
            'sublime',
            'super',
            'superbe',
            'surprenant',
            'terrible',
            'thaumaturgique'
        ];

        $randomEmphatic = rand(0, count($emphaticArray) - 1);

        return $emphaticArray[$randomEmphatic];
    }

    private function articleStr($word)
    {
        $vowels = ['a', 'e', 'i', 'o', 'u', 'é'];
        $firstLetter = mb_substr($word, 0, 1);
        if (in_array($firstLetter, $vowels)) {
            return 'L`';
        } else {
            return 'Le ';
        };
    }

    /**
     * @param $brandStr
     * @return object|null
     */
    private function brandConverter($brandStr)
    {

        $brandsConvArray = [
            'DYNA' => 'DYNASTAR',
            'ROSS' => 'ROSSIGNOL',
            'VERT' => 'VERTICAL',

            'ADVT' => 'ADVENTURE',
            'BROO' => 'BROO',
            'CAMP' => 'CAMP',
            'CORE' => 'CORE',
            'DC__' => 'DC',
            'DEDA' => 'DEDA',
            'ELAN' => 'ELAN',
            'FELT' => 'FELT',
            'FERR' => 'FERR',
            'FISC' => 'FISC',
            'FIZI' => 'FIZI',
            'GNU_' => 'GNU',
            'GPO_' => 'GPO',
            'HAMM' => 'HAMM',
            'KERM' => 'KERM',
            'LANG' => 'LANG',
            'LOOK' => 'LOOK',
            'MAVI' => 'MAVI',
            'MOVE' => 'MOVE',
            'QUIK' => 'QUIK',
            'RAID' => 'RAID',
            'ROXY' => 'ROXY',
            'ROYA' => 'ROYA',
            'SHIM' => 'SHIM',
            'SODI' => 'SODI',
            'SRAM' => 'SRAM',
            'TECH' => 'TECH',
            'TIME' => 'TIME',
            'UNIC' => 'UNIC',
            'WEDZ' => 'WEDZ',
            'ZEFA' => 'ZEFA',
            'ZIPP' => 'ZIPP',
            'ZZZZ' => 'ZZZZ',

        ];

        foreach ($brandsConvArray as $key => $value) {
            if ($key == $brandStr) {
                $brandStr = $value;

                $brand = $this->entityManager->getRepository(Brand::class)->findOneBy(array('name' => $brandStr));
                if (isset($brand)) {
                    return $brand;
                } else {
                    $brand = new Brand();
                    $brand->setName($value);
                    $brand->setCode($key);
                    $this->entityManager->persist($brand);
                    $this->entityManager->flush();
                    return $brand;
                }
            }
        }


    }

    /**
     * @param $currencyStr
     * @return Currency $currency
     */
    private function currencyConverter($currencyStr)
    {
        $currency = $this->entityManager->getRepository(Currency::class)->findOneBy(array('code' => $currencyStr));
        return $currency;

    }

    private function priceModifierConverter($priceCode)
    {
        $priceModifier = $this->entityManager->getRepository(PriceModifier::class)->findOneBy(array('code' => $priceCode));
        return $priceModifier;

    }

    private function generatePrice()
    {
        $random = rand(1, 3);
        if ($random == 1) {
            return rand(100, 999) + 0.99;
        } elseif ($random == 2) {
            return rand(10, 99) + 0.99;
        } else {
            return rand(1, 9) + 0.99;
        }
    }

    private function priceCalculator(Currency $linkedCurrency)
    {
        $currency = $linkedCurrency->getCode();
        $priceMod = $this->priceModifierConverter($currency);
        $ratio = $priceMod->getRatio();
        $price = $this->generatePrice() * $ratio;

        return $price;
    }


    private function categoryInventor()
    {
        $categories = [
            'hommes',
            'femmes',
            'enfants',
            'ski alpin',
            'ski nordique',
            'vélo',
            'pas d\'ailes',
        ];

        $random = rand(0, count($categories) - 1);
        return $categories[$random];
    }

}