<?php

namespace App\Presenters;

use Nette\Application\UI\Form as Form;
use Nette;
use App\Model;
use Nette\Utils\Strings;
use Nette\Application\UI\Multiplier;


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
        $this->template->topics = $this->em->getRepository('App\Model\Entity\Topic')->findBy([],['createdDateTime' => 'DESC']);
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
        $form->addText('title')->setAttribute('placeholder', 'Zadejte název klubu')->setAttribute('class', 'form-control')->addRule(Form::MIN_LENGTH, 'Minimálně %d znaků.', 5)->addRule(Form::MAX_LENGTH, 'Název je příliš dlouhý.', 40);
        $form->addTextArea('description')->setAttribute('placeholder', 'Zde zadejte úvodní příspěvek nového sběratelského klubu, typicky specifikaci tématu.')->setAttribute('class', 'form-control');
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
        foreach ($values->categories as $category)
        {
            $categories[]=$this->em->find('App\Model\Entity\Category',$category);
        }
        $user = $this->em->find('App\Model\Entity\User',$this->getUser()->getId());
        $topic->setUser($user)->setDescription($values->description)->setCategories($categories)->setTitle($values->title);
        $this->em->persist($topic);
        $this->em->flush();
        $this->redirect('Club:show', $topic->getId());
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

            $this->database->table('comments')->insert(array(
                'text' => $values->text,
                'topic_id' => $this->getParameter('id'),
                'user_id' => $this->user->identity->id,
            ));
            $this->redirect('Club:show', $this->getParameter('id'));
        }
    }


}
