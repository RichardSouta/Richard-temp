<?php

namespace App\Presenters;

use Nette;
use App\Model;


class CategoryPresenter extends BasePresenter
{
  public $category, $page;
	public function renderDefault($category=NULL,$page=1)
	{
		  $collectibles=$this->database->query('SELECT collectible_id,picture,co.name,origin,co.description,ca.name as category FROM collectibles co LEFT OUTER JOIN categories ca on co.category_id=ca.category_id where ca.name=? order by collectible_id desc limit 27',$category)->fetchAll();     
      $paginator = new Nette\Utils\Paginator;
      $paginator->setItemCount(count($collectibles)); // celkový počet položek (např. článků)
      $paginator->setItemsPerPage(9); // počet položek na stránce
      $paginator->setPage($page); // číslo aktuální stránky, číslováno od 1
      $this->template->paginator=$paginator;
      $this->template->collectibles=array_slice($collectibles, $paginator->getOffset(),$paginator->getLength());
	}

}
