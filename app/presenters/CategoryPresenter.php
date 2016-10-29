<?php

namespace App\Presenters;

use Nette;
use App\Model;
use Nette\Application\UI\Form as Form;
use Nette\Utils\Image;


class CategoryPresenter extends BasePresenter
{
    public $category, $page, $collectibles;

    public function renderDefault($category = NULL)
    {
        if (!$category) {
            $this->redirect('Homepage:');
        } else {
            $this->category = $this->template->category = $this->em->getRepository('App\Model\Entity\Category')->find($category);
            $this->template->collectibles = $this->category->getCollectibles();
            $this->template->topics = $this->category->getTopics();
        }

    }

    protected function createComponentCategoryForm()
    {
        $form = new Form;
        $form->addProtection();
        $form->addText('name', 'Název kategorie')->setRequired()->setAttribute('class', 'form-control');

        $form->addTextArea('description', 'Popis kategorie')->setRequired()->setAttribute('class', 'form-control');
        $form->addUpload('icon', 'Vyberte ikonku pro Vaši novou kategorii')->setRequired(false)
            ->addCondition(Form::FILLED)
            ->addRule(Form::IMAGE, 'Musí být obrázek!');

        $form->addSubmit('send', 'nová kategorie')->setRequired()->setAttribute('class', 'form-control')->setAttribute('id', 'submit_button');

        $form->onSuccess[] = $this->categoryFormSubmitted;

        return $form;
    }

    public function categoryFormSubmitted($form, $values)
    {
        try {
            $category = new Model\Entity\Category();
            $category->setDescription($values->description)->setName($values->name);
            $this->em->persist($category);
            $this->em->flush();

            if ($values['icon']->isOk()) {
                /** @var Image $image */
                $image = $values['icon']->toImage();
                $image->resize(64, 64, Image::SHRINK_ONLY);
                $image->sharpen();
                $name = $category->getId();
                $image->save("../www/images/category/$name.png", 100, Image::PNG);
                $category->setIcon(1);
                $this->em->persist($category);
                $this->em->flush();
            }


            $this->flashMessage('Kategorie byla vytvořena, nyní vytvořte sběratelský klub pro Vaši novou kategorii ' . $category->getName());
            $this->redirect('Club:new');
        } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
            $this->flashMessage('Kategorie '.$category->getName().' již existuje');

        }
    }
}
