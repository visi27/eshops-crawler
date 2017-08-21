<?php

namespace AppBundle\Service;

use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Class WebCrawler
 * Service to crawl configured eshops and extract product and categories.
 * Before getting products or caegories be sure to call setShopConfig on this service.
 *
 * @package AppBundle\Service
 */
class WebCrawler
{
    /**
     * @var string
     */
    private $url;

    /**
     * @var Crawler
     */
    private $crawler;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var array
     */
    private $shopConfig;

    /**
     * @var array
     */
    private $parameters;

    /**
     * WebCrawler constructor.
     * Inject crawler config keys array from parameters in crawler.yml.
     * @param $parameters
     */
    public function __construct(array $parameters)
    {

        $this->parameters = $parameters;
    }

    /**
     * Set initial crawl url and crawl it.
     *
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;

        $this->client = new Client();
        $this->crawler = $this->client->request('GET', $url);
    }

    /**
     * Get list of products from crawled page
     *
     * @return array Array with products
     */
    public function getProducts()
    {
        if (empty($this->shopConfig)) {
            return array();
        }

        $name_selector = $this->shopConfig["product"]["name"];
        $price_selector = $this->shopConfig["product"]["price"];
        $desc_selector = $this->shopConfig["product"]["description"];
        $link_selector = $this->shopConfig["product"]["link"];
        $image_selector = $this->shopConfig["product"]["image"];

        $products = array();

        $this->crawler->filter($this->shopConfig["product"]["css_filter"])->each(
            function ($node) use (&$products, $name_selector, $price_selector, $desc_selector, $link_selector, $image_selector) {
                /**
                 * @var Crawler $node
                 */
                $name = $node->filter($name_selector)->first()->text();

                $check_price = $node->filter($price_selector)->count();
                if($check_price > 0){
                    $price = $node->filter($price_selector)->first()->text();
                }else{
                    $price = 0; //Check for sale price
                }

                $price = preg_replace('/[^0-9.]+/', '', $price);
                $price = str_replace('.', ',', $price);

                $description = $node->filter($desc_selector)->first()->html();

                $link = $node->filter($link_selector)->links()[0]->getUri();

                $image_url = $node ->filter($image_selector)->extract(array('src'));
                if(count($image_url)>0){
                    $image_url = $image_url[0];
                }else{
                    $image_url = "";
                }

                array_push(
                    $products,
                    array("name" => $name, "url" => $link, "price" => $price, "description" => $description, "image" => $image_url)
                );
            }
        );

        return $products;
    }

    /**
     * Get list of pages from given URL. Tries to get all pages by following paginator links
     *
     * @return array Array of page links
     */
    public function getPages()
    {
        if (empty($this->shopConfig)) {
            return array();
        }

        if($this->shopConfig["pages"]["next_page_finder"] == "text"){
            $selector = $this->shopConfig["pages"]["selector"];
            $text = $this->shopConfig["pages"]["text"];

            return $this->getPagesByNextPageLinkText($selector, $text);
        }

        return array();
    }

    private function getPagesByNextPageLinkText($selector, $text){
        $linksCrawler = $this->crawler;
        $pages = array();
        array_push($pages, $this->url);
        while (strtolower(trim($linksCrawler->filter($selector)->last()->text())) == strtolower($text)) {
            $link = $linksCrawler->selectLink($text)->link();
            $url = $link->getUri();
            array_push($pages, $url);
            $linksCrawler = $this->client->click($link);
        }

        return $pages;
    }

    // In this situation we find the current link and see if there is a page link after that.
//    private function getPagesByNextLink($currentPage){
//        $linksCrawler = $this->crawler;
//        $pages = array();
//        array_push($pages, $this->url);
//
//        while($linksCrawler->filter($currentPage)->nextAll()->text()){
//
//        }
//    }

    /**
     * @return array Shop configuration holding css selectors for a given shop.
     */
    public function getShopConfig()
    {
        return $this->shopConfig;
    }

    /**
     * @param string $shopConfig Configuration key from db. This is used to get css selectors from crawler.yml
     */
    public function setShopConfig($shopConfig)
    {
        if (array_key_exists($shopConfig, $this->parameters))
        {
            $this->shopConfig = $this->parameters[$shopConfig];
        } else {
            $this->shopConfig = array();
        }
    }
}