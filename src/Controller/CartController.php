<?php

namespace App\Controller;

use App\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class CartController extends AbstractController
{
    /**
     * @Route("/cart", name="cart")
     */
    public function index(Request $request)
    {

        $session = $request->getSession();

        $cart = $session->get('cart', array());

        if( $cart != '' ) {

            $em = $this->getDoctrine()->getManager();
            $repo = $em->getRepository(Product::class);

            $products = [];
            foreach ($cart as $id => $quantity) {
                $products[] = $repo->find($id);
            }

            if( !isset( $products ) )
            {
                return $this->render('cart/index.html.twig', array(
                    'empty' => true,
                ));
            }

            return $this->render('cart/index.html.twig',     array(
                'products' => $products,
            ));
        } else {
            return $this->render('cart/index.html.twig',     array(
                'empty' => true,
            ));
        }

    }
    /**
     * @Route("/add/{id}", name="cart_add")
     */
    public function addAction($id, Request $request)
    {
        // fetch the cart
       // $em = $this->getDoctrine()->getManager();
        $product =$this->getDoctrine()
            ->getRepository(Product::class)
            ->find($id);

        $session = $request->getSession();
        $cart = $session->get('cart', array());
      //  print_r($cart); die;

        if ( $product == NULL ) {
            $this->get('session')->setFlash('notice', 'This product is not  available in Stores');
            return $this->redirect($this->generateUrl('cart'));
        } else {
            if( isset($cart[$id]) ) {

                $qtyAvailable = 999;

                if( $qtyAvailable >= $cart[$id]  + 1 ) {
                    $cart[$id]  = $cart[$id]  + 1;
                } else {
                    $this->get('session')->setFlash('notice', 'Quantity     exceeds the available stock');
                    return $this->redirect($this->generateUrl('cart'));
                }
            } else {
                // if it doesnt make it 1
                $cart = $session->get('cart', array());
                $cart[$id] = $id;
                $cart[$id]  = 1;
            }

            $session->set('cart', $cart);
            return $this->redirect($this->generateUrl('cart'));

        }


    }

//

    /**
     * @Route("/remove/{id}", name="cart_remove")
     */

    public function removeAction($id)
    {

        $session = $this->get('request_stack')->getCurrentRequest()->getSession();
        $cart = $session->get('cart', array());

        if(!$cart) { $this->redirect( $this->generateUrl('cart') ); }


        if( isset($cart[$id]) ) {

            $cart[$id]  = '0';
            unset($cart[$id]);

        } else {
            return $this->redirect( $this->generateUrl('cart') );
        }

        $session->set('cart', $cart);

        // redirect(index page)
        return $this->redirect($this->generateUrl('cart'));
    }

    /**
     * @Route("/cartupdate/{id}", name="cart_update", methods={"POST"})
     */

//    Need make Json request
    public function updateAction($id, Request $request)
    {

        $value = $request->request->get('item');

        $session = $this->get('request_stack')->getCurrentRequest()->getSession();
        $cart = $session->get('cart', array());

        $cart[$id] = $value;
        $session->set('cart', $cart);

        return $this->redirect( $this->generateUrl('cart') );
    }





}
