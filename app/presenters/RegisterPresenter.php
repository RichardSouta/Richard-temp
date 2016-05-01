<?php

namespace App\Presenters;
use Nette\Application\UI\Form as Form;
use Nette;
use Nette\Utils\Html;


class RegisterPresenter extends BasePresenter
{
	
	protected function createComponentRegisterForm()
{
    $form = new Form;
    $form->addProtection();
    $form->addText('firstName', 'Tell us your first name please')
        ->setRequired();

    $form->addText('surname', 'And your surname')
        ->setRequired();

    $form->addText('username', 'Your username')
    ->setRequired();

     $form->addText('email', 'Your e-mail')
    ->setRequired('Choose an e-mail')
    ->addRule(Form::EMAIL, 'Please fill in your valid adress')
    ->emptyValue = '@';

    $form->addPassword('password', 'Your password')
    ->setRequired('Choose a password')
    ->addRule(Form::MIN_LENGTH, 'Heslo musí mít alespoň %d znaky', 6)
    ->addRule(Form::PATTERN, 'Musí obsahovat číslici', '.*[0-9].*');

    $form->addPassword('passwordVerify', 'Your password second time')
    ->addRule(Form::FILLED, 'Zadejte heslo ještě jednou pro kontrolu')
    ->addRule(Form::EQUAL, 'Zadané hesla se neshodují', $form['password']);

    $link = Nette\Utils\Html::el('a')->href($this->link('//Terms:default'))->target("_blank")->setText('I agree with terms');
    $label = Html::el()->setHtml($link);
    $form->addCheckbox('agree', $label)
    ->addRule(Form::EQUAL, 'Je potřeba souhlasit s podmínkami', TRUE);
    
/*  $link = Nette\Utils\Html::el('a')->href($this->link('//LostPassword:default'))->setText('Forggot your password?');
    $label = Html::el()->setHtml($link);
    $form->addLabel('lost', $label);  */





    $form->addSubmit('send', 'Registrovat se');
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
    $link = Nette\Utils\Html::el('a')->href($this->link('//Check:verify', $token))->setText('Confirm registration');
    $mail = new Message;
    $mail->setFrom('lokaleat@gmail.com')
    ->addTo($values->email)
    ->setSubject('Welcome to LokalEat')
    ->setHTMLBody('<style>.wrapper{width:100%;height:auto;}.container{max-width: 90%;margin:0 auto;}.mail_header{background-color:#fcdb00;padding: 0.5em;}.mail_body{text-align:center;margin-top: 1em;}.mail_body p{max-width: 80%; margin: 1em auto;}.large{font-size: 1.5em;margin-top: 2em;}.bottom{margin-top: 2em;}.confirm_button{background-color:#fcdb00; padding: 0.5em; margin: 150px auto 0;}.confirm_button a {text-decoration: none; color: #000;}.confirm_button:hover{opacity:0.9;}</style><div class="wrapper"><div class="container"><div class="mail_header">LokalEat</div><div class="mail_body"><p class="large">Hello ' . $values->firstName . ', Welcome to LokalEat</p><p>Your registration was successful, please confirm your email address by clicking the link below and activate your account. Make sure to let us know at support@lokaleat.com if you come across any issues during registration or if you come across any other questions.</p><p class="bottom"><span class="confirm_button">' . $link . '</span></p></div></div>');
    $mailer = new Nette\Mail\SmtpMailer(array(
        'host' => 'smtp.gmail.com',
        'username' => 'lokaleat@gmail.com',
        'password' => 'qwertz12',
        'secure' => 'ssl',
));
    $mailer->send($mail);

    $this->flashMessage('Děkujeme za registraci', 'success');
    $this->redirect('this'); }
    catch(\PDOException $e) {
                if(Strings::contains($e, 'username')) {$form['username']->addError('Username is already taken');}
                if(Strings::contains($e, 'email')) {$form['email']->addError('Email is already used');}
                else throw $e;
                
            }
}

}
