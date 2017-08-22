<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="shop_categories")
 */
class ShopCategory
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Shop")
     */
    private $shop;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Category")
     * @var Category
     */
    private $category;

    /**
     * @ORM\Column(type="string")
     */
    private $url;

    /**
     * @ORM\Column(type="boolean", options={"default":0})
     */
    private $process;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getShop()
    {
        return $this->shop;
    }

    /**
     * @param Shop $shop
     */
    public function setShop(Shop $shop)
    {
        $this->shop = $shop;
    }

    /**
     * @return Category
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param Category $category
     */
    public function setCategory(Category $category)
    {
        $this->category = $category;
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
    public function getProcess()
    {
        return $this->process;
    }

    /**
     * @param mixed $process
     */
    public function setProcess($process)
    {
        $this->process = $process;
    }

    public function __toString()
    {
        return $this->category->__toString();
    }
}