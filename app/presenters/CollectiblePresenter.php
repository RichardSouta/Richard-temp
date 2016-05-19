<?php

namespace App\Presenters;
use Nette\Application\UI\Form as Form;
use Nette;
use App\Model;
use Nette\Application\UI\Multiplier;


class CollectiblePresenter extends BasePresenter
{
  public $id;
	public function renderDefault($id)
	{
	    $collectible=$this->template->collectible=$this->database->table('collectibles')->get($id);
      if(!empty($collectible["category_id"]))$this->template->category=$this->database->table('categories')->get($collectible["category_id"])->name;      
      $this->template->vlastnik=$this->database->table('users')->get($collectible["user_id"]);
	}
  
  public function renderEdit($id)
	{
	     if (!($this->user->isLoggedIn())){
      $this->redirect('Homepage:');
      
      }     
     
	}
  
   public function renderTrade($id)
	{
	     if (!($this->user->isLoggedIn())){
      $this->redirect('Homepage:');
      
      }     
     
	}
  
  public function renderConfirm($id)
	{
	     if (!($this->user->isLoggedIn())){
      $this->redirect('Homepage:');
      
      }
    $collectible=$this->template->collectible=$this->database->table('collectibles')->get($this->getParameter('id'));
    $this->template->category=$this->database->table('categories')->get($collectible["category_id"])->name;     
     
	}
  
  public function renderFinal($id,$id2)
	{
	     if (!($this->user->isLoggedIn())){
      $this->redirect('Homepage:');
      
      }
    $collectible=$this->template->collectible=$this->database->table('collectibles')->get($id);
    $this->template->category=$this->database->table('categories')->get($collectible["category_id"])->name;
    
    $collectible2=$this->template->collectible2=$this->database->table('collectibles')->get($id2);
    $this->template->category2=$this->database->table('categories')->get($collectible2["category_id"])->name;     
     
	}
  
  protected function createComponentCollectibleForm()
	{
    $form = new Form;
    $form->addProtection();
    $form->addText('name', 'Název předmětu')->setRequired()->setAttribute('class','form-control');  

    $form->addTextArea('description', 'Popis předmětu')->setRequired()->setAttribute('class','form-control');

    $form->addTextArea('origin', 'Původ předmětu')->setRequired()->setAttribute('class','form-control');

    $categories=$this->database->table('categories')->select('*')->fetchPairs('category_id','name');
    $form->addSelect('category','Kategorie',$categories)->setAttribute('class','form-control');


    $form->addUpload('img', 'Foto předmětu')->addRule(Form::IMAGE, 'Cover must be JPEG, PNG or GIF.')->setAttribute('class','form-control');


    $form->addSubmit('send', 'nový předmět')->setRequired()->setAttribute('class','form-control')->setAttribute('id','submit_button');

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
    $cil=WWW_DIR."/images/collectible/$filename.$pripona";
    $cil2=$targetPath."images/collectible/$filename.$pripona";
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
    $form->addText('name', 'název')->setValue($data->name)->setRequired()->setAttribute('class','form-control');

    $form->addTextArea('description', 'popis')->setValue($data->description)->setAttribute('class','form-control')->setRequired();

    $form->addTextArea('origin', 'původ')->setValue($data->origin)->setAttribute('class','form-control')->setRequired();

    $categories=$this->database->table('categories')->select('*')->fetchPairs('category_id','name');
    $form->addSelect('category','kategorie',$categories)->setValue($data->category_id)->setAttribute('class','form-control');

    $form->addUpload('img', 'Upload your image')->setAttribute('class','form-control')->setRequired(false)->addCondition(Form::FILLED)->addRule(Form::IMAGE, 'Cover must be JPEG, PNG or GIF.');

    $form->addSubmit('send', 'Upravit předmět')->setAttribute('class','form-control')->setAttribute('id','submit_button')->setRequired();

  	$form->onSuccess[] = $this->collectibleEditFormSubmitted;

		return $form;
	}

   public function collectibleEditFormSubmitted($form,$values)
	{
  if ($values['img']->isOk()) {
    //$id = $this->database->query("SHOW TABLE STATUS LIKE 'collectibles' ")->fetch()->Auto_increment;
    $file=$this->database->table('collectibles')->get($this->getParameter('id'))->cover;
    if (!(empty($file)))unlink(WWW_DIR.substr($file,15));
    $filename = $this->getUser()->identity->data['username'].$this->getParam("id");
    $targetPath = $this->presenter->basePath;

    $pripona = pathinfo($values['img']->getSanitizedName(), PATHINFO_EXTENSION);
    $cil=WWW_DIR."/images/collectible/$filename.$pripona";
    $cil2=$targetPath."images/collectible/$filename.$pripona";
    $values['img']->move($cil);
    }
		$this->database->table('collectibles')->get($this->getParameter('id'))->update(array(

        'name' => $values->name,
        'description' => $values->description,
        'user_id' =>  $this->getUser()->identity->id,
        'origin' =>   $values->origin,
    ));
    if(isset($cil2)){	$this->database->table('collectibles')->get($this->getParameter('id'))->update(array('picture' => $cil2,));}
    if(!empty($values->category)){	$this->database->table('collectibles')->get($this->getParameter('id'))->update(array('category_id' =>   $values->category));}  
    $this->flashMessage('Předmět byl upraven.');
		$this->redirect('Collectible:',$this->getParameter('id'));

	}
  
     protected function createComponentMessageForm()
	{
  return new Multiplier(function ($reciever_id) {
    $form = new Form;
    $form->addProtection();
    $form->addText('text')->setAttribute('placeholder','Napište zprávu')->setAttribute('class','form-control');
    $form->addSubmit('send', 'Send')->setAttribute('class','form-control')->setAttribute('id','submit_button');
    $form->addHidden('reciever_id', $reciever_id);
    $form->onSuccess[] = $this->messageFormSubmitted;
    $renderer = $form->getRenderer();
    $renderer->wrappers['controls']['container'] = NULL;
    $renderer->wrappers['pair']['container'] = NULL;
    $renderer->wrappers['label']['container'] = NULL;
    $renderer->wrappers['control']['container'] = NULL;
		return $form;
    
    });
    }
 public function messageFormSubmitted($form,$values)
	{
  if(!empty($values->text)){
        $this->database->table('messages')->insert(array(
        'sender_id' => $this->getUser()->id,
        'text' => $values->text,
        'reciever_id' => $values->reciever_id,
        ));
        }
  $this->redirect('Chat:');
  }
  
  protected function createComponentTradeForm()
	{
    $form = new Form;
    $form->addProtection();

    $users=$this->database->table('users')->select('*')->fetchPairs('user_id','username');
    $form->addSelect('user','Uživatel',$users)->setAttribute('class','form-control');

    $form->addSubmit('send', 'Poslat nabídku')->setRequired()->setAttribute('class','form-control')->setAttribute('id','submit_button');

  	$form->onSuccess[] = $this->tradeFormSubmitted;
    
		return $form;
	}

   public function tradeFormSubmitted($form,$values)
	{ 
    $collectible=$this->database->table('collectibles')->get($this->getParameter('id'));
    $category=$this->database->table('categories')->get($collectible["category_id"])->name;
    $message="Uživatel ".$this->getUser()->identity->username." Vám nabízí k výměně předmět ".$collectible->name." z kategorie ".$category." kliknutím na tuto zprávu se dostanete na detail nabídky.";
    $link = Nette\Utils\Html::el('a')->href($this->link('//Collectible:confirm', $this->getParameter('id')))->setText($message);
    $link = Nette\Utils\Html::el('div')->class("trade")->setText($link);
		$this->database->table('messages')->insert(array(
        'sender_id' => $this->getUser()->id,
        'text' => $link,
        'reciever_id' => $values->user,
        ));
    
    $this->flashMessage('Žádost byla odeslána.');
		$this->redirect('Homepage:');

	}
  
   protected function createComponentConfirmTradeForm()
	{
    $form = new Form;
    $form->addProtection();

    $collectibles=$this->database->table('collectibles')->select('*')->fetchPairs('collectible_id','name');
    $form->addSelect('collectible','Sběratelský předmět',$collectibles)->setAttribute('class','form-control');

    $form->addSubmit('send', 'Poslat nabídku')->setRequired()->setAttribute('class','form-control')->setAttribute('id','submit_button');

  	$form->onSuccess[] = $this->confirmTradeFormSubmitted;
    
		return $form;
	}

   public function confirmTradeFormSubmitted($form,$values)
	{ 
    $collectible=$this->database->table('collectibles')->get($this->getParameter('id'));
    $category=$this->database->table('categories')->get($collectible["category_id"])->name; 
    $message="Uživatel ".$this->getUser()->identity->username." Vám nabízí k výměně předmět ".$collectible->name." z kategorie ".$category." kliknutím na tuto zprávu se dostanete na detail nabídky.";
    $link = Nette\Utils\Html::el('a')->href($this->link('//Collectible:final', $this->getParameter('id'),$values->collectible))->setText($message);
    $link = Nette\Utils\Html::el('div')->class("trade")->setText($link);
		$this->database->table('messages')->insert(array(
        'sender_id' => $this->getUser()->id,
        'text' => $link,
        'reciever_id' => $collectible->user_id,
        ));
    
    $this->flashMessage('Žádost byla odeslána.');
		$this->redirect('Homepage:');

	}
  
  protected function createComponentFinalForm()
	{
    $form = new Form;
    $form->addSubmit('send', 'Schválit nabídku')->setRequired()->setAttribute('class','form-control')->setAttribute('id','submit_button');

  	$form->onSuccess[] = $this->finalFormSubmitted;
    
		return $form;
	}

   public function finalFormSubmitted($form,$values)
	{ 
    $id2=$this->database->table('collectibles')->get($this->getParameter('id'))->user_id;
    $id=$this->database->table('collectibles')->get($this->getParameter('id2'))->user_id;
    
    $this->database->table('collectibles')->get($this->getParameter('id'))->update(array(
        'user_id' =>  $id,
    ));
    
    $this->database->table('collectibles')->get($this->getParameter('id2'))->update(array(
        'user_id' =>  $id2,
    ));
    
    $this->flashMessage('Předměty byly vyměněny.');
		$this->redirect('Homepage:');

	}

}
