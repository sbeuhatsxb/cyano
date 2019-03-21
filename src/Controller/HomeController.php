<?php
/**
 * Created by PhpStorm.
 * User: sebastien
 * Date: 22/02/19
 * Time: 13:53
 */

namespace App\Controller;

use App\Repository\ArticleRepository;
use App\Repository\BrandRepository;
use App\Repository\CategoryRepository;
use App\Service\PaginationService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\LastArticlesService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Entity\Article;
use App\Entity\Brand;
use App\Entity\Category;
use App\Entity\Currency;


class HomeController extends Controller
{
    const PAGE_LIMIT = 9;
    const PAGE_NUMBER = 1;

    /**
     * @var LastArticlesService
     */
    protected $lastArticlesService;

    /**
     * @var PaginationService
     */
    protected $paginationService;


    public function __construct(LastArticlesService $lastArticlesService, PaginationService $paginationService)
    {
        $this->lastArticlesService = $lastArticlesService;
        $this->paginationService = $paginationService;
    }

    /**
     * @Route("/",  name="index")
     * @param Request $request
     * @param ArticleRepository $articleRepository
     * @param CategoryRepository $categorieRepo
     * @param BrandRepository $brandRepo
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Request $request, ArticleRepository $articleRepository, CategoryRepository $categorieRepo, BrandRepository $brandRepo)
    {

        $lastArticles = $articleRepository->findLastTwelveArticles();
        $categories = $categorieRepo->findAll();
        $brands = $brandRepo->findAll();

        $articles = $this->paginationService->paginate($lastArticles, self::PAGE_NUMBER, self::PAGE_LIMIT);

        return $this->render('article_list.html.twig', [
            'articles' => $articles,
            'categories' => $categories,
            'brands' => $brands
        ]);
    }

    /**
     * @Route("/filtres/{shortname}/{filter}",  name="filtered_list")
     * @param $filter
     * @param $shortname
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function filteredList($filter = null, $shortname = null, CategoryRepository $categorieRepo, BrandRepository $brandRepo, Request $request)
    {
        $categories = $categorieRepo->findAll();
        $brands = $brandRepo->findAll();
        $classname = 'App\Entity\\'.$request->attributes->get('shortname');

        $em = $this->getDoctrine()->getManager();

        $getFilter = $em->getRepository($classname)->findBy(['name' => $filter]);

        if (!class_exists($classname)
            || !in_array($shortname, ['Brand', 'Category'])
            || empty($getFilter)
        ) {
            throw $this->createNotFoundException('Désolé, ce filtre n\'existe pas...');
        }

        $filterId = $getFilter[0]->getId();

        $lastArticles = $this->lastArticlesService->getLastArticles($shortname, $filterId);

        $articles = $this->paginationService->paginate($lastArticles, 1, 12);

        if ($articles->getTotalItemCount() == 0) {
            throw $this->createNotFoundException('Aucun résultat selon les critères sélectionnés...');
        };

        return $this->render('article_list.html.twig', [
            'articles' => $articles,
            'shortname' => $shortname,
            'filter' => $filter,
            'categories' => $categories,
            'brands' => $brands

        ]);
    }

}