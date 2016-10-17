<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass="App\Model\Repository\UserRepository")
 */
class User
{
    public function __construct()
    {
        $this->collectibles = new ArrayCollection;
        $this->regDateTime = new \DateTime();
    }

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ORM\OneToMany(targetEntity="Collectible", mappedBy="user", cascade={"persist", "remove"})
     * @var Collectible[]
     */
    private $collectibles;

    /**
     * @ORM\Column(type="string")
     */
    protected $name;

    /**
     * @ORM\Column(type="string")
     */
    protected $surname;

    /**
     * @ORM\Column(type="string")
     */
    protected $username;

    /**
     * @ORM\Column(type="string")
     */
    protected $password;

    /**
     * @ORM\Column(type="string")
     */
    protected $email;

    /**
     * @ORM\Column(type="string")
     */
    protected $confirmedEmail;

    /**
     * @ORM\Column(type="string")
     */
    protected $security;

    /**
     * @ORM\Column(type="string")
     */
    protected $notification;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $regDateTime;

    /**
     * @ORM\Column(type="string")
     */
    protected $description;

    /**
     * @ORM\Column(type="string")
     */
    protected $phone;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return User
     */
    public function setId($id)
    {
        $this->id = $id;
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
     * @return User
     */
    public function setCollectibles($collectibles)
    {
        $this->collectibles = $collectibles;
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
     * @return User
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSurname()
    {
        return $this->surname;
    }

    /**
     * @param mixed $surname
     * @return User
     */
    public function setSurname($surname)
    {
        $this->surname = $surname;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param mixed $username
     * @return User
     */
    public function setUsername($username)
    {
        $this->username = $username;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param mixed $password
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getConfirmedEmail()
    {
        return $this->confirmedEmail;
    }

    /**
     * @param mixed $confirmedEmail
     * @return User
     */
    public function setConfirmedEmail($confirmedEmail)
    {
        $this->confirmedEmail = $confirmedEmail;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSecurity()
    {
        return $this->security;
    }

    /**
     * @param mixed $security
     * @return User
     */
    public function setSecurity($security)
    {
        $this->security = $security;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getNotification()
    {
        return $this->notification;
    }

    /**
     * @param mixed $notification
     * @return User
     */
    public function setNotification($notification)
    {
        $this->notification = $notification;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRegDateTime()
    {
        return $this->regDateTime;
    }

    /**
     * @param mixed $regDateTime
     * @return User
     */
    public function setRegDateTime($regDateTime)
    {
        $this->regDateTime = $regDateTime;
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
     * @return User
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param mixed $phone
     * @return User
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
        return $this;
    }

    public function __toString()
    {
        return $this->username;
    }

    public function toIdentity()
    {
        $identity = [];
        $identity['confirmedEmail'] = $this->confirmedEmail;
        $identity['username'] = $this->username;
        return $identity;
    }


}