<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="pages_queue")
 */
class PageQueue
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Shop")
     */
    private $shop;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\ShopCategory")
     */
    private $shopCategory;

    /**
     * @ORM\Column(type="string")
     */
    private $url;

    /**
     * @ORM\Column(type="boolean")
     */
    private $processed;

    /**
     * @ORM\Column(type="datetime")
     */
    private $queuedDate;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $processedDate;

    /**
     * @return mixed
     */
    public function getShop()
    {
        return $this->shop;
    }

    /**
     * @param mixed $shop
     */
    public function setShop($shop)
    {
        $this->shop = $shop;
    }


    /**
     * @return ShopCategory
     */
    public function getShopCategory()
    {
        return $this->shopCategory;
    }

    /**
     * @param ShopCategory $shopCategory
     */
    public function setShopCategory(ShopCategory $shopCategory)
    {
        $this->shopCategory = $shopCategory;
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param mixed $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @return mixed
     */
    public function getProcessed()
    {
        return $this->processed;
    }

    /**
     * @param mixed $processed
     */
    public function setProcessed($processed)
    {
        $this->processed = $processed;
    }

    /**
     * @return mixed
     */
    public function getQueuedDate()
    {
        return $this->queuedDate;
    }

    /**
     * @param mixed $queuedDate
     */
    public function setQueuedDate($queuedDate)
    {
        $this->queuedDate = $queuedDate;
    }

    /**
     * @return mixed
     */
    public function getProcessedDate()
    {
        return $this->processedDate;
    }

    /**
     * @param mixed $processedDate
     */
    public function setProcessedDate($processedDate)
    {
        $this->processedDate = $processedDate;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }
}