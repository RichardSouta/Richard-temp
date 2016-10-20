<?php

namespace App\Presenters;

use Nette\Application\UI\Form as Form;
use Nette;
use App\Model;
use Nette\Utils\Strings;
use Nette\Application\UI\Multiplier;
use App\Model\Entity\Message;


class ChatPresenter extends BasePresenter
{

    /**
     * @param $id Model\Entity\Chat id
     */
    public function renderDefault($id)
    {
        if (!($this->user->isLoggedIn())) {
            $this->redirect('Homepage:');

        }
        $chats = $this->template->chats = $this->em->getRepository('App\Model\Entity\User')->find($this->user->id)->getChats();
        //var_dump($chats[0]);exit();
        if (empty($chats[0])) {
            $this->flashMessage('Žádné konverzace k zobrazení. Zahajte nějakou přes symbol zprávy na profilu předmětu nebo uživatele.');
            $this->redirect('Homepage:');
        }
        if (!(isset($id))) $this->redirect('Chat:', $chats[0]->getId());
        $this->template->messages = $this->em->getRepository('App\Model\Entity\Chat')->find($id)->getMessages();
    }

    public function ShowChatAction(Model\Entity\Chat $chat){



    }
    protected function createComponentMessageForm()
    {

        $form = new Form;
        $form->addProtection();
        $form->addText('text')->setAttribute('placeholder', 'Napište zprávu')->setAttribute('class', 'form-control');
        $form->addSubmit('send', 'Poslat')->setAttribute('class', 'form-control');
        $form->onSuccess[] = $this->messageFormSubmitted;
        $renderer = $form->getRenderer();
        return $form;

    }

    public function messageFormSubmitted($form, $values)
    {
        if (!empty($values->text)) {
            $message = new Message();
            $chat = $this->em->find('App\Model\Entity\Chat',$this->getParameter('id'));
            $user = $this->em->find('App\Model\Entity\User',$this->getUser()->getId());
            $message->setChat($chat)->setSender($user)->setText($values->text);
            $this->em->persist($message);
            $this->em->flush();
        }
        $this->redirect('Chat:', $this->getParameter('id'));
    }


}
