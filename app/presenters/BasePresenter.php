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
  
  
 }
 
 protected function createComponentSignInForm()
	{
  $form = new Form;
  $form->addProtection();
	$form->addText('username', 'Username:')
			->setRequired('Please enter your username.');

		$form->addPassword('password', 'heslo:')
			->setRequired('Please enter your password.');

		$form->addCheckbox('remember', 'Zůstat přihlášen');

		$form->addSubmit('send', 'Přihlásit');

		// call method signInFormSubmitted() on success
		$form->onSuccess[] = $this->signInFormSubmitted;

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
 
}
