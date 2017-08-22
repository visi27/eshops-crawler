<?php

namespace AppBundle\Command;

use AppBundle\Entity\Product;
use AppBundle\Entity\Shop;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ProcessPagesQueueCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('app:process-pages-queue');
        $this->setDescription('Get first page from queue and extracts products from it.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /**
         * @var ObjectManager $em
         */
        $em = $this->getContainer()->get('doctrine')->getManager();

        $page = $em->getRepository('AppBundle:PageQueue')->findOneBy(['processed' => 0]);

        while ($page){
            /**
             * @var Shop $shop
             */
            $shop = $page->getShop();
            $category_id = $page->getShopCategory()->getCategory();

            $shopCrawler = $this->getContainer()
                ->getParameter("crawler_config_keys")[$shop->getConfigKey()]["crawler"];

            $crawler = $this->getContainer()->get($shopCrawler);
            $crawler->setShopConfig($shop->getConfigKey());
            $crawler->setUrl($page->getUrl());

            $products = $crawler->getProducts();

            foreach ($products as $product){
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
                            $output->writeln('<error>Error writing image to: '.$imageFilePath.'</error>');
                            $productObject->setImageUrl("");
                        }
                    }else{
                        $output->writeln('<error>Error getting image: '.$product["image"].'</error>');
                    }
                }
                $productObject->setImageFileName($fileName);

                $em->persist($productObject);
            }

            $page->setProcessed(1);
            $em->persist($page);

            $em->flush();

            $page = $em->getRepository('AppBundle:PageQueue')->findOneBy(['processed' => 0]);
        }

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