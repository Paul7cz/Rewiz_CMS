<?php

namespace App\FrontModule\Controls;

use Nette\Application\UI\Control;

/**
 * Created by PhpStorm.
 * User: Dominik Gavrecký
 * Date: 27.11.2016
 * Time: 19:41
 */
class News extends Control
{

    /** @var \App\Model\NewsManager */
    public $newsManager;

    /**
     * News constructor.
     * @param \App\Model\NewsManager $newsManager
     */
    public function __construct(\App\Model\NewsManager $newsManager)
    {
        $this->newsManager = $newsManager;
    }

    public function renderReply($comment_id){
        $this->template->reply = $this->newsManager->getReplyComments($comment_id);
        $this->template->setFile(__DIR__ . '/templates/reply.latte');
        $this->template->render();
    }


}