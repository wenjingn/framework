<?php
namespace Http\Header;

use Obj,
    Http\Cookie;

class Meta
{
    public $value = null;
    public $params = null;

    public function __construct(string $value = '')
    {
        $this->value = $value;
    }

    public function __toString()
    {
        $s = $this->value;
        if ($this->params && !$this->params->isEmpty()) {
            foreach ($this->params as $k => $v) {
                $s.= sprintf('; %s=%s', $k, $v);
            }
        }
        return $s;
    }

    public function parse($meta)
    {
        $meta = explode('; ', $meta);
        $this->value = array_shift($meta);
        if (empty($meta)) return $this;
        $this->params = new Obj;
        foreach ($meta as $m) {
            list($k, $v) = explode('=', $m);
            $this->params->$k = $v;
        }
        return $this;
    }
}
