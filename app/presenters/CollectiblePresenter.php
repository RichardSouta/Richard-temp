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


    public function renderDefault($id = null)
    {
        if (!empty($id)) $collectible = $this->template->collectible = $this->em->getRepository('App\Model\Entity\Collectible')->find($id);
        else $this->redirect('Homepage:');
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
        $collectible = $this->template->collectible = $this->em->getRepository('App\Model\Entity\Collectible')->find($id);
        $collectible->setTradeable(true);
        $this->em->persist($collectible);
        $this->em->flush();
        $this->flashMessage('Váš předmět byl vystaven k výměně.');
        $this->redirect('User:', $this->user->id);


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

        $form->addTextArea('description', 'popis')->setValue($collectible->getDescription())->setAttribute('class', 'form-control')->setRequired('Toto pole je povinné');

        $form->addTextArea('origin', 'původ')->setValue($collectible->getOrigin())->setAttribute('class', 'form-control')->setRequired('Toto pole je povinné');

        $categories = $this->em->getRepository('App\Model\Entity\Category')->findPairs('name', 'id');
        $form->addSelect('category', 'kategorie', $categories)->setValue($collectible->getCategory()->getId())->setAttribute('class', 'form-control');

        $form->addMultiUpload('imgs', 'Fotky předmětu')->addRule(Form::MAX_FILE_SIZE, 'Maximální velikost obrázku je 4 MB.', 4 * 1024 * 1024 /* v bytech */)
            ->addRule(Form::IMAGE, 'Pouze obrázky!')
            ->addRule(Form::MAX_LENGTH, 'Maximální počet obrázků je 5.', 5)
            ->setAttribute('class', 'form-control');
        //$form['imgs']->addError('Pokud nahrajete nové fotky, staré se mohou přepsat, limit je 5.');

        $form->addSubmit('send', 'Upravit předmět')->setAttribute('class', 'form-control')->setAttribute('id', 'submit_button')->setRequired('Toto pole je povinné');

        $form->onSuccess[] = $this->collectibleEditFormSubmitted;

        return $form;
    }

    public function collectibleEditFormSubmitted($form, $values)
    {
        /** @var Model\Entity\Collectible $collectible */
        $collectible = $this->template->collectible = $this->em->getRepository('App\Model\Entity\Collectible')->find($this->getParameter('id'));
        $newImageCount = count($values['imgs']);
        /*$imageCount = count($collectible->getImages());
        $toReplaceCount = $imageCount + $newImageCount - 5;
        if ($toReplaceCount>0) {
            $this->flashMessage($toReplaceCount.' obrázků bylo nahrazeno.');
        }
        $originalImages = $collectible->getImages();*/
        $collectible->emptyImages();
        $collectible->setImages($newImageCount);
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

        if (!empty($values->name)) {
            $collectible->setName($values->name);
        }

        if (!empty($values->description)) {
            $collectible->setDescription($values->description);
        }

        if (!empty($values->origin)) {
            $collectible->setOrigin($values->origin);
        }

        if (!empty($values->category)) {
            $category = $this->em->find('App\Model\Entity\Category', $values->category);
            $collectible->setCategory($category);
        }

        $this->em->persist($collectible);
        $this->em->flush();
        $this->flashMessage('Předmět byl upraven.');
        $this->redirect('Collectible:', $this->getParameter('id'));
    }


}
