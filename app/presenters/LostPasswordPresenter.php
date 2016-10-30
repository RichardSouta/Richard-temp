<?php
namespace App\Presenters;

use App\Model\Entity\User;
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
        $user = $this->em->getRepository('App\Model\Entity\User')->findBySecurity($token);
        if ($user) {
            $this->flashMessage('Z bezpečnostních důvodů jsi byl přesměrován, prosím zkus to znovu.');
            $this->redirect('LostPassword:');
        }
    }

    protected function createComponentEmailForm()
    {
        $form = new Form;


        $form->addText('email')->setAttribute('class', 'form-control')
            ->setRequired('Zadejte e-mail')
            ->addRule(Form::EMAIL, 'Prosím zadejte svojí e-mail adresu.')
            ->emptyValue = '@';

        $form->addSubmit('send', 'Potvrďte svůj e-mail')->setAttribute('class', 'form-control')->setAttribute('id', 'submit_button');
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
        /** @var User $user */
        $user = $this->em->getRepository('App\Model\Entity\User')->findByEmail($values->email);
        $user->setSecurity($token);
        $this->em->persist($user);
        $this->em->flush();

        if (!(empty($user))) {
            $link = Nette\Utils\Html::el('a')->href($this->link('//LostPassword:new', $token))->setText('Změnit heslo');
            $mail = new Message;
            $mail->setFrom('postmaster@collectorsnest.eu')
                ->addTo($values->email)
                ->setSubject('Obnovit heslo')
                ->setHTMLBody('<style>.wrapper{width:100%;height:auto;}.container{max-width: 90%;margin:0 auto;}.mail_header{background-color:#5cb85c;padding: 0.5em;}.mail_body{text-align:center;margin-top: 1em;}.mail_body p{max-width: 80%; margin: 1em auto;}.large{font-size: 1.5em;margin-top: 2em;}.bottom{margin-top: 2em;}.confirm_button{background-color:#5cb85c; padding: 0.5em; margin: 150px auto 0;}.confirm_button a {text-decoration: none; color: #000;}.confirm_button:hover{opacity:0.9;}</style><div class="wrapper"><div class="container"><div class="mail_header">Icollector</div><div class="mail_body"><p class="large">Ahoj ' . $user->getUsername() . ',</p><p>Pro změnu hesla klikni na tlačítko níže.</p><p>V případě, že jsi o ní nezažádal, ignoruj prosím tuto zprávu.</p><p>Tvůj team collectors\' nest</p><p class="bottom"><span class="confirm_button">' . $link . '</span></p></div></div>');
            $mailer = new SendmailMailer;
            $mailer->send($mail);

            $this->flashMessage('E-mail byl odeslán.', 'success');
            $this->redirect('this');
        } else {
            $this->flashMessage('E-mail nebyl nalezen.', 'warning');
            $this->redirect('this');
        }
    }

    protected function createComponentNewPasswordForm()
    {
        $form = new Form;

        $form->addPassword('password', 'Vaše heslo')->setAttribute('class', 'form-control')
            ->setRequired('Choose a password')
            ->addRule(Form::MIN_LENGTH, 'Heslo musí mít alespoň %d znaky', 6)
            ->addRule(Form::PATTERN, 'Musí obsahovat číslici', '.*[0-9].*');

        $form->addPassword('passwordVerify', 'Vaše heslo podruhé')->setAttribute('class', 'form-control')
            ->addRule(Form::FILLED, 'Zadejte heslo ještě jednou pro kontrolu')
            ->addRule(Form::EQUAL, 'Zadané hesla se neshodují', $form['password']);

        $form->addSubmit('send', 'Změnit heslo')->setAttribute('class', 'form-control')->setAttribute('id', 'submit_button');
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
        try {
            /** @var User $user */
            $user = $this->em->getRepository('App\Model\Entity\User')->findBySecurity($this->getParam("token"));
            $user->setSecurity(null)->setPassword(password_hash($values->password, PASSWORD_DEFAULT));
            $this->em->persist($user);
            $this->em->flush();
            $this->flashMessage('Vaše heslo bylo úspěšně změněno.', 'success');
        } catch (\Exception $e) {
            $this->flashMessage('Vaše heslo nebylo změněno, zkuste to znovu.', 'warning');
        }
        finally
        {
            $this->redirect('Homepage:');
        }
    }


}