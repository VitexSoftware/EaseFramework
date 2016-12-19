<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Ease\Logger;

/**
 * Description of Message
 *
 * @author vitex
 */
class Message
{
    /**
     * Message body
     * @var string
     */
    public $body;

    /**
     * Message type
     * @var string info|succes|warning|danger|mail
     */
    public $type;
    public $caller;

    /**
     *
     * @var type
     */
    public $when;

    public function __construct($message, $type = 'info', $caller = null,
                                $when = null)
    {
        $this->body = $message;
        $this->type   = $type;
        $this->caller = $caller;
        if (is_null($when)) {
            $this->when = time();
        } else {
            $this->when = $when;
        }
    }
}
