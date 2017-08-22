<?php
/**
 * Created by PhpStorm.
 * User: evis
 * Date: 8/22/17
 * Time: 9:27 AM
 */

namespace AppBundle\Service\Crawler;


use AppBundle\Service\WebCrawler;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Custom crawler service for neptun.al website
 * @package AppBundle\Service\Crawler
 */
class Neptun extends WebCrawler
{
    public function getProducts()
    {
        if (empty($this->shopConfig)) {
            return array();
        }

        $name_selector = $this->shopConfig["product"]["name"];
        $price_selector = $this->shopConfig["product"]["price"];
        $sale_price_selector = $this->shopConfig["product"]["sale_price"];
        $old_price_selector = $this->shopConfig["product"]["old_price"];
        $desc_selector = $this->shopConfig["product"]["description"];
        $link_selector = $this->shopConfig["product"]["link"];
        $image_selector = $this->shopConfig["product"]["image"];

        $products = array();

        $this->crawler->filter($this->shopConfig["product"]["css_filter"])->each(
            function ($node) use (&$products, $name_selector, $price_selector, $old_price_selector, $sale_price_selector, $desc_selector, $link_selector, $image_selector) {
                /**
                 * @var Crawler $node
                 */
                $name = $node->filter($name_selector)->first()->text();

                $check_price = $node->filter($price_selector)->count();
                if($check_price > 0){
                    $price = $node->filter($price_selector)->first()->text();
                    $sale_price = 0;
                }else{
                    //try to get old and sale price
                    $check_old_price = $node->filter($old_price_selector)->count();
                    $check_sale_price = $node->filter($sale_price_selector)->count();

                    if($check_old_price > 0){
                        $price = $node->filter($old_price_selector)->first()->text();
                    }else{
                        $price = 0; //Check for sale price
                    }

                    if($check_sale_price > 0){
                        $sale_price = $node->filter($sale_price_selector)->first()->text();
                    }else{
                        $sale_price = 0;
                    }
                }

                // Cleanup price and sale price
                $price = preg_replace('/[^0-9]+/', '', $price);
                $sale_price = preg_replace('/[^0-9]+/', '', $sale_price);


                if($node->filter($desc_selector)->count() > 0){
                    $description = $node->filter($desc_selector)->first()->html();
                }else{
                    $description = "";
                }

                $link = $node->filter($link_selector)->links()[0]->getUri();

                $image_url = $node ->filter($image_selector)->extract(array('data-original'));
                if(count($image_url)>0){
                    $image_url = $image_url[0];
                }else{
                    $image_url = "";
                }

                array_push(
                    $products,
                    array("name" => $name, "url" => $link, "price" => $price, "salePrice" =>$sale_price, "description" => $description, "image" => $image_url)
                );
            }
        );

        return $products;
    }
}