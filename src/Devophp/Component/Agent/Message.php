<?php

namespace Devophp\Component\Agent;

class Message
{
    private $body;
    public function __construct($body)
    {
        $this->body = $body;
    }
    
    public function getBody()
    {
        return $this->body;
    }
}
