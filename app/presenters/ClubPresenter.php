<?php

namespace App\Presenters;
use Nette\Application\UI\Form as Form;
use Nette;
use App\Model;
use Nette\Utils\Strings;
use Nette\Application\UI\Multiplier;


class ClubPresenter extends BasePresenter
{
  public $id,$page;
	public function renderDefault($page)
	{
 $this->template->topics=$this->database->query('SELECT title, picture, username, datetime, topics.topic_id,users.user_id FROM topics JOIN comments on topics.topic_id=comments.topic_id join users on comments.user_id=users.user_id')->fetchAll();
	}

  public function renderShow($id)
	{
   $this->template->title=$this->database->table('topics')->get($id)->title;
   $this->template->comments=$this->database->query('SELECT text, picture, username, datetime, comments.user_id FROM comments JOIN users on comments.user_id=users.user_id where topic_id=?',$id)->fetchAll();
	}
  protected function createComponentNewClubForm(){ 
    $form = new Form;
    $form->addProtection();
    $form->addText('title')->setAttribute('placeholder','Zadejte název klubu')->setAttribute('class','form-control')->addRule(Form::MIN_LENGTH, 'Minimálně %d znaků.', 5)->addRule(Form::MAX_LENGTH, 'Název je příliš dlouhý.', 40);
    $form->addTextArea('message')->setAttribute('placeholder','Zde zadejte úvodní příspěvek nového sběratelského předmětu, typicky specifikaci tématu.')->setAttribute('class','form-control');
    $form->addSubmit('send', 'Založit klub')->setAttribute('class','form-control')->setAttribute('id','submit_button');
    $form->onSuccess[] = $this->newClubFormSubmitted;
    $renderer = $form->getRenderer();
    $renderer->wrappers['controls']['container'] = NULL;
    $renderer->wrappers['pair']['container'] = NULL;
    $renderer->wrappers['label']['container'] = NULL;
    $renderer->wrappers['control']['container'] = NULL;
		return $form;    
  }
  
 public function newClubFormSubmitted($form,$values)
	{
  $id = $this->database->query("SHOW TABLE STATUS LIKE 'topics' ")->fetch()->Auto_increment;
  if((!empty($values->title))&&(!empty($values->message))){
        $this->database->table('topics')->insert(array(
        'title' => $values->title,
        ));
        
         $this->database->table('comments')->insert(array(
        'text' => $values->message,
        'topic_id' => $id,
        'user_id'  => $this->user->identity->id,
        ));        
                        $this->redirect('Club:show',$id);} 
  }
  
  protected function createComponentClubForm(){ 
    $form = new Form;
    $form->addProtection();
    $form->addText('text')->setAttribute('placeholder','Zde napište text vašeho příspěvku.')->setAttribute('class','form-control');
    $form->addSubmit('send', 'Odeslat příspěvek')->setAttribute('class','form-control')->setAttribute('id','submit_button');
    $form->onSuccess[] = $this->clubFormSubmitted;
    $renderer = $form->getRenderer();
    $renderer->wrappers['controls']['container'] = NULL;
    $renderer->wrappers['pair']['container'] = NULL;
    $renderer->wrappers['label']['container'] = NULL;
    $renderer->wrappers['control']['container'] = NULL;
		return $form;    
  }
  
 public function clubFormSubmitted($form,$values)
	{
  if((!empty($values->text))){

         $this->database->table('comments')->insert(array(
        'text' => $values->text,
        'topic_id' => $this->getParameter('id'),
        'user_id'  => $this->user->identity->id,
        ));        
                        $this->redirect('Club:show',$this->getParameter('id'));} 
  }
  
  
  	protected function createComponentRegisterForm()
{
    $form = new Form;
    $form->addProtection();
    $form->addText('firstName', 'Vaše křestní jméno')->setAttribute('class','form-control')
        ->setRequired();

    $form->addText('surname', 'Příjmení')->setAttribute('class','form-control')
        ->setRequired();

    $form->addText('username', 'username')->setAttribute('class','form-control')
    ->setRequired();

     $form->addText('email', 'Váš e-mail')->setAttribute('class','form-control')
    ->setRequired('Choose an e-mail')
    ->addRule(Form::EMAIL, 'Please fill in your valid adress')
    ->emptyValue = '@';

    $form->addPassword('password', 'heslo')->setAttribute('class','form-control')
    ->setRequired('Choose a password')
    ->addRule(Form::MIN_LENGTH, 'Heslo musí mít alespoň %d znaky', 6)
    ->addRule(Form::PATTERN, 'Musí obsahovat číslici', '.*[0-9].*');

    $form->addPassword('passwordVerify', 'heslo znovu')->setAttribute('class','form-control')
    ->addRule(Form::FILLED, 'Zadejte heslo ještě jednou pro kontrolu')
    ->addRule(Form::EQUAL, 'Zadané hesla se neshodují', $form['password']);

    $link = Nette\Utils\Html::el('a')->href($this->link('//Terms:default'))->target("_blank")->setText('Souhlasím s podmínkami');
    $label = Html::el()->setHtml($link);
    $form->addCheckbox('agree', $label)
    ->addRule(Form::EQUAL, 'Je potřeba souhlasit s podmínkami', TRUE);
    
/*  $link = Nette\Utils\Html::el('a')->href($this->link('//LostPassword:default'))->setText('Forggot your password?');
    $label = Html::el()->setHtml($link);
    $form->addLabel('lost', $label);  */





    $form->addSubmit('send', 'Registrovat se')->setAttribute('class','form-control')->setAttribute('id','submit_button');
    $form->onSuccess[] = $this->registerFormSubmitted;

   

    return $form;
}

public function registerFormSubmitted($form)
{
$values = $form->getValues();
    try{
    $token = Nette\Utils\Strings::random();
    $this->database->table('users')->insert(array(
        'firstName' => $values->firstName,
        'surname' => $values->surname,
        'email' => $values->email,
        'username' => $values->username,
        'confirmedEmail' =>$token,
        'password' => password_hash($values->password, PASSWORD_DEFAULT),

    ));
    $link = Nette\Utils\Html::el('a')->href($this->link('//Check:verify', $token))->setText('Potvrdit registraci');
    $mail = new Message;
    $mail->setFrom('lokaleat@gmail.com')
    ->addTo($values->email)
    ->setSubject('Vítej mezi sběrateli na iCollector')
    ->setHTMLBody('<style>.wrapper{width:100%;height:auto;}.container{max-width: 90%;margin:0 auto;}.mail_header{background-color:#5cb85c;padding: 0.5em;}.mail_body{text-align:center;margin-top: 1em;}.mail_body p{max-width: 80%; margin: 1em auto;}.large{font-size: 1.5em;margin-top: 2em;}.bottom{margin-top: 2em;}.confirm_button{background-color:#5cb85c; padding: 0.5em; margin: 150px auto 0;}.confirm_button a {text-decoration: none; color: #000;}.confirm_button:hover{opacity:0.9;}</style><div class="wrapper"><div class="container"><div class="mail_header">LokalEat</div><div class="mail_body"><p class="large">Ahoj ' . $values->firstName . ', Vítej na iCollector </p><p> Tvoje registrace byla úspěšná, prosím potvrď svůj e-mail kliknutím na odkaz níže. Team iCollector</p><p class="bottom"><span class="confirm_button">' . $link . '</span></p></div></div>');
    $mailer = new Nette\Mail\SmtpMailer(array(
        'host' => 'smtp.gmail.com',
        'username' => 'lokaleat@gmail.com',
        'password' => 'qwertz12',
        'secure' => 'ssl',
));
    $mailer->send($mail);

    $this->flashMessage('Děkujeme za registraci', 'success');
    $this->redirect('Homepage:'); }
    catch(\PDOException $e) {
                if(Strings::contains($e, 'username')) {$form['username']->addError('Username is already taken');}
                if(Strings::contains($e, 'email')) {$form['email']->addError('Email is already used');}
                else throw $e;
                
            }
}
  

}
