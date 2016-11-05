<?php

namespace App\Presenters;

use Nette\Application\UI\Form as Form;
use Nette;
use App\Model;
use Nette\Utils\Strings;
use Nette\Application\UI\Multiplier;
use Nette\Mail\Message;


class ClubPresenter extends BasePresenter
{
    public $id;

    public function renderNew()
    {
        if (!($this->user->isLoggedIn())) {
            $this->redirect('Homepage:');

        }
    }

    public function renderDefault($page)
    {
        $this->template->topics = $this->em->getRepository('App\Model\Entity\Topic')->findBy([], ['relevance' => 'DESC']);
        //relevance SELECT (COUNT(*) / (1 + TIMESTAMPDIFF(DAY, `datetime`, NOW())/30))  FROM comments where `topic_id`=3
    }

    public function renderDisplay($id)
    {
        $this->template->topic = $this->em->getRepository('App\Model\Entity\Topic')->find($id);
    }

    protected function createComponentNewClubForm()
    {
        $form = new Form;
        $form->addProtection();
        $categories = $this->em->getRepository('App\Model\Entity\Category')->findPairs('name', 'id');
        $form->addMultiSelect('categories', 'Vyberte sběratelské kategorie.', $categories)->setAttribute('class', 'form-control');
        $form->addText('title')->setAttribute('placeholder', 'Zadejte název klubu')->setAttribute('class', 'form-control')->addRule(Form::MIN_LENGTH, 'Minimálně %d znaků.', 5)->addRule(Form::MAX_LENGTH, 'Název je příliš dlouhý.', 100);
        $form->addTextArea('description')->setAttribute('placeholder', 'Zde zadejte úvodní příspěvek nového sběratelského klubu, typicky specifikaci tématu.')->setAttribute('class', 'form-control')->addRule(Form::MIN_LENGTH, 'Minimálně %d znaků.', 20);
        $form->addSubmit('send', 'Založit klub')->setAttribute('class', 'form-control')->setAttribute('id', 'submit_button');
        $form->onSuccess[] = $this->newClubFormSubmitted;
        $renderer = $form->getRenderer();
        $renderer->wrappers['controls']['container'] = NULL;
        $renderer->wrappers['pair']['container'] = NULL;
        $renderer->wrappers['label']['container'] = NULL;
        $renderer->wrappers['control']['container'] = NULL;
        return $form;
    }

    public function newClubFormSubmitted($form, $values)
    {
        $topic = new Model\Entity\Topic();
        $categories = [];
        foreach ($values->categories as $category) {
            $categories[] = $this->em->find('App\Model\Entity\Category', $category);
        }
        $user = $this->em->find('App\Model\Entity\User', $this->getUser()->getId());
        $topic->setUser($user)->setDescription($values->description)->setCategories($categories)->setTitle($values->title);
        $this->em->persist($topic);
        $this->em->flush();
        $this->redirect('Club:display', $topic->getId());
    }

    protected function createComponentClubForm()
    {
        $form = new Form;
        $form->addProtection();
        $form->addText('text')->setAttribute('placeholder', 'Zde napište text vašeho příspěvku.')->setAttribute('class', 'form-control');
        $form->addSubmit('send', 'Odeslat příspěvek')->setAttribute('class', 'form-control')->setAttribute('id', 'submit_button');
        $form->onSuccess[] = $this->clubFormSubmitted;
        $renderer = $form->getRenderer();
        $renderer->wrappers['controls']['container'] = NULL;
        $renderer->wrappers['pair']['container'] = NULL;
        $renderer->wrappers['label']['container'] = NULL;
        $renderer->wrappers['control']['container'] = NULL;
        return $form;
    }

    public function clubFormSubmitted($form, $values)
    {
        if ((!empty($values->text))) {
            $comment = new Model\Entity\Comment();
            $user = $this->em->getRepository('App\Model\Entity\User')->find($this->getUser()->id);
            /** @var Model\Entity\Topic $topic */
            $topic = $this->em->getRepository('App\Model\Entity\Topic')->findOneById($this->getParameter('id'));
            $topic->setRelevance();
            $userList = [];
            $userList[] = $this->user->getId();
            foreach ($topic->getComments() as $comment) {
                if ((!in_array($comment->getUser()->getId(), $userList)) && $comment->getUser()->getNotification()) {
                    $userList[] = $comment->getUser()->getId();
                    $mail = new Message;
                    $link = Nette\Utils\Html::el('a')->href($this->link('//Club:display', $this->getParameter('id')))->setText('Zobrazit sběratelský klub');
                    $mail->setFrom('info@collectorsnest.eu')
                        ->addTo($comment->getUser()->getEmail())
                        ->setSubject('Nový příspěvek ve sběratelském klubu ' . $topic->getTitle())
                        ->setHTMLBody('<style>.wrapper{width:100%;height:auto;}.container{max-width: 90%;margin:0 auto;}.mail_header{background-color:#5cb85c;padding: 0.5em;}.mail_body{text-align:center;margin-top: 1em;}.mail_body p{max-width: 80%; margin: 1em auto;}.large{font-size: 1.5em;margin-top: 2em;}.bottom{margin-top: 2em;}.confirm_button{background-color:#5cb85c; padding: 0.5em; margin: 150px auto 0;}.confirm_button a {text-decoration: none; color: #000;}.confirm_button:hover{opacity:0.9;}</style><div class="wrapper"><div class="container"><div class="mail_header">The collectors\' nest</div><div class="mail_body"><p class="large">Ahoj ' . $comment->getUser()->getUsername() . ' </p><p> Ve sběratelském klubu který sleduješ je nový příspěvek. Team collectors\' nest</p><p class="bottom"><span class="confirm_button">' . $link . '</span></p></div></div>');
                    $this->mailer->send($mail);
                }
            }

            $comment->setText($values->text)->setUser($user)->setTopic($topic);
            $this->em->persist($comment);
            $this->em->flush();
            $this->redirect('Club:display', $this->getParameter('id'));
        }
    }

    protected function createComponentReportCommentForm()
    {
        return new Multiplier(function ($comment_id) {
            $form = new Form;
            $form->addProtection();
            $form->addTextArea('text')->setAttribute('placeholder', 'Uveďte důvod nahlášení')->setAttribute('class', 'form-control');
            $form->addSubmit('send', 'Odeslat')->setAttribute('class', 'form-control')->setAttribute('id', 'submit_button');
            $form->addHidden('comment_id', $comment_id);
            $form->onSuccess[] = $this->reportCommentFormSubmitted;
            $renderer = $form->getRenderer();
            $renderer->wrappers['controls']['container'] = NULL;
            $renderer->wrappers['pair']['container'] = NULL;
            $renderer->wrappers['label']['container'] = NULL;
            $renderer->wrappers['control']['container'] = NULL;
            return $form;

        });
    }

    public function reportCommentFormSubmitted($form, $values)
    {
        $mail = new Message;
        $mail->setFrom('postmaster@collectorsnest.eu')
            ->addTo('info@collectorsnest.eu')
            ->setSubject('Hlášení příspěvku ' . $values->comment_id)
            ->setHTMLBody('<style>.wrapper{width:100%;height:auto;}.container{max-width: 90%;margin:0 auto;}.mail_header{background-color:#5cb85c;padding: 0.5em;}.mail_body{text-align:center;margin-top: 1em;}.mail_body p{max-width: 80%; margin: 1em auto;}.large{font-size: 1.5em;margin-top: 2em;}.bottom{margin-top: 2em;}.confirm_button{background-color:#5cb85c; padding: 0.5em; margin: 150px auto 0;}.confirm_button a {text-decoration: none; color: #000;}.confirm_button:hover{opacity:0.9;}</style><div class="wrapper"><div class="container"><div class="mail_header">collectors\' nest</div><div class="mail_body">' . $values->text . '</div></div>');
        $this->mailer->send($mail);

        $this->flashMessage('Děkujeme za pomoc. Zpráva nyní bude prověřena.', 'success');

    }
}
