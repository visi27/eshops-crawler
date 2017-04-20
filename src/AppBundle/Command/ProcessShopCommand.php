<?php

namespace AppBundle\Command;

use AppBundle\Crawler\WebCrawler;
use AppBundle\Entity\PageQueue;
use AppBundle\Entity\ShopCategory;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ProcessShopCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('app:process-shop');
        $this->setDescription('Starts crawling a given shop by checking all categories to process and inserts resulting urls to page queue');

        $this->addArgument('shop', InputArgument::REQUIRED, "Shop ID");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $shopId = $input->getArgument('shop');
        $em = $this->getContainer()->get('doctrine')->getManager();
        //$shop = $em->getRepository('AppBundle:Shop')->findOneBy(['id' => $shopId]);

        $shopCategories = $em->getRepository('AppBundle:ShopCategory')->findBy(['shop' => $shopId, 'process' => 1]);

        /**
         * @var ShopCategory $shopCategory
         */
        foreach ($shopCategories as $shopCategory){
            $url = $shopCategory->getUrl();

            $crawler = new WebCrawler($url);
            $pages = $crawler->getPages();

            foreach ($pages as $page){
                $output->writeln("Adding url ".$page." to queue");
                $pageQueue = new PageQueue();
                $pageQueue->setProcessed(0);
                $pageQueue->setShopCategory($shopCategory);
                $pageQueue->setUrl($page);
                $pageQueue->setQueuedDate(new \DateTime("now"));

                $em->persist($pageQueue);
            }

            $shopCategory->setProcess(0);
            $em->persist($shopCategory);

            $em->flush();
        }
    }
}