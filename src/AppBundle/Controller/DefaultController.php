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

    /**
     * @Route("/publish", name="publish-aws-message")
     */
    public function publishAction(){
        $message = ['test' => 'Keep Messaging'];

//        // Optional config to override default options
//        $options = [
//            'push_notifications' => 0,
//            'message_delay'      => 0
//        ];

        //$this->get('uecode_qpush.eshops_pages')->publish($message, $options);
        $message = ['test' => 'First IN'];
        $this->get('uecode_qpush.eshops_pages')->publish($message);
        $message = ['test' => 'Second IN'];
        $this->get('uecode_qpush.eshops_pages')->publish($message);
        $message = ['test' => 'Third IN'];
        $this->get('uecode_qpush.eshops_pages')->publish($message);

        return new Response("<html><body>"."Messages Sent"."</body></html>");
    }

    /**
     * @Route("/recive", name="recive-aws-message")
     */
    public function reciveAction(){

        $options = [
            'messages_to_receive' => 3
        ];

        $messages = $this->get('uecode_qpush.eshops_pages')->receive($options);

        $response = "";
        foreach ($messages as $message) {
            $response .= $message->getBody()["test"]."<br/>";
        }
        return new Response("<html><body>".$response."</body></html>");
    }
}
