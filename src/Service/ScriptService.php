<?php
/**
 * Created by PhpStorm.
 * User: sebastien
 * Date: 05/03/19
 * Time: 16:23
 */

namespace App\Service;

use App\Entity\Article;
use App\Entity\Catalog\Mark\Product;
use App\Entity\Catalog\Mark\ProductMedia;
use App\Entity\Catalog\Mark\TechnoMedia;
use App\Entity\Image;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ScriptService
{

    /**
     * @var EntityManagerInterface $entityManager
     */
    protected $entityManager;

    function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }


    public function script()
    {

        $batchSize = 20;
        $i = 0;
        $q = $this->entityManager->createQuery('select u from App\Entity\Article u');
        $j = 1;

        $iterableResult = $q->iterate();
        foreach ($iterableResult as $product) {
            /**
             * @var Article $clone
             */
            $product = $product[0];
            $clone = new Article();
            $clone->setTitle($product->getTitle());
            $clone->setContent($product->getContent());
            $clone->setSlug($product->getSlug());
            $clone->setIsPublished(true);
            $clone->setPreview($product->getPreview());
            $clone->setPrice($product->getPrice());
            foreach ($product->getLinkedCategory() as $category) {
                $clone->addLinkedCategory($category);
            }
            foreach ($product->getLinkedBrand() as $brand) {
                $clone->addLinkedBrand($brand);
            }

            $this->entityManager->persist($clone);
            $j++;
            if ($j >= 50) {
                dump("FLUSHED !!!");
                $this->entityManager->detach($clone);

                $this->entityManager->flush();
                $this->entityManager->clear();
                $j = 0;
            }
        }

    }
    //    public function script()
    //    {
    //        $randomParagraph = $this->randParagraph();
    //
    //        $products =  $this->entityManager->getRepository(Article::class)->findAll();
    //        $i =0;
    //
    //
    //        foreach($products as $product){
    //            $product->setContent($randomParagraph);
    //            $this->entityManager->persist($product);
    //            $i++;
    //            if($i >= 20){
    //                $this->entityManager->flush();
    //                $i = 0;
    //            }
    //        }
    //
    //    }

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


    private function junkyard()
    {

        $fileSystem = new Filesystem();

        $finder = new Finder();
        $finder->in('public/uploads/images/manual')->files();


        $imagesRepo = $this->entityManager->getRepository(Image::class);

        foreach ($imagesRepo as $truc) {
            $this->entityManager->persist($truc->remove());
        }

        $imageLocationRepo = $this->entityManager->getRepository(ProductMedia::class)->findAll();
        $i = 0;
        foreach ($imageLocationRepo as $truc) {
            $url = $truc->getOriginalName();
            $productMediaLabel = $truc->getLabel();
            $arr = explode(".", $productMediaLabel);
            $productMediaLabel = $arr[0];

            if (strpos($productMediaLabel, '_') !== false) {
                $productMediaLabel = explode("_", $productMediaLabel);
            }

            $productLabel = $productMediaLabel[0];

            //            $product= $this->entityManager->getRepository(Product::class)->findOneBy(['modelCode' => $productLabel]);
            //            if($product){
            //                $product->addMedium($truc);
            //                $this->entityManager->persist($product);
            //                $i++;
            //                dump($i);
            //                if($i >= 20){
            //                    dump('flushed !');
            //                    $this->entityManager->flush();
            //                    $i = 0;
            //                    continue;
            //                }
            //
            //            };
            $products = $this->entityManager->getRepository(Product::class)->findall();

            foreach ($products as $product) {
                if (!is_null($product->getMedia()[0])) {


                    $filename = 'http://bdm.grouperossignol.com' . $product->getMedia()[0]->getUrl();

                    $newFileName = $product->getMedia()[0]->getLabel();

                    if ($newFileName) {
                        $fileSystem->copy($filename, 'public/uploads/images/' . $newFileName);
                        $image = new Image();
                        $image->setImage($newFileName);
                        $this->entityManager->persist($image);
                    }

                    dd($image);

                    $this->entityManager->persist($image);
                    $i++;
                    if ($i >= 20) {
                        $this->entityManager->flush();
                        $i = 0;
                    }


                }
            }

        }


        dd('sbeuh');
        $i = 0;

        foreach ($imageLocationRepo as $value) {


        }

    }


}