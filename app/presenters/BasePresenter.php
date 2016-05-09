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
  if ($this->user->isLoggedIn())
  if ($this->user->identity->confirmedEmail!=1) {
  $this->getUser()->logout();
  
  $this->flashMessage('aktivujte prosím svůj e-mail','danger');
  $this->redirect('Homepage:');
  }
  
 }
 
 protected function createComponentSignInForm()
	{
  $form = new Form;
  $form->addProtection();
	$form->addText('username', 'Username:')->setAttribute('class','form-control')
			->setRequired('Please enter your username.');

		$form->addPassword('password', 'heslo:')->setAttribute('class','form-control')
			->setRequired('Please enter your password.');

		$form->addCheckbox('remember', 'Zůstat přihlášen');

		$form->addSubmit('send', 'Přihlásit')->setAttribute('class','form-control')->setAttribute('id','submit_button');

		// call method signInFormSubmitted() on success
		$form->onSuccess[] = $this->signInFormSubmitted;
    $renderer = $form->getRenderer();
    
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
			$this->flashMessage($e->getMessage());
			return;
		}

		$this->redirect('Homepage:');
	}
	public function actionLogout()
	{

		$this->getUser()->logout();
		$this->flashMessage('You have been signed out.');
		$this->redirect('Homepage:');
	}
  
  protected function createComponentSearchForm()
	{
  $form = new Form;
  $form->addProtection();
	$form->addText('search')->setAttribute('class','form-control')->setAttribute('placeholder','kategorie');
		// call method signInFormSubmitted() on success
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

		$this->redirect('Category:',$values->search);
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
