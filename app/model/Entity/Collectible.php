<?php

namespace App\Model\Entity;

use App\Model\Repository\CollectibleRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass="App\Model\Repository\CollectibleRepository")
 */
class Collectible
{
    public function __construct()
    {
        $this->images = new ArrayCollection;
        $this->dateTimeAdded = new \DateTime();
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
     * @return Category
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param Category $user
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
        for ($i=0;$i<$count;$i++)
        {
            $this->setImage();
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
    public function setImage()
    {
        $image = new Image();
        $image->setCollectible($this);
        $this->images [] = $image;
        return $this;
    }

}