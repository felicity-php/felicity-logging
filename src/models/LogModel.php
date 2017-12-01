<?php

/**
 * @author TJ Draper <tj@buzzingpixel.com>
 * @copyright 2017 BuzzingPixel, LLC
 * @license Apache-2.0
 */

namespace felicity\logging\models;

use DateTime;
use felicity\datamodel\Model;
use felicity\datamodel\services\datahandlers\DateTimeHandler;
use felicity\datamodel\services\datahandlers\IntHandler;
use felicity\datamodel\services\datahandlers\StringHandler;

/**
 * Class LogModel
 */
class LogModel extends Model
{
    /** @var DateTime $time */
    public $time;

    /** @var mixed $messages */
    public $message = [];

    /** @var int $level */
    public $level = 100;

    /** @var string|int $levelString */
    public $levelString = '';

    /** @var string $category */
    public $category = 'application';

    /**
     * @inheritdoc
     */
    public function __construct(array $properties = array())
    {
        $this->time = new DateTime();
        parent::__construct($properties);
    }

    /**
     * @inheritdoc
     */
    protected function defineHandlers(): array
    {
        return [
            'time' => [
                'class' => DateTimeHandler::class,
            ],
            'level' => [
                'class' => IntHandler::class,
            ],
            'category' => [
                'class' => StringHandler::class,
            ],
        ];
    }
}
