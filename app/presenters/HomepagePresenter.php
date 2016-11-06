<?php
namespace App\Presenters;

use Nette;
use App\Model;


class HomepagePresenter extends BasePresenter
{
    public function actionDefault()
	{
        $this->collectibles = $this->em->getRepository('App\Model\Entity\Collectible')->findBy([],['dateTimeAdded' => 'DESC'],15,0);
        $this->template->collectibles=$this->collectibles;
	}

}
