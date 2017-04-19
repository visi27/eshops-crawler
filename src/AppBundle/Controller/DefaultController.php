<?php

namespace AppBundle\Controller;

use AppBundle\Crawler\WebCrawler;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {

        $url = "https://dyqani.shpresa.al/celulare-47-c";

        $crawler = new WebCrawler($url);
        $products = $crawler->getProducts();
        $pages = $crawler->getPages();

        // replace this example code with whatever you need
        return new Response("<pre>".print_r($pages, true)."</pre>");
    }
}
