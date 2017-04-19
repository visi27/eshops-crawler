<?php

namespace AppBundle\Command;


use AppBundle\Crawler\WebCrawler;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CrawlCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('app:crawl');
        $this->setDescription('Crawl Command');

        $this->addArgument('url', InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $url = $input->getArgument('url');

        $crawler = new WebCrawler($url);
        $products = $crawler->getProducts();

        foreach ($products as $product){
            $output->writeln($product["name"]." -- ".$product["price"]);
            $output->writeln($product["description"]);
            $output->writeln("");
        }

    }
}