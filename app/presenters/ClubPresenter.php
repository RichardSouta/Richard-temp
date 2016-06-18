<?php

namespace App\Presenters;
use Nette\Application\UI\Form as Form;
use Nette;
use App\Model;
use Nette\Utils\Strings;
use Nette\Application\UI\Multiplier;


class ClubPresenter extends BasePresenter
{
  public $id,$page;
  public function renderNew()
  {
    if (!($this->user->isLoggedIn())){
      $this->redirect('Homepage:');
      
      }  
  }
	public function renderDefault($page)
	{
 $this->template->topics=$this->database->query('Select title, cid as user_id, username, picture, cda as datetime, tid as topic_id FROM(SELECT title, comments.user_id as cid, comments.datetime as cda, topics.topic_id as tid FROM topics JOIN comments on topics.topic_id=comments.topic_id LIMIT 1) as t JOIN users ON users.user_id=cid')->fetchAll();
	}

  public function renderShow($id)
	{
   $this->template->title=$this->database->table('topics')->get($id)->title;
   $this->template->comments=$this->database->query('SELECT text, picture, username, datetime, comments.user_id FROM comments JOIN users on comments.user_id=users.user_id where topic_id=?',$id)->fetchAll();
	}
  protected function createComponentNewClubForm(){ 
    $form = new Form;
    $form->addProtection();
    $form->addText('title')->setAttribute('placeholder','Zadejte název klubu')->setAttribute('class','form-control')->addRule(Form::MIN_LENGTH, 'Minimálně %d znaků.', 5)->addRule(Form::MAX_LENGTH, 'Název je příliš dlouhý.', 40);
    $form->addTextArea('message')->setAttribute('placeholder','Zde zadejte úvodní příspěvek nového sběratelského předmětu, typicky specifikaci tématu.')->setAttribute('class','form-control');
    $form->addSubmit('send', 'Založit klub')->setAttribute('class','form-control')->setAttribute('id','submit_button');
    $form->onSuccess[] = $this->newClubFormSubmitted;
    $renderer = $form->getRenderer();
    $renderer->wrappers['controls']['container'] = NULL;
    $renderer->wrappers['pair']['container'] = NULL;
    $renderer->wrappers['label']['container'] = NULL;
    $renderer->wrappers['control']['container'] = NULL;
		return $form;    
  }
  
 public function newClubFormSubmitted($form,$values)
	{
  $id = $this->database->query("SHOW TABLE STATUS LIKE 'topics' ")->fetch()->Auto_increment;
  if((!empty($values->title))&&(!empty($values->message))){
        $this->database->table('topics')->insert(array(
        'title' => $values->title,
        ));
        
         $this->database->table('comments')->insert(array(
        'text' => $values->message,
        'topic_id' => $id,
        'user_id'  => $this->user->identity->id,
        ));        
                        $this->redirect('Club:show',$id);} 
  }
  
  protected function createComponentClubForm(){ 
    $form = new Form;
    $form->addProtection();
    $form->addText('text')->setAttribute('placeholder','Zde napište text vašeho příspěvku.')->setAttribute('class','form-control');
    $form->addSubmit('send', 'Odeslat příspěvek')->setAttribute('class','form-control')->setAttribute('id','submit_button');
    $form->onSuccess[] = $this->clubFormSubmitted;
    $renderer = $form->getRenderer();
    $renderer->wrappers['controls']['container'] = NULL;
    $renderer->wrappers['pair']['container'] = NULL;
    $renderer->wrappers['label']['container'] = NULL;
    $renderer->wrappers['control']['container'] = NULL;
		return $form;    
  }
  
 public function clubFormSubmitted($form,$values)
	{
  if((!empty($values->text))){

         $this->database->table('comments')->insert(array(
        'text' => $values->text,
        'topic_id' => $this->getParameter('id'),
        'user_id'  => $this->user->identity->id,
        ));        
                        $this->redirect('Club:show',$this->getParameter('id'));} 
  }
  
  

}
