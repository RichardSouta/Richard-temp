<?php
namespace App\Presenters;

use Nette;
use App\Model;


class HomepagePresenter extends BasePresenter
{
  public $page;
	public function renderDefault($page=1)
	{
      $collectibles=$this->database->query('SELECT collectible_id,picture,co.name,origin,co.description,ca.name as category FROM collectibles co LEFT OUTER JOIN categories ca on co.category_id=ca.category_id order by collectible_id desc')->fetchAll();     
      $paginator = new Nette\Utils\Paginator;
      $paginator->setItemCount(count($collectibles)); // celkový počet položek (např. článků)
      $paginator->setItemsPerPage(10); // počet položek na stránce
      $paginator->setPage($page); // číslo aktuální stránky, číslováno od 1
      $this->template->paginator=$paginator;
      $this->template->collectibles=array_slice($collectibles, $paginator->getOffset(),$paginator->getLength());
      

	}

}
