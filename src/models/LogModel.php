<?php

/**
 * @author TJ Draper <tj@buzzingpixel.com>
 * @copyright 2017 BuzzingPixel, LLC
 * @license Apache-2.0
 */

namespace felicity\logging\models;

use felicity\datamodel\Model;
use felicity\datamodel\services\datahandlers\IntHandler;
use felicity\datamodel\services\datahandlers\StringHandler;
use felicity\datamodel\services\datahandlers\StringArrayHandler;

/**
 * Class LogModel
 */
class LogModel extends Model
{
    /** @var mixed $messages */
    public $message = [];

    /** @var int $level */
    public $level = 100;

    /** @var string $category */
    public $category = 'application';

    /**
     * @inheritdoc
     */
    protected function defineHandlers(): array
    {
        return [
            'level' => [
                'class' => IntHandler::class,
            ],
            'category' => [
                'class' => StringHandler::class,
            ],
        ];
    }
}
