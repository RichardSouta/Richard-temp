<?php

namespace App\Presenters;

use Nette;
use App\Model;
use Nette\Application\UI\Form as Form;

class CategoryPresenter extends BasePresenter
{
  public $category, $page;
	public function renderDefault($category=NULL,$page=1)
	{
		  $collectibles=$this->database->query('SELECT collectible_id,picture,co.name,origin,co.description,ca.name as category FROM collectibles co LEFT OUTER JOIN categories ca on co.category_id=ca.category_id where ca.name=? order by collectible_id desc',$category)->fetchAll();     
      $paginator = new Nette\Utils\Paginator;
      $paginator->setItemCount(count($collectibles)); // celkový počet položek (např. článků)
      $paginator->setItemsPerPage(10); // počet položek na stránce
      $paginator->setPage($page); // číslo aktuální stránky, číslováno od 1
      $this->template->paginator=$paginator;
      $this->template->collectibles=array_slice($collectibles, $paginator->getOffset(),$paginator->getLength());
	}
     protected function createComponentCategoryForm()
	{
    $form = new Form;
    $form->addProtection();
    $form->addText('name', 'Název kategorie')->setRequired()->setAttribute('class','form-control');  

    $form->addTextArea('description', 'Popis kategorie')->setRequired()->setAttribute('class','form-control');

    $form->addSubmit('send', 'nová kategorie')->setRequired()->setAttribute('class','form-control')->setAttribute('id','submit_button');

  	$form->onSuccess[] = $this->categoryFormSubmitted;
    
		return $form;
	}

   public function categoryFormSubmitted($form,$values)
	{   
		$this->database->table('categories')->insert(array(

        'name' => $values->name,
        'description' => $values->description,
    ));
    
    $this->flashMessage('Kategorie byla vytvořena.');
		$this->redirect('Homepage:');

	}
}
