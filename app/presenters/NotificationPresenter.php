<?php

namespace App\Presenters;


class NotificationPresenter extends BasePresenter
{
    public function renderDefault()
    {
        $this->flashMessage('Zde uvidíte Vaše notifikace.');
    }

}