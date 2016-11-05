<?php

namespace App\Presenters;

use Nette\Application\UI\Form as Form;
use Nette;
use App\Model;
use Nette\Application\UI\Multiplier;
use Nette\Utils\Image;
use Nette\Mail\Message;

class CollectiblePresenter extends BasePresenter
{
    public $id;

    /** @persistent */
    public $page;


    public function renderDefault($id = null)
    {
        if (!empty($id)) {
            $this->template->collectible = $this->em->getRepository('App\Model\Entity\Collectible')->find($id);
        } else {
            $this->redirect('Homepage:');
        }
    }

    public function renderEdit($id)
    {
        if (!($this->user->isLoggedIn())) {
            $this->redirect('Homepage:');
        }

        $collectible = $this->em->getRepository('App\Model\Entity\Collectible')->find($id);
        if ($collectible) {
            if ($collectible->getUser()->getId() === $this->user->getId()) {

            } else {
                $this->redirect('Homepage:');
            }
        } else {
            $this->redirect('Homepage:');
        }
    }

    public function actionTrade($id)
    {
        if (!($this->user->isLoggedIn())) {
            $this->redirect('Homepage:');
        }
        /** @var Model\Entity\Collectible $collectible */
        $collectible = $this->em->getRepository('App\Model\Entity\Collectible')->find($id);
        if ($collectible) {
            if ($collectible->getUser()->getId() === $this->user->getId()) {
                $collectible->setTradeable(true);
                $this->em->persist($collectible);
                $this->em->flush();
                $this->flashMessage($collectible->getName().' byl vystaven k výměně.');
            }
        }
        $this->redirect('Collectible:', $id);
    }

    public function actionCancel($id)
    {
        if (!($this->user->isLoggedIn())) {
            $this->redirect('Homepage:');
        }
        /** @var Model\Entity\Collectible $collectible */
        $collectible = $this->em->getRepository('App\Model\Entity\Collectible')->find($id);
        if ($collectible) {
            if ($collectible->getUser()->getId() === $this->user->getId()) {
                $collectible->setTradeable(false);
                $this->em->persist($collectible);
                $this->em->flush();
                $this->flashMessage($collectible->getName().' již není k výměně.');
            }
        }
        $this->redirect('Collectible:', $id);
    }

    public function actionOffer($id)
    {
        if (!($this->user->isLoggedIn())) {
            $this->redirect('Homepage:');
        }

        /** @var Model\Entity\Collectible $collectible */
        $collectible = $this->em->getRepository('App\Model\Entity\Collectible')->find($id);
        if (!$collectible || !$collectible->getTradeable()) {
            $this->redirect('Homepage:');
        }
        /** @var Model\Entity\User $user */
        $user = $this->em->find('App\Model\Entity\User', $this->getUser()->id);
        $collectibles = $user->getCollectibles();
        if (count($collectibles) < 1) {
            $this->flashMessage('Nemáte žádné předměty k nabídnutí.');
            $this->redirect('Homepage:');
        }

    }

    protected function createComponentOfferForm()
    {
        $form = new Form;
        $form->addProtection();
        $collectibles = $this->em->getRepository('App\Model\Entity\Collectible')->findPairs(['user' => $this->getUser()->id], 'name', 'id');
        $form->addSelect('collectible', 'Vybrat předmět k nabídnutí', $collectibles)->setAttribute('class', 'form-control');
        $form->addSubmit('send', 'Potvrdit nabídku')->setRequired('Toto pole je povinné')->setAttribute('class', 'form-control')->setAttribute('id', 'submit_button');

        $form->onSuccess[] = $this->offerFormSubmitted;

        return $form;
    }

    public function offerFormSubmitted($form, $values)
    {
        $trade = new Model\Entity\Trade();
        /** @var Model\Entity\Collectible $collectibleAsked */
        $collectibleAsked = $this->em->getRepository('App\Model\Entity\Collectible')->find($this->getParameter('id'));
        /** @var Model\Entity\Collectible $collectibleOffered */
        $collectibleOffered = $this->em->getRepository('App\Model\Entity\Collectible')->find($values->collectible);
        $trade->setCollectibleAsked($collectibleAsked)->setCollectibleOffered($collectibleOffered)->setStatus('pending')->setSender($collectibleOffered->getUser())->setReceiver($collectibleAsked->getUser());
        $this->em->persist($trade);
        $this->em->flush();
        $link = Nette\Utils\Html::el('a')->href($this->link('//Trade:default', $trade->getId()))->setText('Zobrazit žádost o výměnu');
        $mail = new Message;
        $mail->setFrom('postmaster@collectorsnest.eu')
            ->addTo($collectibleAsked->getUser()->getEmail())
            ->setSubject('Nová nabídka výměny na collectors\' nest')
            ->setHTMLBody('<style>.wrapper{width:100%;height:auto;}.container{max-width: 90%;margin:0 auto;}.mail_header{background-color:#5cb85c;padding: 0.5em;}.mail_body{text-align:center;margin-top: 1em;}.mail_body p{max-width: 80%; margin: 1em auto;}.large{font-size: 1.5em;margin-top: 2em;}.bottom{margin-top: 2em;}.confirm_button{background-color:#5cb85c; padding: 0.5em; margin: 150px auto 0;}.confirm_button a {text-decoration: none; color: #000;}.confirm_button:hover{opacity:0.9;}</style><div class="wrapper"><div class="container"><div class="mail_header">The collectors\' nest</div><div class="mail_body"><p class="large">Ahoj ' . $collectibleAsked->getUser()->getUsername() . ', máte novou nabídku výměny předmětů. </p><p> Detail nabídky zobrazíte kliknutím na odkaz níže. Team collectors\' nest</p><p class="bottom"><span class="confirm_button">' . $link . '</span></p></div></div>');
        $this->mailer->send($mail);

        $this->flashMessage('Nabídka vytvořena a majitel předmětu o tom byl informován e-mailem.');
        $this->redirect('User:', $this->user->id);

    }

    protected function createComponentCollectibleForm()
    {
        $form = new Form;
        $form->addProtection();
        $form->addText('name', 'Název předmětu')->setRequired('Toto pole je povinné')->setAttribute('class', 'form-control')->addRule(Form::MIN_LENGTH, 'Minimálně %d znaky.', 3);

        $form->addTextArea('description', 'Popis předmětu')->setRequired('Toto pole je povinné')->setAttribute('class', 'form-control')->addRule(Form::MIN_LENGTH, 'Minimálně %d znaků.', 40);

        $form->addTextArea('origin', 'Původ předmětu')->setRequired('Toto pole je povinné')->setAttribute('class', 'form-control')->addRule(Form::MIN_LENGTH, 'Minimálně %d znaků.', 5);

        $categories = $this->em->getRepository('App\Model\Entity\Category')->findPairs('name', 'id');
        $form->addSelect('category', 'Kategorie', $categories)->setAttribute('class', 'form-control');


        $form->addMultiUpload('imgs', 'Fotky předmětu')->setRequired('Toto pole je povinné')->addRule(Form::MAX_FILE_SIZE, 'Maximální velikost obrázku je 4 MB.', 4 * 1024 * 1024 /* v bytech */)
            ->addRule(Form::IMAGE, 'Pouze obrázky!')
            ->addRule(Form::MAX_LENGTH, 'Maximální počet obrázků je 5.', 5)
            ->setAttribute('class', 'form-control');


        $form->addSubmit('send', 'nový předmět')->setRequired('Toto pole je povinné')->setAttribute('class', 'form-control')->setAttribute('id', 'submit_button');


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
        $form->addText('name', 'název')->setValue($collectible->getName())->setRequired('Toto pole je povinné')->setAttribute('class', 'form-control')->addRule(Form::MIN_LENGTH, 'Minimálně %d znaky.', 3);

        $form->addTextArea('description', 'popis')->setValue($collectible->getDescription())->setAttribute('class', 'form-control')->setRequired('Toto pole je povinné')->addRule(Form::MIN_LENGTH, 'Minimálně %d znaků.', 40);

        $form->addTextArea('origin', 'původ')->setValue($collectible->getOrigin())->setAttribute('class', 'form-control')->setRequired('Toto pole je povinné')->addRule(Form::MIN_LENGTH, 'Minimálně %d znaků.', 5);

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
        if (!empty($values['imgs'])) {
            $newImageCount = count($values['imgs']);
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
