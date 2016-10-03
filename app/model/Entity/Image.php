<?php

namespace App\Model\Entity;

use App\Model\Repository\ImageRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="ImageRepository")
 */
class Image
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     */
    protected $path;

    /**
     * @ORM\ManyToOne(targetEntity="Collectible", inversedBy="images")
     * @ORM\JoinColumn(nullable=false)
     * @var Collectible
     */
    private $collectible;
}