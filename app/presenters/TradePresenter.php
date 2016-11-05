<?php

namespace App\Presenters;


use App\Model\Entity\Trade;

class TradePresenter extends BasePresenter
{
    public function renderDefault($id)
    {
        if (!($this->user->isLoggedIn() || !$id)) {
            $this->redirect('Homepage:');
        }

        /** @var Trade $trade */
        $trade = $this->em->getRepository('App\Model\Entity\Trade')->find($id);
        if ($this->user->id != $trade->getReceiver()->getId()) {
            $this->redirect('Homepage:');
        }

        if($trade->getStatus()!='pending')
        {
            $this->flashMessage('Tato nabídka již byla Vámi vyhodnocena');
            $this->redirect('Homepage:');
        }
        $this->template->trade = $trade;
    }


    public function actionConfirm($id)
    {
        /** @var Trade $trade */
        $trade = $this->em->getRepository('App\Model\Entity\Trade')->find($id);
        $trade->setStatus('confirmed');
        $trade->getCollectibleAsked()->setTradeable(false)->setUser($trade->getSender());
        $trade->getCollectibleOffered()->setTradeable(false)->setUser($trade->getReceiver());
        $this->em->flush();
        $this->flashMessage('Nabídka byla potvrzena a předměty vyměněny. Fyzické předání je už na vás :-)');
        $this->redirect('Collectible:',$trade->getCollectibleOffered()->getId());
    }

    public function actionDecline($id)
    {
        /** @var Trade $trade */
        $trade = $this->em->getRepository('App\Model\Entity\Trade')->find($id);
        $trade->setStatus('declined');
        $this->em->flush();
        $this->flashMessage('Nabídka byla trvale odmítnuta');
        $this->redirect('Homepage:');
    }
}