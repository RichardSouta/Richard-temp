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


/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{
 protected $database; 
	public function __construct(Nette\Database\Context $database)
	{
		$this->database = $database;
	}
    public function getBasePath()
{  
  return $this->getHttpRequest()->getUrl()->getBasePath();
}
  
  public function beforeRender() 
 { 

  if ($this->user->isLoggedIn()){
      if ($this->user->identity->confirmedEmail!=1) {
      $this->getUser()->logout();
      
      $this->flashMessage('aktivujte prosím svůj e-mail','danger');
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
		    if($this->isAjax()){
                /** va */
		    $form->addError('Špatná kombinace jména a hesla');

            $this->redrawControl('sign');}
		    else {
			$this->flashMessage($e->getMessage());}
			return;
		}
        if($this->isAjax()){
            $this->template->hide = 'true';
            $this->redrawControl('user_controls');
            $this->redrawControl('flashMessages');
        }
        else {
            $this->redirect('Homepage:');
        }
	}
	public function handleLogout()
	{

		$this->getUser()->logout();
		$this->flashMessage('Byl jste odhlášen.');
        if($this->isAjax()){
            $this->redrawControl('sign');
            $this->redrawControl('user_controls');
            $this->redrawControl('flashMessages');
        }
        else {
            $this->redirect('Homepage:');
        }
	}
  
  protected function createComponentSearchForm()
	{
  $form = new Form;
  $form->addProtection();
	  $categories=$this->database->table('categories')->select('*')->where('category_id !=',1)->fetchPairs('category_id','name');
    if($this->getParameter('category')!=NULL) $form->addSelect('category','Hledat kategorie',$categories)->setAttribute('class','form-control')->setPrompt('Všechny kategorie')->setAttribute('onchange',"document.getElementById('frm-searchForm').submit();")->setValue($this->getParameter('category'));
    else $form->addSelect('category','Hledat kategorie',$categories)->setAttribute('class','form-control')->setPrompt('Všechny kategorie')->setAttribute('onchange',"document.getElementById('frm-searchForm').submit();");
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

		$this->redirect('Category:',$values->category);
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
