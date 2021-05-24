<?php
namespace Media;

use Media;

class FormData extends Media
{
    protected $type = 'application/x-www-form-urlencoded';
    
    public $encode = false;

    public function __toString()
    {
        $s = '';
        foreach ($this as $k => $v) {
            $p = $this->encode ? 
                rawurlencode($k).'='.rawurlencode($v) : 
                $k.'='.$v;
            $s.= $p.'&';
        }
        return substr($s, 0, -1);
    }

    public function parse(string $params)
    {
        if (!$this->isEmpty()) $this->purge();
        if (empty($params)) return $this;
        $params = explode('&', $params);
        foreach ($params as $kv) {
            list($k, $v) = explode('=', $kv);
            if ($this->encode) {
                $k = rawurldecode($k);
                $v = rawurldecode($v);
            }
            $this->set($k, $v);
        }
        return $this;
    }
}
