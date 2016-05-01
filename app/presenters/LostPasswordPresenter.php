<?php
namespace App\Presenters;
use Nette\Application\UI\Form as Form;
use Nette;
use Nette\Utils\Html;
use Nette\Utils\Strings;
use Nette\Mail\Message;
use Nette\Mail\SendmailMailer;
class LostPasswordPresenter extends BasePresenter
{

   public function renderNew($token)
   {
     if($this->database->table('users')->select('*')->where("security = ?",$token)->count()==0)
     {
     $this->flashMessage('You have been redirected for security reasons, please try again.');
     $this->redirect('LostPassword:');
     }
   } 

   protected function createComponentEmailForm()
{
    $form = new Form;


     $form->addText('email', 'Your e-mail')
    ->setRequired('Choose an e-mail')
    ->addRule(Form::EMAIL, 'Please fill in your valid adress')
    ->emptyValue = '@';

    $form->addSubmit('send', 'Confirm your e-mail');
    $form->onSuccess[] = $this->emailFormSubmitted;

    $renderer = $form->getRenderer();
    $renderer->wrappers['controls']['container'] = NULL;
    $renderer->wrappers['pair']['container'] = NULL;
    $renderer->wrappers['label']['container'] = NULL;
    $renderer->wrappers['control']['container'] = NULL;

    return $form;
}

public function emailFormSubmitted($form)
{
    $values = $form->getValues();
    $token = Nette\Utils\Strings::random();
    $this->database->table('users')->where("email = ?",$values->email)->update(array('security' => $token));    
    $data=$this->database->table('users')->select('*')->where("email = ?",$values->email)->fetch();
    $link = Nette\Utils\Html::el('a')->href($this->link('//LostPassword:new', $token))->setText('Change your password');
    $mail = new Message;
    $mail->setFrom('lokaleat@gmail.com')
    ->addTo($values->email)
    ->setSubject('Renew your password')
    ->setHTMLBody('<style>.wrapper{width:100%;height:auto;}.container{max-width: 90%;margin:0 auto;}.mail_header{background-color:#fcdb00;padding: 0.5em;}.mail_body{text-align:center;margin-top: 1em;}.mail_body p{max-width: 80%; margin: 1em auto;}.large{font-size: 1.5em;margin-top: 2em;}.bottom{margin-top: 2em;}.confirm_button{background-color:#fcdb00; padding: 0.5em; margin: 150px auto 0;}.confirm_button a {text-decoration: none; color: #000;}.confirm_button:hover{opacity:0.9;}</style><div class="wrapper"><div class="container"><div class="mail_header">LokalEat</div><div class="mail_body"><p class="large">Hello ' . $data->firstName . ', Welcome to LokalEat</p><p>Your registration was successful, please confirm your email address by clicking the link below and activate your account. Make sure to let us know at support@lokaleat.com if you come across any issues during registration or if you come across any other questions.</p><p class="bottom"><span class="confirm_button">' . $link . '</span></p></div></div>');
    $mailer = new Nette\Mail\SmtpMailer(array(
        'host' => 'smtp.gmail.com',
        'username' => 'lokaleat@gmail.com',
        'password' => 'qwertz12',
        'secure' => 'ssl',
    ));
    $mailer->send($mail);

    $this->flashMessage('Email has been sent.', 'success');
    $this->redirect('this');
    
}

   protected function createComponentNewPasswordForm()
{
    $form = new Form;

    $form->addPassword('password', 'Your password')
    ->setRequired('Choose a password')
    ->addRule(Form::MIN_LENGTH, 'Heslo musí mít alespoň %d znaky', 6)
    ->addRule(Form::PATTERN, 'Musí obsahovat číslici', '.*[0-9].*');

    $form->addPassword('passwordVerify', 'Your password second time')
    ->addRule(Form::FILLED, 'Zadejte heslo ještě jednou pro kontrolu')
    ->addRule(Form::EQUAL, 'Zadané hesla se neshodují', $form['password']);

    $form->addSubmit('send', 'Change password');
    $form->onSuccess[] = $this->newPasswordFormSubmitted;

    $renderer = $form->getRenderer();
    $renderer->wrappers['controls']['container'] = NULL;
    $renderer->wrappers['pair']['container'] = NULL;
    $renderer->wrappers['label']['container'] = NULL;
    $renderer->wrappers['control']['container'] = NULL;

    return $form;
}

public function newPasswordFormSubmitted($form)
{
$values = $form->getValues();
    try{
        $this->database->table('users')->where("security = ?",$this->getParam("token"))->update(array('security' => NULL, 'password' => password_hash($values->password, PASSWORD_DEFAULT)));

    $this->flashMessage('Your password has been changed', 'success');
    $this->redirect('Homepage:'); }
    catch(\PDOException $e) {
                throw $e;
                
            }
}

    
}
?>