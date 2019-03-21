<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Currency;
use App\Entity\Category;
use App\Entity\Brand;
use App\Repository\BrandRepository;
use App\Repository\CategoryRepository;
use App\Service\LastArticlesService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/article")
 */
class ArticleController extends Controller
{

    /**
     * @var LastArticlesService
     */
    protected $lastArticlesService;

    public function __construct(LastArticlesService $lastArticlesService)
    {
        $this->lastArticlesService = $lastArticlesService;
    }

    /**
     * @Route("/{id}", name="article_show", methods={"GET", "POST"})
     * @param Article $article
     * @param Request $request
     * @param CategoryRepository $categorieRepo
     * @param BrandRepository $brandRepo
     * @return Response
     * @throws \ReflectionException
     */
    public function show(Article $article, Request $request, CategoryRepository $categorieRepo, BrandRepository $brandRepo): Response
    {
        $categories = $categorieRepo->findAll();
        $brands = $brandRepo->findAll();
        $brand = new \ReflectionClass(Brand::class);
        //required for class name
        $brandClass = $brand->getName();
        //required for path
        $brandShortname = $brand->getShortName();

        $category = new \ReflectionClass(Category::class);
        //ibid
        $categoryClass = $category->getName();
        //ibid
        $categoryShortname = $category->getShortName();

        return $this->render('article/show.html.twig', [
            'article' => $article,
            'brandClass' => $brandClass,
            'categoryClass' => $categoryClass,
            'brandShortname' => $brandShortname,
            'categoryShortname' => $categoryShortname,
            'categories' => $categories,
            'brands' => $brands

        ]);
    }
}
