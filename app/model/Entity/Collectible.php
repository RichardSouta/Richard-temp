<?php

namespace App\Model\Entity;

use App\Model\Repository\CollectibleRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Nette\Utils\Finder;

/**
 * @ORM\Entity(repositoryClass="App\Model\Repository\CollectibleRepository")
 */
class Collectible
{
    public function __construct()
    {
        $this->images = new ArrayCollection;
        $this->dateTimeAdded = new \DateTime();
        $this->tradeable = 0;
    }

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     */
    private $name;

    /**
     * @ORM\Column(type="string")
     */
    protected $origin;

    /**
     * @ORM\Column(type="string")
     */
    protected $description;

    /**
     * @ORM\Column(type="boolean", options={"default" : 0})
     */
    protected $tradeable;

    /**
     * @return mixed
     */
    public function getTradeable()
    {
        return $this->tradeable;
    }

    /**
     * @param mixed $tradeable
     */
    public function setTradeable($tradeable)
    {
        $this->tradeable = $tradeable;
        return $this;
    }

    /**
     * @ORM\OneToMany(targetEntity="Image", mappedBy="collectible", cascade={"persist", "remove"})
     * @var Image[]
     */
    private $images;

    /**
     * @ORM\ManyToOne(targetEntity="Category", inversedBy="collectibles")
     * @ORM\JoinColumn(nullable=false)
     * @var Category
     */
    private $category;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="collectibles")
     * @ORM\JoinColumn(nullable=false)
     * @var User
     */
    private $user;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $dateTimeAdded;

    /**
     * @return mixed
     */
    public function getDateTimeAdded()
    {
        return $this->dateTimeAdded;
    }

    /**
     * @param mixed $dateTimeAdded
     */
    public function setDateTimeAdded($dateTimeAdded)
    {
        $this->dateTimeAdded = $dateTimeAdded;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return Collectible
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
     * @return Collectible
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getOrigin()
    {
        return $this->origin;
    }

    /**
     * @param mixed $origin
     * @return Collectible
     */
    public function setOrigin($origin)
    {
        $this->origin = $origin;
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
     * @return Collectible
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return Image[]
     */
    public function getImages()
    {
        return $this->images;
    }

    /**
     * @param $count
     * @return Collectible
     */
    public function setImages($count)
    {
        for ($i = 0; $i < $count; $i++) {
            $image = new Image();
            $image->setCollectible($this);
            $this->setImage($image);
        }
        return $this;
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
     * @return Collectible
     */
    public function setCategory($category)
    {
        $this->category = $category;
        return $this;
    }

    /**
     * @param Image $image
     * @return Collectible
     */
    public function setImage($image)
    {
        $this->images [] = $image;
        return $this;
    }

    public function emptyImages()
    {
        foreach ($this->getImages() as $image) {
            $name = $image;
            unlink("../www/images/collectible/$name.jpg");
            $name = $name . '\-';
            foreach (Finder::findFiles($name . '.*')->in('../www/images/collectible') as $key => $file) {
                unlink($key);
            }
        }
        $this->images->clear();
        return $this;
    }

}