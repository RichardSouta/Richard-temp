<?php

namespace App\Presenters;

use Nette\Application\UI\Form as Form;
use Nette;
use App\Model;
use Nette\Utils\Strings;
use Nette\Application\UI\Multiplier;
use Nette\Utils\Finder;
use Nette\Utils\Image;

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

    public function renderEdit()
    {
        if (!($this->user->isLoggedIn())) {
            $this->redirect('Homepage:');
        }
    }

    protected function createComponentProfileForm()
    {
        /** @var Model\Entity\User $user */
        $user = $this->em->find('App\Model\Entity\User', $this->user->getId());
        $form = new Form;
        $form->addProtection();
        $form->addUpload('avatar', 'Obrázek')->setAttribute('class', 'form-control')
            ->setRequired(false)
            ->addCondition(Form::FILLED)
            ->addRule(Form::IMAGE, 'Musí být obrázek!');
        $form->addText('username', 'Uživatelské jméno')->setValue($user->getUsername())->setAttribute('class', 'form-control')
            ->setRequired('Toto pole je povinné');

        $form->addText('name', 'Jméno')->setValue($user->getName())->setAttribute('class', 'form-control')
            ->setRequired('Toto pole je povinné');

        $form->addText('surname', 'Příjmení')->setValue($user->getSurname())->setAttribute('class', 'form-control')
            ->setRequired('Toto pole je povinné');

        $form->addText('email', 'E-mail')->setValue($user->getEmail())->setAttribute('class', 'form-control')
            ->setRequired('Toto pole je povinné')
            ->addRule(Form::EMAIL, 'Prosím vyplňte skutečný e-mail.')
            ->emptyValue = '@';

        $form->addTextArea('description', 'Zde napište něco o sobě')->setValue($user->getDescription())->setAttribute('class', 'form-control');

        $form->addCheckbox('notification', 'Chcete dostávat na e-mail notifikace?')
            ->setValue($user->getNotification());

        $form->addPassword('password', 'heslo')->setAttribute('class', 'form-control')
            ->setRequired(false)
            ->addCondition(Form::FILLED)
            ->addRule(Form::MIN_LENGTH, 'Heslo musí mít alespoň %d znaky', 6)
            ->addRule(Form::PATTERN, 'Musí obsahovat číslici', '.*[0-9].*');

        $form->addPassword('passwordVerify', 'heslo znovu')->setAttribute('class', 'form-control')
            ->setRequired(false)
            ->addCondition(Form::FILLED)
            ->addRule(Form::EQUAL, 'Zadané hesla se neshodují', $form['password']);

        $form->addText('phone', 'Telefoní číslo')->setValue($user->getPhone())->setAttribute('class', 'form-control');

        $form->addSubmit('send', 'Upravit profil')->setAttribute('class', 'form-control')->setAttribute('id', 'submit_button');

        $form->onSuccess[] = $this->profileFormSubmitted;
        return $form;
    }

    public function profileFormSubmitted($form)
    {
        $values = $form->getValues();
        if ($values['avatar']->isOk()) {
            $name = $this->user->getId();
            foreach (Finder::findFiles($name . "-*.*")->in('../www/images/user') as $key => $file) {
                unlink($key);
            }
            $image = $values['avatar']->toImage();
            $image->resize(200, 200);
            $image->sharpen();
            $name = $this->user->getId();
            $image->save("../www/images/user/$name.jpg", 100, Image::JPEG);
        }

        /** @var Model\Entity\User $user */
        $user = $this->em->find('App\Model\Entity\User', $this->user->getId());
        if ($user->getEmail() != $values->email) {
            $user->setEmail($values->email);
            //$this->getUser()->getIdentity()->email = $values->email;
        }

        if ($user->getUsername() != $values->username) {
            $user->setUsername($values->username);
            $this->getUser()->getIdentity()->username = $values->username;
        }

        if (!empty($values->name)) {
            $user->setName($values->name);
        }

        if (!empty($values->surname)) {
            $user->setSurname($values->surname);
        }

        if (!empty($values->password)) {
            $user->setPassword(password_hash($values->password, PASSWORD_DEFAULT));
        }
        if (!empty($values->phone)) {
            $user->setPhone($values->phone);
            //$this->getUser()->getIdentity()->phone = $values->phone;
        }
        if (!empty($values->description)) {
            $user->setDescription($values->description);
            //$this->getUser()->getIdentity()->description = $values->description;
        }

        $user->setNotification($values->notification);

        $this->em->getConnection()->beginTransaction();
        try {
            $this->em->persist($user);
            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
            $this->em->getConnection()->rollback();
            $this->em->close();
            if (Strings::contains($e, 'username')) {
                $form['username']->addError('uživatelské jméno je obsazeno');
                return;
            }
            if (Strings::contains($e, 'email')) {
                $form['email']->addError('Email je již použit');
                return;
            } else {
                $this->flashMessage('Profil nebyl upraven.', 'warning');
                $this->redirect('User:', $this->getParameter('id'));
            }
        }

        $this->flashMessage('Profil byl upraven.');
        $this->redirect('User:', $this->user->id);
    }

    protected function createComponentCategoryForm()
    {
        $form = new Form;
        $form->addProtection();
        $categories = $this->em->getRepository('App\Model\Entity\Category')->findPairs('name', 'id');
        $userCategories = [];
        $user = $this->em->getRepository('App\Model\Entity\User')->find($this->getParameter('id'));
        $collectibles = $user->getCollectibles();
        /** @var Model\Entity\Collectible $collectible */
        foreach ($collectibles as $collectible) {
            if ($key = array_search($collectible->getCategory()->getName(), $categories)) {
                $userCategories[$key] = $categories[$key];
                unset($categories[$key]);
            }
        }

        if ($this->getParameter('category') != NULL) $form->addSelect('category', '', $userCategories)->setAttribute('class', 'form-control')->setAttribute('onchange', "document.getElementById('frm-categoryForm').submit();")->setPrompt('Všechny kategorie')->setValue($this->getParameter('category'));
        else $form->addSelect('category', '', $userCategories)->setAttribute('class', 'form-control')->setAttribute('onchange', "document.getElementById('frm-categoryForm').submit();")->setPrompt('Všechny kategorie');
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
