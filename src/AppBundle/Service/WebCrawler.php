<?php

namespace AppBundle\Service;

use Goutte\Client;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
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
    use ContainerAwareTrait;

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
     * WebCrawler constructor.
     * Inject container so we have access to symfony services.
     * @param $container
     */
    public function __construct($container)
    {
        $this->setContainer($container);
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
        $price_selector = $this->shopConfig["product"]["name"];
        $desc_selector = $this->shopConfig["product"]["name"];
        $link_selector = $this->shopConfig["product"]["name"];

        $products = array();

        $this->crawler->filter('div.product')->each(
            function ($node) use (&$products, $name_selector, $price_selector, $desc_selector, $link_selector) {
                /**
                 * @var Crawler $node
                 */
                $name = $node->filter($name_selector)->first()->text();
                $price = $node->filter($price_selector)->first()->text();
                $description = $node->filter($desc_selector)->first()->html();

                $link = $node->filter($link_selector)->links()[0]->getUri();

                array_push(
                    $products,
                    array("name" => $name, "url" => $link, "price" => $price, "description" => $description)
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

        $selector = $this->shopConfig["pages"]["selector"];
        $text = $this->shopConfig["pages"]["text"];

        $linksCrawler = $this->crawler;
        $pages = array();
        array_push($pages, $this->url);
        while ($linksCrawler->filter($selector)->last()->text() == $text) {
            $link = $linksCrawler->selectLink($text)->link();
            $url = $link->getUri();
            array_push($pages, $url);
            $linksCrawler = $this->client->click($link);
        }

        return $pages;
    }

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
        if ($this->container->hasParameter($shopConfig)) {
            $this->shopConfig = $this->container->getParameter($shopConfig);
        } else {
            $this->shopConfig = array();
        }
    }
}