<?php

namespace Media;

use Media;

class Json extends Media
{
    protected $type = 'application/json';

    public function __toString()
    {
        return json_encode($this);
    }

    public function parse($json)
    {
        if (!$this->isEmpty()) $this->purge();
        if (null === $o = json_decode($json, true)) {
            return false;
        }
        
        if (is_string($o)) {
            //return $this->set('str', $o);
        }
        return $this->import($o);
    }
}
