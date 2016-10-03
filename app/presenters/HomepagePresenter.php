<?php
namespace App\Presenters;

use Nette;
use App\Model;


class HomepagePresenter extends BasePresenter
{
    /** @persistent */
    public $page;

    private $collectibles;

	public function renderDefault()
	{
        $this->collectibles=$this->database->query('SELECT collectible_id,picture,co.name,origin,co.description,ca.name as category FROM collectibles co LEFT OUTER JOIN categories ca on co.category_id=ca.category_id order by collectible_id desc limit 10 offset ?',$this->page*10)->fetchAll();
        $this->template->collectibles=$this->collectibles;
	}

	public function handleLoad($page)
    {
        if($this->isAjax())
        {
            $this->collectibles=$this->database->query('SELECT collectible_id,picture,co.name,origin,co.description,ca.name as category FROM collectibles co LEFT OUTER JOIN categories ca on co.category_id=ca.category_id order by collectible_id desc limit 10 offset ?',$page*10)->fetchAll();
            $this->redrawControl('collectibles');

        }
    }

}
