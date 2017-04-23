<?php

namespace AppBundle\Command;

use AppBundle\Crawler\WebCrawler;
use AppBundle\Entity\PageQueue;
use AppBundle\Entity\ShopCategory;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ProcessShopCategoryCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('app:process-category');
        $this->setDescription('Starts crawling a given shop category and inserts resulting urls to page queue');

        $this->addArgument('shop-category', InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $shopCategoryId = $input->getArgument('shop-category');
        $em = $this->getContainer()->get('doctrine')->getManager();

        /**
         * @var ShopCategory $shopCategory
         */
        $shopCategory = $em->getRepository('AppBundle:ShopCategory')->findOneBy(['id' => $shopCategoryId]);

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

        $em->flush();
    }
}