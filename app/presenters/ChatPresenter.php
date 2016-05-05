<?php

namespace App\Presenters;
use Nette\Application\UI\Form as Form;
use Nette;
use App\Model;
use Nette\Utils\Strings;
use Nette\Application\UI\Multiplier;


class ChatPresenter extends BasePresenter
{
  public $id1,$id2;
	public function renderDefault()
	{
	 $this->template->chats=$this->database->query("SELECT DISTINCT user_id,surname,firstName,picture FROM (SELECT u.user_id,u.firstName,u.surname,u.picture,`timestamp` FROM messages join users u on u.user_id=`reciever_id` WHERE `reciever_id`=1 UNION SELECT u2.user_id,u2.firstName,u2.surname,u2.picture,`timestamp` FROM messages join users u2 on u2.user_id=`sender_id` WHERE `sender_id`=1 ORDER BY timestamp DESC) AS t")->fetchAll();   
	}
   protected function createComponentMessageForm()
	{
  return new Multiplier(function ($itemId) {
    $form = new Form;
    $form->addProtection();
    $form->addText('text')->setAttribute('placeholder','Type a message…')->setAttribute('class','form-control');
    $form->addSubmit('send', 'Send');
    $form->addHidden('itemId', $itemId);
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
  $id_prijemce=$this->database->query('SELECT b.id_author FROM bookings b WHERE b.id=?',$values->itemId)->fetchField('id_author');
  if($id_prijemce==$this->getUser()->id) {$id_prijemce=$this->database->query('SELECT u.id as prijemce FROM bookings b join locations l ON b.id_location=l.id join users u on l.id_owner=u.id WHERE b.id=?',$values->itemId)->fetchField('prijemce');}
  $this->database->table('bookingmessages')->insert(array(
        'id_booking' => $values->itemId,
        'message' => $values->text,
        'id_user' => $this->getUser()->id,
        'id_prijemce' =>$id_prijemce
    ));
    }
  $this->redirect('Notifications:bookingmessages',$this->getParameter('id'),$this->getParameter('id2'));
  }
  
  

}
