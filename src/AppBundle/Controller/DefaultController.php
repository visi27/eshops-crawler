<?php

namespace AppBundle\Controller;

use AppBundle\Crawler\WebCrawler;
use AppBundle\Entity\ShopCategory;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction()
    {

        $em = $this->getDoctrine()->getManager();
        $shop = $em->getRepository('AppBundle:Shop')->findOneBy(['id' => 1]);

        $shopCategories = $em->getRepository('AppBundle:ShopCategory')->findBy(['shop' => 1, 'process' => 1]);
//
//        $url = "https://dyqani.shpresa.al/celulare-47-c";
//
//        $crawler = new WebCrawler($url);
//        $products = $crawler->getProducts();
//        $pages = $crawler->getPages();

        // replace this example code with whatever you need
        return new Response("<pre>".print_r(count($shopCategories), true)."</pre>");
    }
}
