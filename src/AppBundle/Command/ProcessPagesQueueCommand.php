<?php

namespace AppBundle\Command;

use AppBundle\Crawler\WebCrawler;
use AppBundle\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ProcessPagesQueueCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('app:process-pages-queue');
        $this->setDescription('Get first page from queue and extracts prodicts from it.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();

        $page = $em->getRepository('AppBundle:PageQueue')->findOneBy(['processed' => 0]);

        $shop_id = $page->getShop();
        $category_id = $page->getShopCategory()->getCategory();

        $crawler = new WebCrawler($page->getUrl());
        $products = $crawler->getProducts();

        foreach ($products as $product){
            $productObject = new Product();

            $productObject->setCategory($category_id);
            $productObject->setShop($shop_id);

            $productObject->setName(trim($product["name"]));
            $productObject->setDescription(trim($product["description"]));
            $productObject->setPrice(floatval($product["price"]));
            $productObject->setUrl($product["url"]);

            $em->persist($productObject);
        }

        $page->setProcessed(1);
        $em->persist($page);

        $em->flush();
    }
}