<?php

namespace App\Presenters;
use Nette\Application\UI\Form as Form;
use Nette;
use App\Model;
use Nette\Utils\Strings;
use Nette\Application\UI\Multiplier;


class ChatPresenter extends BasePresenter
{
  public $id;
	public function renderDefault($id)
	{
 
	 $chats=$this->template->chats=$this->database->query("SELECT DISTINCT user_id, username,picture FROM (SELECT u.user_id,u.firstName,u.username,u.surname,u.picture,`timestamp` FROM messages join users u on u.user_id=`reciever_id` WHERE `sender_id`=? UNION SELECT u2.user_id,u2.firstName,u2.username,u2.surname,u2.picture,`timestamp` FROM messages join users u2 on u2.user_id=`sender_id` WHERE `reciever_id`=? ORDER BY timestamp DESC) as t",$this->getUser()->id,$this->getUser()->id)->fetchAll();   
	  if(!(isset($id)))  $this->redirect('Chat:',$chats[0]['user_id']);
   $this->template->messages=$this->database->query('SELECT * FROM messages WHERE reciever_id=? and sender_id=? or sender_id=? and reciever_id=? ORDER BY timestamp ASC',$this->getUser()->id,$id,$this->getUser()->id,$id)->fetchAll();
  }
  
  protected function createComponentMessageForm()
	{

    $form = new Form;
    $form->addProtection();
    $form->addText('text')->setAttribute('placeholder','Napište zprávu')->setAttribute('class','form-control');
    $form->addSubmit('send', 'Poslat')->setAttribute('class','form-control');
    $form->onSuccess[] = $this->messageFormSubmitted;
    $renderer = $form->getRenderer();
		return $form;
    
  }
 public function messageFormSubmitted($form,$values)
	{
  if(!empty($values->text)){
        $this->database->table('messages')->insert(array(
        'sender_id' => $this->getUser()->id,
        'text' => $values->text,
        'reciever_id' => $this->getParameter('id'),
        ));
        }
  $this->redirect('Chat:',$this->getParameter('id'));
  }
  
  
  

}
