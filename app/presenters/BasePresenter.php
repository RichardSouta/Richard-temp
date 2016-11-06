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
use Nette\Application\UI\Multiplier;


/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{
    /** @var EntityManager @inject */
    public $em;

    protected $collectibles;

    protected $facebook;

    /** @var Nette\Mail\IMailer @inject */
    public $mailer;

    public function handleLoad($page)
    {
        if ($this->isAjax()) {
            $this->collectibles = $this->em->getRepository('App\Model\Entity\Collectible')->findBy([], ['dateTimeAdded' => 'DESC'], 15, $page * 15);
            $this->payload->stopLoading = false;
            if (count($this->collectibles) != 15) {
                $this->payload->stopLoading = true;
            }

            if (count($this->collectibles) != 0) {
                $this->redrawControl('collectibles');
            } else {
                $this->sendPayload();
            }

        }
    }

    public function __construct(\Kdyby\Facebook\Facebook $facebook)
    {
        $this->facebook = $facebook;
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
        if (!empty($path)) {
            $image = Image::fromFile($path);
            if (!empty($width)) {
                $image->resize($width, NULL, Image::SHRINK_ONLY);
                $image->sharpen();
                $image->save("../www/images/collectible/$id-$width.jpg", 100, Image::JPEG);
            }
            $image->send();
        } else {
            if (!empty($width)) {
                $image = Image::fromFile("../www/images/collectible/placeholder-$width.jpg");
                $image->send();
            }
            $nameAndWidth = explode('-', $id);
            return $this->actionShow($nameAndWidth[0], $nameAndWidth[1]);

        }
    }

    public function actionShowPicture($id, $width)
    {

        foreach (Finder::findFiles($id . ".*")->in('../www/images/user') as $key => $file) {
            $path = $key; // $key je řetězec s názvem souboru včetně cesty
            //echo $file; // $file je objektem SplFileInfo

        }
        if (!empty($path)) {
            $image = Image::fromFile($path);
            if (!empty($width)) {
                $image->resize($width, NULL, Image::SHRINK_ONLY);
                $image->sharpen();
                $image->save("../www/images/user/$id-$width.jpg", 100, Image::JPEG);
            }
            $image->send();
        } else {
            if (!empty($width)) {
                $image = Image::fromFile("../www/images/collectible/placeholder-$width.jpg");
                $image->send();
            }
            $nameAndWidth = explode('-', $id);
            return $this->actionShowPicture($nameAndWidth[0], $nameAndWidth[1]);

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
            /** @var Model\Entity\User $user */
            $user = $this->em->find('App\Model\Entity\User', $this->user->getId());
            $chats = $user->getChats();
            foreach ($chats as $chat) {
                foreach ($chat->getMessages() as $message) {
                    if ($message->getSender()->getId() != $this->user->id) {
                        if ($message->getSeen() === false) {
                            $this->template->notify = true;
                            break;
                        }
                    }
                }
            }
        }
    }

    protected function createComponentSignInForm()
    {
        $form = new Form;
        $form->elementPrototype->addAttributes(array('class' => 'ajax'));
        $form->addProtection();
        $form->addText('username', 'e-mail')->setAttribute('placeholder', 'nebo uživatelské jméno')->setAttribute('class', 'form-control')
            ->setRequired('Prosím zadejte e-mail nebo uživatelské jméno.');

        $form->addPassword('password', 'heslo:')->setAttribute('class', 'form-control')
            ->setRequired('Prosím zadejte heslo.');

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
            $this->getUser()->login($values->username, $values->password, 'app');
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
            /*$this->template->hide = 'true';
            $this->redrawControl('user_controls');
            $this->redrawControl('flashMessages');*/
            $this->redirect('this');
        } else {
            $this->redirect('Homepage:');
        }
    }

    public function handleLogout()
    {

        $this->getUser()->logout();
        $this->flashMessage('Byl jste odhlášen.');
        $this->redirect('this');


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

    protected function createComponentMessageForm()
    {
        return new Multiplier(function ($receiver_id) {
            $form = new Form;
            $form->addProtection();
            $form->addTextArea('text')->setAttribute('placeholder', 'Napište zprávu')->setAttribute('class', 'form-control');
            $form->addSubmit('send', 'Odeslat')->setAttribute('class', 'form-control')->setAttribute('id', 'submit_button');
            $form->addHidden('reciever_id', $receiver_id);
            $form->onSuccess[] = $this->messageFormSubmitted;
            $renderer = $form->getRenderer();
            $renderer->wrappers['controls']['container'] = NULL;
            $renderer->wrappers['pair']['container'] = NULL;
            $renderer->wrappers['label']['container'] = NULL;
            $renderer->wrappers['control']['container'] = NULL;
            return $form;

        });
    }

    public function messageFormSubmitted($form, $values)
    {
        $chats = $this->em->getRepository('App\Model\Entity\Chat')->findBy(['users.id' => $values->reciever_id]);
        foreach ($chats as $chat_i) {
            foreach ($chat_i->getUsers() as $user) {
                if ($user->getId() === $this->user->id) {
                    $chat = $chat_i;
                    break;
                }
            }
        }
        if (empty($chat)) {
            $chat = new Model\Entity\Chat();
            $newChat = true;
        }

        if (!empty($values->text)) {
            $message = new Model\Entity\Message();
            $sender = $this->em->find('App\Model\Entity\User', $this->user->id);
            $receiver = $this->em->find('App\Model\Entity\User', $values->reciever_id);
            $message->setText($values->text)->setSender($sender);
            $chat->addMessage($message);
            if (isset($newChat)) {
                $chat->addUser($sender);
                $chat->addUser($receiver);
            }
            $this->em->persist($chat);
            $this->em->flush();
        }
        $this->redirect('Chat:', $chat->getId());
    }

    protected function createComponentFbLogin()
    {
        $dialog = $this->facebook->createDialog('login');
        /* @var \Kdyby\Facebook\Dialog\LoginDialog $dialog */

        $dialog->onResponse[] = function (\Kdyby\Facebook\Dialog\LoginDialog $dialog) {
            $fb = $dialog->getFacebook();

            if (!$fb->getUser()) {
                $this->flashMessage('Facebook nerozeznal Váš profil.');

                return;
            }

            /*
             * If we get here, it means that the user was recognized
             * and we can call the Facebook API
             */

            try {
                $me = $fb->api('/me/?fields=name,email,id,link,picture.type(large)');
                $id = $me->offsetGet('id');
                if (array_key_exists('email', $me)) {
                    if ((!$existing = $this->em->getRepository('App\Model\Entity\User')->findOneByFacebook($id))) {
                        $token = Nette\Utils\Strings::random();
                        $mee = $me->offsetGet('picture');
                        $meee = $mee->offsetGet('data');
                        $picture = $meee->offsetGet('url');
                        $id = $me->offsetGet('id');
                        $name = $me->offsetGet('name');
                        $email = $me->offsetGet('email');
                        $targetPath = $this->presenter->basePath;
                        $cil2 = $targetPath . 'images/user/' . $id . '.jpg';
                        $name2 = explode(' ', $name);
                        $user = new Model\Entity\User();
                        $user->setPassword(password_hash($token, PASSWORD_DEFAULT))->setName(reset($name2))->setEmail($email)->setSurname(end($name2))->setUsername($name)->setConfirmedEmail(1)->setFacebook($id);

                        $this->em->persist($user);
                        $this->em->flush();
                        copy($picture, WWW_DIR . '/images/user/' . $user->getId() . '.jpg');
                        $this->getUser()->login($name, $token, 'app');
                        $this->redirect('Homepage:', array('welcome' => 1));
                    } //dump($me);
                    else {
                        $this->getUser()->login($existing->getUsername(), $existing->getPassword(), 'social');
                    }
                } else {
                    $this->flashMessage('Z Facebooku jsme nedostali všechny potřebné informace, registrujte se pomocí registračního formuláře.');
                }


            } catch (\Kdyby\Facebook\FacebookApiException $e) {
                /*
                 * You might wanna know what happened, so let's log the exception.
                 *
                 * Rendering entire bluescreen is kind of slow task,
                 * so might wanna log only $e->getMessage(), it's up to you
                 */
                \Tracy\Debugger::log($e, 'facebook');
                $this->flashMessage('Z neznámého důvodu se proces neprovedl, zkuste to prosím znovu nebo použijte jiný způsob.');
            } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
                \Tracy\Debugger::log($e, 'facebook');
                if (Strings::contains($e, 'UNIQ_8D93D649E7927C74') || Strings::contains($e, 'email')) {
                    $this->flashMessage('Email je již použit, použijte prosím klasické přihlašování.');
                } else {
                    $this->flashMessage('Přihlašovací jméno je již použito, použijte prosím klasické přihlašování.');
                }
            }
            $this->redirect('this');
        };

        return $dialog;
    }

    protected function createComponentContactForm()
    {

        $form = new Form;
        $form->addProtection();
        $form->addTextArea('text')->setAttribute('placeholder', 'Napište zprávu')->setAttribute('class', 'form-control');
        $form->addSubmit('send', 'Poslat')->setAttribute('class', 'form-control')->setAttribute('id', 'submit_button');
        $form->onSuccess[] = $this->contactFormSubmitted;
        $renderer = $form->getRenderer();
        $renderer->wrappers['controls']['container'] = NULL;
        $renderer->wrappers['pair']['container'] = NULL;
        $renderer->wrappers['label']['container'] = NULL;
        $renderer->wrappers['control']['container'] = NULL;
        return $form;

    }

    public function contactFormSubmitted($form, $values)
    {
        if (!empty($values->text)) {
            $mail = new Message;
            if ($this->user->isLoggedIn()) {
                /** @var Model\Entity\User $user */
                $user = $this->em->find('App\Model\Entity\User', $this->user->id);
                $mail->setSubject('Zpráva pro administrátora od uživatele '.$user->getUsername());
            }
            else {
               $mail->setSubject('Zpráva pro administrátora');
            }
            $mail->setFrom('postmaster@collectorsnest.eu')
                ->addTo('info@collectorsnest.eu')
                ->setBody($values->text);
            $this->mailer->send($mail);

            $this->flashMessage('Zpráva byla úspěšně odeslána.', 'success');
        }
    }
}
