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
use App\Entity\User;
use App\Repository\ArticleRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

class CartController extends AbstractController
{
    /**
     * @Route("/ajax", name="_addToCart")
     * @Method("POST")
     * @param Request $request
     * @param ArticleRepository $articleRepository
     * @param Session $session
     * @return Response
     */
    public function processOrder(Request $request, ArticleRepository $articleRepository, Session $session)
    {
        if ($request->isXmlHttpRequest()) {

            //Checking if we have any order in $_SESSION else, we create it
            if (!$session->get('order')) {
                $order = new Order();
                $user = $this->get('security.token_storage')->getToken()->getUser();
                if ($user instanceof User) {
                    $order->setLinkedUser($user);
                }
            } else {
                $order = $session->get('order');
            }

            //Thanks to our JS sent via $_POST we retrieve productId and quantity
            $articleId = $request->request->get('articleId');
            $quantity = $request->request->get('quantity');

            $article = $articleRepository->find($articleId);

            $em = $this->getDoctrine()->getManager();

            //Manage existing lines
            if (!$order->getOrderLine()->isEmpty()) {
                foreach ($order->getOrderLine() as $orderLine) {
                    if ($orderLine->getArticle()->getId() == $articleId) {
                        $newQuantity = $orderLine->getQuantity() + $quantity;
                        $orderLine->setQuantity($newQuantity);
                        $em->persist($orderLine);
                        break;
                    } else {
                        $orderLine = new OrderLine();

                        $orderLine->setArticle($article);
                        $orderLine->setQuantity($quantity);
                        $orderLine->setCommand($order);
                        $em->persist($orderLine);
                    }
                }
            } else {
                $orderLine = new OrderLine();

                $orderLine->setArticle($article);
                $orderLine->setQuantity($quantity);
                $orderLine->setCommand($order);
                $em->persist($orderLine);
            }


            //feeding Order with last Orderline
            $order->addOrderLine($orderLine);
            $em->persist($order);

            //Saving those information into the $_SESSION and DB
            $session->set('order', $order);

        }
        return new Response();
    }

    /**
     * @Route("/cart", name="_viewCart")
     * @param Session $session
     * @param ArticleRepository $articleRepo
     * @return Response
     */
    public function view(Session $session, ArticleRepository $articleRepo)
    {
        if ($session->get('order')) {

            $gatheredArticles = [];

            $sessionOrder = $session->get('order');

            /**
             * @var Order $sessionOrder
             */
            foreach ($sessionOrder->getOrderLine() as $orderline) {
                //Unfortunately needed for data initialization
                $article = $articleRepo->find($orderline->getArticle()->getId());
                $orderline->getArticle()->setLinkedImage($article->getLinkedImage());
            };

        } else {
            $sessionOrder = '';
        }

        $user = $this->get('security.token_storage')->getToken()->getUser();

        return $this->render('cart.html.twig', [
            'order' => $sessionOrder,
            'user' => $user
        ]);
    }

    /**
     * @Route("/clear_cart", name="_clearCart")
     * @param Session $session
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function clear(Session $session)
    {
        $session->set('order', '');
        return $this->redirectToRoute('_viewCart');
    }
}