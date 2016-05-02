<?php

namespace App\Presenters;

use Nette;
use App\Model;


class CategoryPresenter extends BasePresenter
{
  public $page;
	public function renderDefault($page=1)
	{
		  $collectibles=$this->database->table('collectibles')->select('*')->limit(27)->fetchAll();      
      $paginator = new Nette\Utils\Paginator;
      $paginator->setItemCount(count($collectibles)); // celkový počet položek (např. článků)
      $paginator->setItemsPerPage(9); // počet položek na stránce
      $paginator->setPage($page); // číslo aktuální stránky, číslováno od 1
      $this->template->paginator=$paginator;
      $this->template->collectibles=array_slice($collectibles, $paginator->getOffset(),$paginator->getLength());
	}

}
