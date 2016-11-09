<?php
/**
 * Created by PhpStorm.
 * User: PC
 * Date: 12.10.2016
 * Time: 21:43
 */

namespace App\AdminModule\Controls;

use App\Model\ForumManager;
use Nette\Application\UI\Control;

class Forum extends Control
{
    /**
     * @var ForumManager
     */
    public $forumManager;

    /**
     * Forum constructor.
     * @param ForumManager $forumManager Automaticky injektovaná instace triedy XXX pre prácu s XXX
     */
    public function __construct(ForumManager $forumManager)
    {
        $this->forumManager = $forumManager;
    }


    public function renderSubCategories($id)
    {
        $this->template->sub = $this->forumManager->getSubCategories($id);
        $this->template->setFile(__DIR__ . '/templates/SubCategories.latte');
        $this->template->render();
    }
}