<?php

namespace App\Presenters;

use App\Model\Entity\Topic;


class CronPresenter extends BasePresenter
{

    public function actionDefault()
    {
        $topics = $this->em->getRepository('App\Model\Entity\Topic')->findAll();
        foreach ($topics as $topic)
        {
            /** @var Topic $topic*/
            $topic->setRelevance();
            $this->em->persist($topic);
        }
        $this->em->flush();
        $this->redirect('Club:');
    }
}