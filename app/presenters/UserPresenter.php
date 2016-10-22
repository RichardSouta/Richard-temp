<?php

namespace App\Presenters;

use Nette\Application\UI\Form as Form;
use Nette;
use App\Model;
use Nette\Utils\Strings;
use Nette\Application\UI\Multiplier;

class UserPresenter extends BasePresenter
{
    public $id, $page, $category;

    public function renderDefault($id, $category)
    {
        $user = $this->template->uzivatel = $this->em->getRepository('App\Model\Entity\User')->find($id);
        if (isset($category)) {
            $this->template->collectibles = $this->em->getRepository('App\Model\Entity\Collectible')->findBy(['user' => $user, 'category' => $category]);
            $this->template->forTrade = $this->em->getRepository('App\Model\Entity\Collectible')->findBy(['user' => $user, 'tradeable' => true, 'category' => $category]);
        } else {
            $this->template->collectibles = $this->em->getRepository('App\Model\Entity\Collectible')->findByUser($user);
            $this->template->forTrade = $this->em->getRepository('App\Model\Entity\Collectible')->findBy(['user' => $user, 'tradeable' => true]);
        }

    }

    public function renderEdit($id)
    {
        if (!($this->user->isLoggedIn())) {
            $this->redirect('Homepage:');

        }
        if ($this->user->identity->id != $id) $this->redirect('User:', $id);
    }

    protected function createComponentProfileForm()
    {
        $form = new Form;
        $form->addProtection();
        $form->addUpload('avatar', 'Avatar:')->setAttribute('class', 'form-control')
            ->setRequired(false)
            ->addCondition(Form::FILLED)
            ->addRule(Form::IMAGE, 'Avatar musí být JPEG, PNG nebo GIF.');
        $form->addText('username', 'Your username')->setValue($this->getUser()->getIdentity()->username)->setAttribute('class', 'form-control')
            ->setRequired('Toto pole je povinné');

        $form->addText('email', 'Your e-mail')->setValue($this->getUser()->getIdentity()->email)->setAttribute('class', 'form-control')
            ->setRequired()
            ->addRule(Form::EMAIL, 'Please fill in your valid adress')
            ->emptyValue = '@';

        $form->addText('description', 'Change description')->setValue($this->getUser()->getIdentity()->description)->setAttribute('class', 'form-control');

        $form->addPassword('password', 'Your password')->setAttribute('class', 'form-control')
            ->setRequired(false)
            ->addCondition(Form::FILLED)
            ->addRule(Form::MIN_LENGTH, 'Heslo musí mít alespoň %d znaky', 6)
            ->addRule(Form::PATTERN, 'Musí obsahovat číslici', '.*[0-9].*');

        $form->addPassword('passwordVerify', 'Your password second time')->setAttribute('class', 'form-control')
            ->setRequired(false)
            ->addCondition(Form::FILLED)
            ->addRule(Form::EQUAL, 'Zadané hesla se neshodují', $form['password']);

        $form->addText('phone', 'Add your phone number')->setValue($this->getUser()->getIdentity()->phone)->setAttribute('class', 'form-control');

        $form->addSubmit('send', 'Edit your profile')->setAttribute('class', 'form-control')->setAttribute('id', 'submit_button');

        $form->onSuccess[] = $this->profileFormSubmitted;
        return $form;
    }

    public function profileFormSubmitted($form)
    {
        $values = $form->getValues();
        if ($values['avatar']->isOk()) {
            $filename = $this->getUser()->identity->data['username'];//$values['img']->getSanitizedName();
            $targetPath = $this->presenter->basePath;

            // @TODO vyřešit kolize
            $pripona = pathinfo($values['avatar']->getSanitizedName(), PATHINFO_EXTENSION);
            $cil = WWW_DIR . "/images/user/$filename.$pripona";
            $cil2 = $targetPath . "images/user/$filename.$pripona";
            $image = $values['avatar']->toImage();
            $image->resize(200, 200);
            $image->sharpen();
            $image->save($cil);
        }


        try {
            $this->database->table('users')->get($this->getUser()->id)->update(array(
                'email' => $values->email,
                'username' => $values->username,

            ));
            $this->getUser()->getIdentity()->email = $values->email;
            $this->getUser()->getIdentity()->username = $values->username;

            if (isset($cil2)) {
                $this->database->table('users')->get($this->getUser()->id)->update(array('picture' => $cil2,));
                $this->getUser()->getIdentity()->picture = $cil2;
            }
            if (!empty($values->password)) {
                $this->database->table('users')->get($this->getUser()->id)->update(array('password' => password_hash($values->password, PASSWORD_DEFAULT)));
            }
            if (!empty($values->phone)) {
                $this->database->table('users')->get($this->getUser()->id)->update(array('phone' => $values->phone));
                $this->getUser()->getIdentity()->phone = $values->phone;
            }
            if (!empty($values->description)) {
                $this->database->table('users')->get($this->getUser()->id)->update(array('description' => $values->description));
                $this->getUser()->getIdentity()->description = $values->description;
            }
        } //	$this->redirect('Homepage:');}
        catch (\PDOException $e) {
            if (Strings::contains($e, 'username')) {
                $form['username']->addError('Username is already taken');
            }
            if (Strings::contains($e, 'email')) {
                $form['email']->addError('Email is already used');
            } else {
                throw $e;
            }
        }
        $this->flashMessage('Profil byl upraven.');
        $this->redirect('User:', $this->getParameter('id'));
    }

    protected function createComponentCategoryForm()
    {
        $form = new Form;
        $form->addProtection();
        $categories = $this->em->getRepository('App\Model\Entity\Category')->findPairs('name', 'id');
        if ($this->getParameter('category') != NULL) $form->addSelect('category', '', $categories)->setAttribute('class', 'form-control')->setAttribute('onchange', "document.getElementById('frm-categoryForm').submit();")->setPrompt('Všechny kategorie')->setValue($this->getParameter('category'));
        else $form->addSelect('category', '', $categories)->setAttribute('class', 'form-control')->setAttribute('onchange', "document.getElementById('frm-categoryForm').submit();")->setPrompt('Všechny kategorie');
        $form->onSuccess[] = $this->categoryFormSubmitted;
        $renderer = $form->getRenderer();
        $renderer->wrappers['controls']['container'] = NULL;
        $renderer->wrappers['pair']['container'] = NULL;
        $renderer->wrappers['label']['container'] = NULL;
        $renderer->wrappers['control']['container'] = NULL;
        return $form;
    }

    public function categoryFormSubmitted($form)
    {
        $values = $form->getValues();

        $this->redirect('User:', array("id" => $this->getParameter('id'), "category" => $values->category));
    }

}
