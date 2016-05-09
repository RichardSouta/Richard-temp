<?php
namespace App\Presenters;
use Nette\Application\UI\Form as Form;
use Nette;
use Nette\Utils\Html;
use Nette\Utils\Strings;
use Nette\Mail\Message;
use Nette\Mail\SendmailMailer;
class CheckPresenter extends BasePresenter
{
	/** @var Nette\Database\Context */

 public function renderVerify()
 {


 }


// Akce na oveření tokenu
    public function actionVerify($token) {
        if ($this->database->table('users')->where('confirmedEmail', $token)->update(array(
            'confirmedEmail' => '1'
        ))) {
            $this->flashMessage('Děkujeme za aktivaci účtu.', 'alert alert-success');
            // Po odeslani zapisem zaznam do logu
          
            //$this->redirect('Homepage:default');
        } else {
            $this->flashMessage('Registrace nebyla úspěšná, prosím kontaktujte podporu.', 'alert alert-warning');
            // Po odeslani zapisem zaznam do logu
          
           // $this->redirect('Homepage:default');
        }
        $this->redirect('Homepage:');
    }

    
    
    
}