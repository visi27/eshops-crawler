<?php

namespace AppBundle\Service;

use AppBundle\Entity\PageQueue;
use AppBundle\Entity\ShopCategory;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Class DoctrinePageQueue
 * @package AppBundle\Service
 */
class DoctrinePageQueue
{
    use ContainerAwareTrait;

    /**
     * DoctrinePageQueue constructor.
     * Inject container so we have access to symfony services.
     *
     * @param $container
     */
    public function __construct($container)
    {
        $this->setContainer($container);
    }

    /**
     * Ads a page to database queue
     *
     * @param ShopCategory $shopCategory
     * @param $pageUrl
     */
    public function addPageToQueue(ShopCategory $shopCategory, $pageUrl)
    {
        $pageQueue = new PageQueue();
        $pageQueue->setProcessed(0);
        $pageQueue->setShopCategory($shopCategory);
        $pageQueue->setUrl($pageUrl);
        $pageQueue->setQueuedDate(new \DateTime("now"));

        $em = $this->container->get('doctrine')->getManager();
        $em->persist($pageQueue);
        $em->flush();
    }
}