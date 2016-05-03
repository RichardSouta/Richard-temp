<?php

namespace App\Presenters;
use Nette\Application\UI\Form as Form;
use Nette;
use App\Model;
use Nette\Utils\Strings;


class UserPresenter extends BasePresenter
{
  public $id, $page;
	public function renderDefault($id,$page=1)
	{
  
	    $collectibles=$this->database->table('collectibles')->select('*')->where("user_id",$id)->limit(27)->fetchAll();      
      $paginator = new Nette\Utils\Paginator;
      $paginator->setItemCount(count($collectibles)); // celkový počet položek (např. článků)
      $paginator->setItemsPerPage(9); // počet položek na stránce
      $paginator->setPage($page); // číslo aktuální stránky, číslováno od 1
      $this->template->paginator=$paginator;
      $this->template->collectibles=array_slice($collectibles, $paginator->getOffset(),$paginator->getLength());
	}
  
     protected function createComponentProfileForm()
	{
    $form = new Form;
    $form->addProtection();
    $form->addUpload('avatar', 'Avatar:')
    ->setRequired(false)
    ->addCondition(Form::FILLED)
    ->addRule(Form::IMAGE, 'Avatar musí být JPEG, PNG nebo GIF.');
     $form->addText('username', 'Your username')->setValue($this->getUser()->getIdentity()->username)
    ->setRequired();

     $form->addText('email', 'Your e-mail')->setValue($this->getUser()->getIdentity()->email)
    ->setRequired()
    ->addRule(Form::EMAIL, 'Please fill in your valid adress')
    ->emptyValue = '@';

    $form->addText('description', 'Change description')->setValue($this->getUser()->getIdentity()->description);

    $form->addPassword('password', 'Your password')
    ->setRequired(false)
    ->addCondition(Form::FILLED)
    ->addRule(Form::MIN_LENGTH, 'Heslo musí mít alespoň %d znaky', 6)
    ->addRule(Form::PATTERN, 'Musí obsahovat číslici', '.*[0-9].*');

    $form->addPassword('passwordVerify', 'Your password second time')
    ->setRequired(false)
    ->addCondition(Form::FILLED)
    ->addRule(Form::EQUAL, 'Zadané hesla se neshodují', $form['password']);

    $form->addText('phone', 'Add your phone number')->setValue($this->getUser()->getIdentity()->phone);

    $form->addSubmit('send', 'Edit your profile');

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
    $cil=WWW_DIR."/images/user/$filename.$pripona";
    $cil2=$targetPath."images/user/$filename.$pripona";
    $values['avatar']->move($cil);
    }


    try{
		$this->database->table('users')->get($this->getUser()->id)->update(array(
        'email' => $values->email,
        'username' => $values->username,
        
    ));
    $this->getUser()->getIdentity()->email=$values->email;
    $this->getUser()->getIdentity()->username=$values->username;
    
     if(isset($cil2)){	$this->database->table('users')->get($this->getUser()->id)->update(array('picture' => $cil2,)); $this->getUser()->getIdentity()->picture=$cil2;} 
     if(!empty($values->password)){	$this->database->table('users')->get($this->getUser()->id)->update(array('password' => password_hash($values->password, PASSWORD_DEFAULT)));}
     if(!empty($values->phone)) {	$this->database->table('users')->get($this->getUser()->id)->update(array('phone' => $values->phone)); $this->getUser()->getIdentity()->phone=$values->phone;}
     if(!empty($values->description)) {	$this->database->table('users')->get($this->getUser()->id)->update(array('description' => $values->description)); $this->getUser()->getIdentity()->description=$values->description;} 
     }

	//	$this->redirect('Homepage:');}
    catch(\PDOException $e) {
                if(Strings::contains($e, 'username')) {$form['username']->addError('Username is already taken');}
                if(Strings::contains($e, 'email')) {$form['email']->addError('Email is already used');}
                else {
                    throw $e;
                }
            }
	}

}
