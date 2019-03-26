<?php
/**
 * Created by PhpStorm.
 * User: sebastien
 * Date: 21/02/19
 * Time: 16:41
 */

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderLine;
use App\Repository\ArticleRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

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

            if(!isset($order)){
                $order = new Order();
                $user = $this->get('security.token_storage')->getToken()->getUser();
                $order->setLinkedUser($user);
            }

            $orderLine = new OrderLine();

            $articleId = $request->request->get('articleId');
            $quantity = $request->request->get('quantity');

            $article = $articleRepository->find($articleId);

            $em = $this->getDoctrine()->getManager();

            $orderLine->setArticle($article);
            $orderLine->setQuantity($quantity);
            $orderLine->setCommand($order);
            $em->persist($orderLine);

            return new Response();
        }
    }


}