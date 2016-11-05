<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;
/**
 * @ORM\Entity(repositoryClass="App\Model\Repository\TradeRepository")
 */
class Trade
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Collectible")
     * @ORM\JoinColumn(nullable=false)
     * @var Collectible
     */
    private $collectibleOffered;

    /**
     * @ORM\ManyToOne(targetEntity="Collectible")
     * @ORM\JoinColumn(nullable=false)
     * @var Collectible
     */
    private $collectibleAsked;

    /**
     * @ORM\Column(type="string")
     */
    protected $status;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(nullable=false)
     * @var User
     */
    private $receiver;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(nullable=false)
     * @var User
     */
    private $sender;

    /**
     * @return User
     */
    public function getReceiver()
    {
        return $this->receiver;
    }

    /**
     * @param User $receiver
     * @return Trade
     */
    public function setReceiver($receiver)
    {
        $this->receiver = $receiver;
        return $this;
    }

    /**
     * @return User
     */
    public function getSender()
    {
        return $this->sender;
    }

    /**
     * @param User $sender
     * @return Trade
     */
    public function setSender($sender)
    {
        $this->sender = $sender;
        return $this;
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
     * @return Trade
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return Collectible
     */
    public function getCollectibleOffered()
    {
        return $this->collectibleOffered;
    }

    /**
     * @param Collectible $collectibleOffered
     * @return Trade
     */
    public function setCollectibleOffered($collectibleOffered)
    {
        $this->collectibleOffered = $collectibleOffered;
        return $this;
    }

    /**
     * @return Collectible
     */
    public function getCollectibleAsked()
    {
        return $this->collectibleAsked;
    }

    /**
     * @param Collectible $collectibleAsked
     * @return Trade
     */
    public function setCollectibleAsked($collectibleAsked)
    {
        $this->collectibleAsked = $collectibleAsked;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     * @return Trade
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }


}