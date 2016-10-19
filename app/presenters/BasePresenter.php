<?php
namespace App\Presenters;

use Nette\Application\UI\Form as Form;
use Nette;
use Nette\Utils\Html;
use Nette\Utils\Strings;
use Nette\Mail\Message;
use Nette\Mail\SendmailMailer;
use App\Model;
use Nette\Http\Request;
use Nette\Context;
use Nette\Http\FileUpload;
use Tomaj\Form\Renderer\BootstrapRenderer;
use Kdyby\Doctrine\EntityManager;
use Nette\Utils\Image;
use Nette\Utils\Finder;


/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{
    /** @var EntityManager @inject */
    public $em;

    protected $database;

    protected $collectibles;

    /** @var Nette\Mail\IMailer @inject */
    public $mailer;

    public function handleLoad($page)
    {
        if ($this->isAjax()) {
            $this->collectibles = $this->em->getRepository('App\Model\Entity\Collectible')->findBy([],['dateTimeAdded' => 'DESC'],15,$page*15);
            $this->payload->stopLoading = false;
            if (count($this->collectibles) != 15) {
                $this->payload->stopLoading = true;
            }

            if (count($this->collectibles)!=0){
                $this->redrawControl('collectibles');
            }
            else {
                $this->sendPayload();
            }

        }
    }
    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    public function getBasePath()
    {
        return $this->getHttpRequest()->getUrl()->getBasePath();
    }

    public function actionShow($id, $width = null)
    {

        foreach (Finder::findFiles($id . '.*')->in('../www/images/collectible') as $key => $file) {
            $path = $key; // $key je řetězec s názvem souboru včetně cesty
            //echo $file; // $file je objektem SplFileInfo

        }
        if (isset($path)) {
            $image = Image::fromFile($path);
            if (isset($width)) {
                $image->resize($width, NULL, Image::SHRINK_ONLY);
                $image->sharpen();
                $image->save("../www/images/collectible/$id-$width.jpg", 100, Image::JPEG);
            }
            $image->send();
        } else {
            if (isset($width)) {
                $image = Image::fromFile("../www/images/collectible/placeholder-$width.jpg");
                $image->send();
            }
            $nameAndWidth = explode('-', $id);
            return $this->actionShow($nameAndWidth[0], $nameAndWidth[1]);

        }
    }

    public function beforeRender()
    {

        if ($this->user->isLoggedIn()) {
            if ($this->user->identity->confirmedEmail != 1) {
                $this->getUser()->logout();

                $this->flashMessage('aktivujte prosím svůj e-mail', 'danger');
                $this->redirect('Homepage:');
            }
        }
    }

    protected function createComponentSignInForm()
    {
        $form = new Form;
        $form->elementPrototype->addAttributes(array('class' => 'ajax'));
        $form->addProtection();
        $form->addText('username', 'Username:')->setAttribute('class', 'form-control')
            ->setRequired('Please enter your username.');

        $form->addPassword('password', 'heslo:')->setAttribute('class', 'form-control')
            ->setRequired('Please enter your password.');

        $form->addCheckbox('remember', 'Zůstat přihlášen');

        $form->addSubmit('send', 'Přihlásit')->setAttribute('class', 'form-control')->setAttribute('id', 'submit_button');

        // call method signInFormSubmitted() on success
        $form->onSuccess[] = $this->signInFormSubmitted;
        $renderer = $form->getRenderer();
        $renderer->wrappers['error']['container'] = 'div class="alert alert-danger"';
        $renderer->wrappers['error']['item'] = 'p';

        return $form;
    }

    public function signInFormSubmitted($form)
    {
        $values = $form->getValues();

        if ($values->remember) {
            $this->getUser()->setExpiration('14 days', FALSE);
        } else {
            $this->getUser()->setExpiration('20 minutes', TRUE);
        }

        try {
            $this->getUser()->login($values->username, $values->password);
        } catch (Nette\Security\AuthenticationException $e) {
            if ($this->isAjax()) {
                /** va */
                $form->addError('Špatná kombinace jména a hesla');

                $this->redrawControl('sign');
            } else {
                $this->flashMessage($e->getMessage());
            }
            return;
        }
        if ($this->isAjax()) {
            $this->template->hide = 'true';
            $this->redrawControl('user_controls');
            $this->redrawControl('flashMessages');
        } else {
            $this->redirect('Homepage:');
        }
    }

    public function handleLogout()
    {

        $this->getUser()->logout();
        $this->flashMessage('Byl jste odhlášen.');
        if ($this->isAjax()) {
            $this->redrawControl('sign');
            $this->redrawControl('user_controls');
            $this->redrawControl('flashMessages');
        } else {
            $this->redirect('Homepage:');
        }
    }

    protected function createComponentSearchForm()
    {
        $form = new Form;
        $form->addProtection();
        $categories = $this->em->getRepository('App\Model\Entity\Category')->findPairs('name', 'id');
        if ($this->getParameter('category') != NULL) $form->addSelect('category', 'Hledat kategorie', $categories)->setAttribute('class', 'form-control')->setPrompt('Všechny kategorie')->setAttribute('onchange', "document.getElementById('frm-searchForm').submit();")->setValue($this->getParameter('category'));
        else $form->addSelect('category', 'Hledat kategorie', $categories)->setAttribute('class', 'form-control')->setPrompt('Všechny kategorie')->setAttribute('onchange', "document.getElementById('frm-searchForm').submit();");
        $form->onSuccess[] = $this->searchFormSubmitted;
        $renderer = $form->getRenderer();
        $renderer->wrappers['controls']['container'] = NULL;
        $renderer->wrappers['pair']['container'] = NULL;
        $renderer->wrappers['label']['container'] = NULL;
        $renderer->wrappers['control']['container'] = NULL;
        return $form;
    }

    public function searchFormSubmitted($form)
    {
        $values = $form->getValues();

        $this->redirect('Category:', $values->category);
    }
    /*
      protected function createComponentOptionForm()
        {
      $form = new Form;
      $form->addProtection();
      $options = array(
        'g' => 'galerie',
        's' => 'seznam',);
    $form->addRadioList('option','', $options)->setAttribute('class','form-control')->getSeparatorPrototype()->setName(NULL);


            // call method signInFormSubmitted() on success
            $form->onSuccess[] = $this->optionFormSubmitted;

            return $form;
        }

      public function optionFormSubmitted($form)
        {
            $values = $form->getValues();

            $this->redirect('Category:',$values->option);
        }
    */
}
