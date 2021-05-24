<?php
namespace Http;

class Cookie
{
    public $key;
    public $val;
    public $expires;
    public $MaxAge;
    public $path;
    public $domain;
    public $secure;
    public $HttpOnly;

    public function __construct(string $key = '', string $val = '', 
            int $expires = 0, int $MaxAge = 0, string $path = '', string $domain = '',
            bool $secure = false, bool $HttpOnly = false)
    {
        $this->key = $key;
        $this->val = $val;
        $this->expires = $expires;
        $this->MaxAge = $MaxAge;
        $this->path = $path;
        $this->domain = $domain;
        $this->secure = $secure;
        $this->HttpOnly = $HttpOnly;
    }

    public function __toString()
    {
        $s = sprintf('Set-Cookie: %s=%s', $this->key, $this->val);
        if ($this->expires)  $s.= '; expires='.date('D, d-M-Y H:i:s e', $this->expires);
        if ($this->MaxAge)   $s.= '; Max-Age='.$this->MaxAge;
        if ($this->path)     $s.= '; path='.$this->path;
        if ($this->domain)   $s.= '; domain='.$this->domain;
        if ($this->secure)   $s.= '; secure';
        if ($this->HttpOnly) $s.= '; HttpOnly';
        return $s;
    }

    public function parse(string $cookie)
    {
        $props = explode('; ', $cookie);
        list($this->key, $this->val) = explode('=', array_shift($props));
        foreach ($props as $prop) {
            $kv = explode('=', $prop);
            $k= $kv[0];
            if (count($kv) == 1) {
                $this->$k = true;
            } else {
                $v = $kv[1];
                if ($k == 'Max-Age') {
                    $k = 'MaxAge';
                }
                if ($k == 'expires') {
                    $v = strtotime($v);
                }
                $this->$k = $v;
            }
        }
        return $this;
    }
}
