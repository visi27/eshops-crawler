<?php

namespace AppBundle\Crawler;

use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;

class WebCrawler
{
    private $url;

    private $crawler;

    private $client;

    public function __construct($url)
    {
        $this->url = $url;

        $this->client = new Client();
        $this->crawler = $this->client->request('GET', $url);
    }

    public function getProducts(){
        $products = array();
        $pages = $this->getPages();
        foreach ($pages as $page){
            $this->crawler = $this->client->request('GET', $page);

            $this->crawler->filter('div.product')->each(function ($node) use (&$products) {
                /**
                 * @var Crawler $node
                 */
                $name = $node->filter('div.description > h4 > a')->first()->text();
                $price = $node->filter('div.price > span')->first()->text();
                $description = $node->filter('div.description > p')->first()->html();
                array_push($products, array("name"=>$name, "price"=> $price, "description" => $description));
            });
        }



        return $products;
    }

    public function getPages(){
        $linksCrawler = $this->crawler;
        $pages = array();
        array_push($pages, $this->url);
        while($linksCrawler->filter('ul.pagination > li > a')->last()->text()=="»"){
            $link = $linksCrawler->selectLink("»")->link();
            $url = $link->getUri();
            array_push($pages, $url);
            $linksCrawler = $this->client->click($link);
        }

        return $pages;
    }
}