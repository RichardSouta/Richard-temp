<?php
namespace App\Presenters;

use Nette;
use App\Model;


class HomepagePresenter extends BasePresenter
{
    protected $collectibles;
    public function renderDefault()
	{
        $this->collectibles = $this->em->getRepository('App\Model\Entity\Collectible')->findBy([],['dateTimeAdded' => 'DESC'],15,0);
        $this->template->collectibles=$this->collectibles;
	}

    public function handleLoad($page)
    {
        if ($this->isAjax()) {
            $this->collectibles = $this->em->getRepository('App\Model\Entity\Collectible')->findBy([],['dateTimeAdded' => 'DESC'],15,$page*15);
            if (count($this->collectibles) != 15) {
                $this->payload->stopLoading = true;
            }

            if (count($this->collectibles)!=0){
                $this->redrawControl('collectibles');
            }
            else {
                $this->sendPayload();
            }

        }
    }
}
