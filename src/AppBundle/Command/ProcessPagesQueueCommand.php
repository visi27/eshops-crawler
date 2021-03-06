<?php

namespace AppBundle\Command;

use AppBundle\Entity\PageQueue;
use AppBundle\Entity\Product;
use AppBundle\Entity\Shop;
use AppBundle\Service\WebCrawler;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ProcessPagesQueueCommand extends ContainerAwareCommand
{
    /**
     * @var ObjectManager
     */
    private $em;

    /**
     * @var OutputInterface
     */
    private $output;

    protected function configure()
    {
        $this->setName('app:process-pages-queue');
        $this->setDescription('Get first page from queue and extracts products from it.');

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->em = $this->getContainer()->get('doctrine')->getManager();

        // Save output interface reference to class property so it can be accessed by this classes methods
        $this->output = $output;

        while ($page = $this->getNextPage()){
            $output->writeln("Processing page: ".$page->getUrl());

            /** @var Shop $shop */
            $shop = $page->getShop();

            $shopCrawler = $this->getContainer()
                ->getParameter("crawler_config_keys")[$shop->getConfigKey()]["crawler"];

            /** @var WebCrawler $crawler */
            $crawler = $this->getContainer()->get($shopCrawler);
            $crawler->setShopConfig($shop->getConfigKey());
            $crawler->setUrl($page->getUrl());

            $products = $crawler->getProducts();
            $output->writeln("Found ".count($products)." products on page. Persisting to DB");
            $this->persistProducts($page, $products);
        }
    }

    private function getNextPage(){
        return $this->em->getRepository('AppBundle:PageQueue')->findOneBy(['processed' => 0]);
    }

    private function persistProducts(PageQueue $page, array $products){
        /** @var Shop $shop */
        $shop = $page->getShop();
        $category_id = $page->getShopCategory()->getCategory();

        foreach ($products as $product){
            $this->output->writeln('<info>Persisting: "'.$product["name"].'" to DB</info>');
            $productObject = new Product();

            $productObject->setCategory($category_id);
            $productObject->setShop($shop);

            $productObject->setName(trim($product["name"]));
            $productObject->setDescription(trim($product["description"]));
            $productObject->setPrice($product["price"]);
            $productObject->setSalePrice($product["salePrice"]);
            $productObject->setUrl($product["url"]);
            $productObject->setImageUrl($product["image"]);

            $fileName = basename($product["image"]);
            //Save image to disk if we have one
            if(!empty($fileName)){
                $imageFilePath = $this->getContainer()->getParameter('crawler_images_path')."/".$shop->getId()."/".$fileName;

                if($this->remoteFileExists($product["image"])){
                    $file = file_get_contents($product["image"]);
                    $insert = file_put_contents($imageFilePath, $file);
                    if (!$insert) {
                        $this->output->writeln('<error>Error writing image to: '.$imageFilePath.'</error>');
                        $productObject->setImageUrl("");
                    }
                }else{
                    $this->output->writeln('<error>Error getting image: '.$product["image"].'</error>');
                }
            }
            $productObject->setImageFileName($fileName);

            $this->em->persist($productObject);
        }

        $page->setProcessed(1);
        $this->em->persist($page);

        $this->em->flush();
    }

    private function remoteFileExists($url) {
        $curl = curl_init($url);

        //don't fetch the actual page, you only want to check the connection is ok
        curl_setopt($curl, CURLOPT_NOBODY, true);

        //do request
        $result = curl_exec($curl);

        $ret = false;

        //if request did not fail
        if ($result !== false) {
            //if request was ok, check response code
            $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

            if ($statusCode == 200) {
                $ret = true;
            }
        }

        curl_close($curl);

        return $ret;
    }
}