<?php

namespace App\Presenters;
use Nette\Application\UI\Form as Form;
use Nette;
use App\Model;


class CollectiblePresenter extends BasePresenter
{
  public $id;
	public function renderDefault($id)
	{
	    $this->template->collectible=$this->database->table('collectibles')->get($id);      
     
	}
  
  public function renderEdit($id)
	{
	    //$this->template->collectible=$this->database->table('collectibles')->get($id);      
     
	}
  
  protected function createComponentCollectibleForm()
	{
    $form = new Form;
    $form->addProtection();
    $form->addText('name', 'Název předmětu')->setRequired();  

    $form->addTextArea('description', 'Popis předmětu')->setRequired();

    $form->addTextArea('origin', 'Původ předmětu')->setRequired();

    $categories=$this->database->table('categories')->select('*')->fetchPairs('category_id','name');
    $form->addSelect('category','Kategorie',$categories);


    $form->addUpload('img', 'Foto předmětu')->addRule(Form::IMAGE, 'Cover must be JPEG, PNG or GIF.');





    $form->addSubmit('send', 'nový předmět')->setRequired();

  	$form->onSuccess[] = $this->collectibleFormSubmitted;
		return $form;
	}

   public function collectibleFormSubmitted($form,$values)
	{
    if ($values['img']->isOk()) {
    $id = $this->database->query("SHOW TABLE STATUS LIKE 'collectibles' ")->fetch()->Auto_increment; 
    $filename = $this->getUser()->identity->data['username'].$id;
    $targetPath = $this->presenter->basePath;

    // @TODO vyřešit kolize
    $pripona = pathinfo($values['img']->getSanitizedName(), PATHINFO_EXTENSION);
    $cil=WWW_DIR."/images/location/$filename.$pripona";
    $cil2=$targetPath."images/location/$filename.$pripona";
    $values['img']->move($cil);
    }
    
		$this->database->table('collectibles')->insert(array(

        'name' => $values->name,
        'description' => $values->description,
        'user_id' =>  $this->getUser()->identity->id,
        'origin' =>   $values->origin,
        'picture' =>  $cil2,
    ));
    if(!empty($values->category)){	$this->database->table('collectibles')->get($id)->update(array('category_id' =>   $values->category));} 
    
    $this->flashMessage('Předmět byl vytvořen.');
		$this->redirect('Homepage:');

	}
  
  
  protected function createComponentCollectibleEditForm()
	{
    $form = new Form;
    $form->addProtection();
    $data=$this->database->table('collectibles')->get($this->getParameter('id'));
    $form->addText('name', 'název')->setValue($data->name)->setRequired();

    $form->addTextArea('description', 'popis')->setValue($data->description)->setRequired();

    $form->addTextArea('origin', 'původ')->setValue($data->origin)->setRequired();

    $categories=$this->database->table('categories')->select('*')->fetchPairs('category_id','name');
    $form->addSelect('category','kategorie',$categories)->setValue($data->category_id);

    $form->addUpload('img', 'Upload your image')->setRequired(false)->addCondition(Form::FILLED)->addRule(Form::IMAGE, 'Cover must be JPEG, PNG or GIF.');

    $form->addSubmit('send', 'Upravit předmět')->setRequired();

  	$form->onSuccess[] = $this->collectibleEditFormSubmitted;

		return $form;
	}

   public function collectibleEditFormSubmitted($form,$values)
	{
  if ($values['img']->isOk()) {
    //$id = $this->database->query("SHOW TABLE STATUS LIKE 'locations' ")->fetch()->Auto_increment;
    $file=$this->database->table('collectibles')->get($this->getParameter('id'))->cover;
    if (!(empty($file)))unlink(WWW_DIR.substr($file,15));
    $filename = $this->getUser()->identity->data['username'].$this->getParam("id");
    $targetPath = $this->presenter->basePath;

    $pripona = pathinfo($values['img']->getSanitizedName(), PATHINFO_EXTENSION);
    $cil=WWW_DIR."/images/location/$filename.$pripona";
    $cil2=$targetPath."images/location/$filename.$pripona";
    $values['img']->move($cil);
    }
		$this->database->table('collectibles')->get($this->getParameter('id'))->update(array(

        'name' => $values->name,
        'description' => $values->description,
        'user_id' =>  $this->getUser()->identity->id,
        'origin' =>   $values->origin,
    ));
    if(isset($cil2)){	$this->database->table('collectibles')->get($this->getParameter('id'))->update(array('picture' => $cil2,));}
    if(!empty($values->category)){	$this->database->table('collectibles')->get($id)->update(array('category_id' =>   $values->category));}  
    $this->flashMessage('Předmět byl upraven.');
		$this->redirect('Collectible:',$this->getParameter('id'));

	}

}
