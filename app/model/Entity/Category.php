<?php

namespace App\Model\Entity;

use App\Model\Repository\CategoryRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;


/**
 * @ORM\Entity(repositoryClass="App\Model\Repository\CategoryRepository")
 */
class Category
{
    public function __construct()
    {
        $this->collectibles = new ArrayCollection;
        $this->topics = new ArrayCollection;
    }

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ORM\Column(type="string", unique=true)
     */
    protected $name;

    /**
     * @ORM\Column(type="string")
     */
    protected $description;

    /**
     * @ORM\OneToMany(targetEntity="Collectible", mappedBy="category", cascade={"persist", "remove"})
     * @var Collectible[]
     */
    private $collectibles;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $icon;

    /**
     * @ORM\ManyToMany(targetEntity="Topic", mappedBy="categories", cascade={"persist", "remove"})
     * @var Topic[]
     */
    private $topics;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return Category
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     * @return Category
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     * @return Category
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return Collectible[]
     */
    public function getCollectibles()
    {
        return $this->collectibles;
    }

    /**
     * @param Collectible[] $collectibles
     * @return Category
     */
    public function setCollectibles($collectibles)
    {
        $this->collectibles = $collectibles;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * @param mixed $icon
     * @return Category
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;
        return $this;
    }

    /**
     * @return Topic[]
     */
    public function getTopics()
    {
        return $this->topics;
    }

    /**
     * @param Topic[] $topics
     * @return Category
     */
    public function setTopics($topics)
    {
        $this->topics = $topics;
        return $this;
    }

    public function __toString()
    {
        return $this->name;
    }

}