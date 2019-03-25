<?php
/**
 * Created by PhpStorm.
 * User: sebastien
 * Date: 21/02/19
 * Time: 16:41
 */

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Cart;
use App\Repository\ArticleRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class AddToCartAjaxController extends AbstractController
{
    /**
     * @Route("/ajax", name="_addToCard")
     * @Method("POST")
     * @param Request $request
     * @param ArticleRepository $articleRepository
     * @return Response
     */
    public function hop(Request $request, ArticleRepository $articleRepository)
    {
        if ($request->isXmlHttpRequest()) {

            $cart = new Cart();

            $articleId = $request->query->get('articleId');
            $quantity = $request->query->get('quantity');

            $article = $articleRepository->find($articleId);

            $em = $this->getDoctrine()->getManager();

            $cart->setArticles($article);
            $cart->setQuantity($quantity);
            $em->persist($cart);

            return new Response();
        }


    }

}