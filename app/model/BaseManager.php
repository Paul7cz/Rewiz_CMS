<?php

namespace App\Model;

use Nette\Database\Context;
use Nette\Object;

/**
 * Class BaseManager
 * @package App\Model
 * @author  Dominik Gavrecký <dominikgavrecky@icloud.com>
 * Hlavná modelova logika pre prácu s databázov
 */
class BaseManager extends Object
{
    /** @var Context */
    protected $database;

    /**
     * BaseManager constructor.
     * @param Context $database
     */
    public function __construct(Context $database)
    {
        $this->database = $database;
    }
}
