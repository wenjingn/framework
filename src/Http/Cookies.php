<?php
namespace Http;

use Dict;

class Cookies extends Dict
{
    public function set($key, $val)
    {
        $this->o[$key] = new Cookie($key, $val);
        return $this;
    }

    public function adds(Cookies $cookies)
    {
        $this->o = array_merge($this->o, $cookies->export());
    }

    public function add(Cookie $cookie)
    {
        $this->o[$cookie->key] = $cookie;
    }

    /**
     *
     */
    public function __toString()
    {
        $now = time();
        $s = '';
        foreach ($this->o as $k => $cookie) {
            if ($cookie->expires && $now > $cookie->expires)
                continue; # why follow? 
            $s.= $k.'='.$cookie->val.'; ';
        }

        return substr($s, 0, -2); # del tail  '; ' 
    }

    public function toFull()
    {
        $s = '';
        foreach ($this->o as $v) {
            $s.= $v."\r\n";
        }
        return $s;
    }

    public function parse($cookies)
    {
        $cookies = explode('; ', $cookies);
        foreach ($cookies as $cookie) {
            [$k, $v] = explode('=', $cookie);
            $this->set($k, $v);
        }
    }
}
