<?php
namespace App\Presenters;

use Nette;
use Nette\Utils\Html;
use Nette\Utils\Strings;

class CheckPresenter extends BasePresenter
{
    public function actionVerify($token)
    {
        $user = $this->em->getRepository('App\Model\Entity\User')->findOneByConfirmedEmail($token);
        if ($user) {
            $user->setConfirmedEmail('1');
            $this->em->persist($user);
            $this->em->flush();
            $this->flashMessage('Děkujeme za aktivaci účtu.', 'alert alert-success');
        } else {
            $this->flashMessage('Váš účet se nám nepodařilo kontaktovat, prosím napište nám na e-mail postmaster@colletorsnest.eu', 'alert alert-warning');
        }
        $this->redirect('Homepage:');
    }


}