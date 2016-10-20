<?php

namespace App\Presenters;

use Nette\Application\UI\Form as Form;
use Nette;
use App\Model;
use Nette\Application\UI\Multiplier;
use Nette\Utils\Image;


class CollectiblePresenter extends BasePresenter
{
    public $id;

    /** @persistent */
    public $page;


    public function renderDefault($id)
    {
        $collectible = $this->template->collectible = $this->em->getRepository('App\Model\Entity\Collectible')->find($id);
    }

    public function renderEdit($id)
    {
        if (!($this->user->isLoggedIn())) {
            $this->redirect('Homepage:');

        }

    }

    public function renderTrade($id)
    {
        if (!($this->user->isLoggedIn())) {
            $this->redirect('Homepage:');

        }

    }

    public function renderConfirm($id)
    {
        if (!($this->user->isLoggedIn())) {
            $this->redirect('Homepage:');

        }
        $collectible = $this->template->collectible = $this->database->table('collectibles')->get($this->getParameter('id'));
        $this->template->category = $this->database->table('categories')->get($collectible["category_id"])->name;

    }

    public function renderFinal($id, $id2)
    {
        if (!($this->user->isLoggedIn())) {
            $this->redirect('Homepage:');

        }
        $collectible = $this->template->collectible = $this->database->table('collectibles')->get($id);
        $this->template->category = $this->database->table('categories')->get($collectible["category_id"])->name;

        $collectible2 = $this->template->collectible2 = $this->database->table('collectibles')->get($id2);
        $this->template->category2 = $this->database->table('categories')->get($collectible2["category_id"])->name;

    }

    protected function createComponentCollectibleForm()
    {
        $form = new Form;
        $form->addProtection();
        $form->addText('name', 'Název předmětu')->setRequired()->setAttribute('class', 'form-control');

        $form->addTextArea('description', 'Popis předmětu')->setRequired()->setAttribute('class', 'form-control');

        $form->addTextArea('origin', 'Původ předmětu')->setRequired()->setAttribute('class', 'form-control');

        $categories = $this->em->getRepository('App\Model\Entity\Category')->findPairs('name', 'id');
        $form->addSelect('category', 'Kategorie', $categories)->setAttribute('class', 'form-control');


        $form->addMultiUpload('imgs', 'Fotky předmětu')->addRule(Form::MAX_FILE_SIZE, 'Maximální velikost obrázku je 4 MB.', 4 * 1024 * 1024 /* v bytech */)
            ->addRule(Form::IMAGE, 'Pouze obrázky!')
            ->addRule(Form::MAX_LENGTH, 'Maximální počet obrázků je 5.', 5)
            ->setAttribute('class', 'form-control');


        $form->addSubmit('send', 'nový předmět')->setRequired()->setAttribute('class', 'form-control')->setAttribute('id', 'submit_button');


        $form->onSuccess[] = $this->collectibleFormSubmitted;

        return $form;
    }

    public function collectibleFormSubmitted($form, $values)
    {
        $count = count($values['imgs']);
        $collectible = new Model\Entity\Collectible();
        $category = $this->em->find('App\Model\Entity\Category', $values->category);
        $user = $this->em->find('App\Model\Entity\User', $this->getUser()->id);
        $collectible->setCategory($category)->setDescription($values->description)->setName($values->name)->setOrigin($values->origin)->setImages($count)->setUser($user);
        $this->em->persist($collectible);
        $this->em->flush();
        foreach ($values['imgs'] as $key => $image) {
            /** @var Nette\Http\FileUpload $image */
            if ($image->isOk()) {
                $image = $image->toImage();
                /** @var Nette\Utils\Image $image */
                $image->resize(2560, null, Image::SHRINK_ONLY);
                $image->sharpen();
                $name = $collectible->getImages()[$key];
                $image->save("../www/images/collectible/$name.jpg", 100, Image::JPEG);
            }
        }


        $this->flashMessage('Předmět byl vytvořen.');
        $this->redirect('Homepage:');

    }


    protected function createComponentCollectibleEditForm()
    {
        $form = new Form;
        $form->addProtection();
        /** @var Model\Entity\Collectible $collectible */
        $collectible = $this->em->getRepository('App\Model\Entity\Collectible')->find($this->getParameter('id'));
        $form->addText('name', 'název')->setValue($collectible->getName())->setRequired()->setAttribute('class', 'form-control');

        $form->addTextArea('description', 'popis')->setValue($collectible->getDescription())->setAttribute('class', 'form-control')->setRequired();

        $form->addTextArea('origin', 'původ')->setValue($collectible->getOrigin())->setAttribute('class', 'form-control')->setRequired();

        $categories = $this->em->getRepository('App\Model\Entity\Category')->findPairs('name', 'id');
        $form->addSelect('category', 'kategorie', $categories)->setValue($collectible->getCategory()->getId())->setAttribute('class', 'form-control');

        $form->addUpload('img', 'Upload your image')->setAttribute('class', 'form-control')->setRequired(false)->addCondition(Form::FILLED)->addRule(Form::IMAGE, 'Cover must be JPEG, PNG or GIF.');

        $form->addSubmit('send', 'Upravit předmět')->setAttribute('class', 'form-control')->setAttribute('id', 'submit_button')->setRequired();

        $form->onSuccess[] = $this->collectibleEditFormSubmitted;

        return $form;
    }

    public function collectibleEditFormSubmitted($form, $values)
    {
        if ($values['img']->isOk()) {
            //$id = $this->database->query("SHOW TABLE STATUS LIKE 'collectibles' ")->fetch()->Auto_increment;
            $file = $this->database->table('collectibles')->get($this->getParameter('id'))->picture;
            if (!(empty($file))) unlink(WWW_DIR . substr($file, 4));
            $filename = $this->getUser()->identity->data['username'] . $this->getParam("id");
            $targetPath = $this->presenter->basePath;

            $pripona = pathinfo($values['img']->getSanitizedName(), PATHINFO_EXTENSION);
            $cil = WWW_DIR . "/images/collectible/$filename.$pripona";
            $cil2 = $targetPath . "images/collectible/$filename.$pripona";
            $image = $values['img']->toImage();
            $image->resize(1280, 1240);
            $image->sharpen();
            $image->save($cil);
        }
        $this->database->table('collectibles')->get($this->getParameter('id'))->update(array(

            'name' => $values->name,
            'description' => $values->description,
            'user_id' => $this->getUser()->identity->id,
            'origin' => $values->origin,
        ));
        if (isset($cil2)) {
            $this->database->table('collectibles')->get($this->getParameter('id'))->update(array('picture' => $cil2,));
        }
        if (!empty($values->category)) {
            $this->database->table('collectibles')->get($this->getParameter('id'))->update(array('category_id' => $values->category));
        }
        $this->flashMessage('Předmět byl upraven.');
        $this->redirect('Collectible:', $this->getParameter('id'));

    }

    protected function createComponentTradeForm()
    {
        $form = new Form;
        $form->addProtection();

        $users = $this->database->table('users')->select('*')->fetchPairs('user_id', 'username');
        $form->addSelect('user', 'Uživatel', $users)->setAttribute('class', 'form-control');

        $form->addSubmit('send', 'Poslat nabídku')->setRequired()->setAttribute('class', 'form-control')->setAttribute('id', 'submit_button');

        $form->onSuccess[] = $this->tradeFormSubmitted;

        return $form;
    }

    public function tradeFormSubmitted($form, $values)
    {
        $collectible = $this->database->table('collectibles')->get($this->getParameter('id'));
        $category = $this->database->table('categories')->get($collectible["category_id"])->name;
        $message = "Uživatel " . $this->getUser()->identity->username . " Vám nabízí k výměně předmět " . $collectible->name . " z kategorie " . $category . " kliknutím na tuto zprávu se dostanete na detail nabídky.";
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

        $collectibles = $this->database->table('collectibles')->select('*')->where('user_id', $this->getUser()->id)->fetchPairs('collectible_id', 'name');
        $form->addSelect('collectible', 'Sběratelský předmět', $collectibles)->setAttribute('class', 'form-control');

        $form->addSubmit('send', 'Poslat nabídku')->setRequired()->setAttribute('class', 'form-control')->setAttribute('id', 'submit_button');

        $form->onSuccess[] = $this->confirmTradeFormSubmitted;

        return $form;
    }

    public function confirmTradeFormSubmitted($form, $values)
    {
        $collectible = $this->database->table('collectibles')->get($this->getParameter('id'));
        $category = $this->database->table('categories')->get($collectible["category_id"])->name;
        $message = "Uživatel " . $this->getUser()->identity->username . " Vám nabízí k výměně předmět " . $collectible->name . " z kategorie " . $category . " kliknutím na tuto zprávu se dostanete na detail nabídky.";
        $link = Nette\Utils\Html::el('a')->href($this->link('//Collectible:final', $this->getParameter('id'), $values->collectible))->setText($message);
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
        $form->addSubmit('send', 'Schválit nabídku')->setRequired()->setAttribute('class', 'form-control')->setAttribute('id', 'submit_button');

        $form->onSuccess[] = $this->finalFormSubmitted;

        return $form;
    }

    public function finalFormSubmitted($form, $values)
    {
        $id2 = $this->database->table('collectibles')->get($this->getParameter('id'))->user_id;
        $id = $this->database->table('collectibles')->get($this->getParameter('id2'))->user_id;

        $this->database->table('collectibles')->get($this->getParameter('id'))->update(array(
            'user_id' => $id,
        ));

        $this->database->table('collectibles')->get($this->getParameter('id2'))->update(array(
            'user_id' => $id2,
        ));

        $this->flashMessage('Předměty byly vyměněny.');
        $this->redirect('Homepage:');

    }

}
