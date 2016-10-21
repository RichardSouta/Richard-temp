<?php

namespace App\Model\Repository;

use App\Model\Entity\User;
use Kdyby\Doctrine\EntityRepository;

class ChatRepository extends EntityRepository
{
    public function findByUsers($users)
    {
        
    }

}