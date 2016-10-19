<?php
namespace App\Presenters;

use App\Model\Entity\Collectible;
use App\Model\Entity\User;
use Nette\Application\UI\Form as Form;
use Nette;
use Nette\Utils\Html;
use Nette\Utils\Strings;
use Nette\Mail\Message;
use Nette\Mail\SendmailMailer;


class RegisterPresenter extends BasePresenter
{

    protected function createComponentRegisterForm()
    {
        $form = new Form;
        $form->addProtection();
        $form->addText('firstName', 'Vaše křestní jméno')->setAttribute('class', 'form-control')
            ->setRequired();

        $form->addText('surname', 'Příjmení')->setAttribute('class', 'form-control')
            ->setRequired();

        $form->addText('username', 'username')->setAttribute('class', 'form-control')
            ->setRequired();

        $form->addText('email', 'Váš e-mail')->setAttribute('class', 'form-control')
            ->setRequired('Choose an e-mail')
            ->addRule(Form::EMAIL, 'Please fill in your valid adress')
            ->emptyValue = '@';

        $form->addPassword('password', 'heslo')->setAttribute('class', 'form-control')->setAttribute('placeholder', 'Alespoň 6 znaků a 1 číslici.')
            ->setRequired('Choose a password')
            ->addRule(Form::MIN_LENGTH, 'Heslo musí mít alespoň %d znaky', 6)
            ->addRule(Form::PATTERN, 'Musí obsahovat číslici', '.*[0-9].*');

        $form->addPassword('passwordVerify', 'heslo znovu')->setAttribute('class', 'form-control')->setAttribute('placeholder', 'Zadejte heslo ještě jednou.')
            ->addRule(Form::FILLED, 'Zadejte heslo ještě jednou pro kontrolu')
            ->addRule(Form::EQUAL, 'Zadané hesla se neshodují', $form['password']);

        $link = Nette\Utils\Html::el('a')->href($this->link('//Terms:default'))->target("_blank")->setText('Souhlasím s podmínkami');
        $label = Html::el()->setHtml($link);
        $form->addCheckbox('agree', $label)
            ->addRule(Form::EQUAL, 'Je potřeba souhlasit s podmínkami', TRUE);

        /*  $link = Nette\Utils\Html::el('a')->href($this->link('//LostPassword:default'))->setText('Forggot your password?');
            $label = Html::el()->setHtml($link);
            $form->addLabel('lost', $label);  */


        $form->addSubmit('send', 'Registrovat se')->setAttribute('class', 'form-control')->setAttribute('id', 'submit_button');
        $form->onSuccess[] = $this->registerFormSubmitted;


        return $form;
    }

    public function registerFormSubmitted($form)
    {
        $values = $form->getValues();
        try {
            $token = Nette\Utils\Strings::random(15, '0-9a-zA-Z');
            $user = new User();
            $user->setName($values->firstName)->setSurname($values->surname)->setUsername($values->username)
            ->setPassword(Nette\Security\Passwords::hash($values->password))->setConfirmedEmail($token)->setEmail($values->email);
            $this->em->persist($user);
            $this->em->flush();
            $link = Nette\Utils\Html::el('a')->href($this->link('//Check:verify', $token))->setText('Potvrdit registraci');
            $mail = new Message;
            $mail->setFrom('postmaster@collectorsnest.eu')
                ->addTo($values->email)
                ->setSubject('Vítej mezi sběrateli na collectors\' nest')
                ->setHTMLBody('<style>.wrapper{width:100%;height:auto;}.container{max-width: 90%;margin:0 auto;}.mail_header{background-color:#5cb85c;padding: 0.5em;}.mail_body{text-align:center;margin-top: 1em;}.mail_body p{max-width: 80%; margin: 1em auto;}.large{font-size: 1.5em;margin-top: 2em;}.bottom{margin-top: 2em;}.confirm_button{background-color:#5cb85c; padding: 0.5em; margin: 150px auto 0;}.confirm_button a {text-decoration: none; color: #000;}.confirm_button:hover{opacity:0.9;}</style><div class="wrapper"><div class="container"><div class="mail_header">collectors\' nest</div><div class="mail_body"><p class="large">Ahoj ' . $values->username . ', Vítej na collectors\' nest </p><p> Tvoje registrace byla úspěšná, prosím potvrď svůj e-mail kliknutím na odkaz níže. Team collectors\' nest</p><p class="bottom"><span class="confirm_button">' . $link . '</span></p></div></div>');
            $this->mailer->send($mail);

            $this->flashMessage('Děkujeme za registraci. Nyní prosím přejděte do e-mailu pro potvrzení registrace. Může to trvat několik minut, případně být ve SPAMu dle Vašeho poskytovatele e-mailu.', 'success');

            $this->redirect('Homepage:');

        } catch (\PDOException $e) {
            if (Strings::contains($e, 'username')) {
                $form['username']->addError('Username is already taken');
            }
            if (Strings::contains($e, 'email')) {
                $form['email']->addError('Email is already used');
            }
            //else throw $e;

        }
    }


}
