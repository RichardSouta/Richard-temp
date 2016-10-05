<?php

namespace App\Model\Entity;

use App\Model\Repository\CategoryRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;


/**
 * @ORM\Entity(repositoryClass="CategoryRepository")
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
     * @ORM\Column(type="string")
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
     * @ORM\Column(type="string")
     */
    protected $icon;

    /**
     * @ORM\ManyToMany(targetEntity="Topic", mappedBy="categories", cascade={"persist", "remove"})
     * @var Topic[]
     */
    private $topics;


}