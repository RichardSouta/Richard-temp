<?php

namespace App\Model\Entity;

use App\Model\Repository\CollectibleRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass="CollectibleRepository")
 */
class Collectible
{
    public function __construct()
    {
        $this->images = new ArrayCollection;
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
}