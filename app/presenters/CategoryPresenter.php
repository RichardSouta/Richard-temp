<?php

namespace App\Presenters;

use Nette;
use App\Model;
use Nette\Application\UI\Form as Form;

class CategoryPresenter extends BasePresenter
{
  public $category, $page, $collectibles;
	public function renderDefault($category=NULL)
	{
      if(!$category)$this->redirect('Homepage:');
      else  $this->collectibles=$this->template->collectibles=$this->em->getRepository('App\Model\Entity\Collectible')->findByCategory($category);

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
	$category = new Model\Entity\Category();
        $category->setDescription($values->description)->setName($values->name);
        $this->em->persist($category);
        $this->em->flush();
    
    $this->flashMessage('Kategorie byla vytvořena.');
		$this->redirect('Homepage:');

	}
}
