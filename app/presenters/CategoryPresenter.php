<?php

namespace App\Presenters;

use Nette;
use App\Model;


class CategoryPresenter extends BasePresenter
{
  public $page;
	public function renderDefault($page=1)
	{
		$this->template->anyVariable = 'any value';
	}

}
