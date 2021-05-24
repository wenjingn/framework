<?php
namespace Http;

use Dict,
    Http\Header\Meta;

class Header extends Dict
{
    public function set($name, $meta)
    {
        if (is_scalar($meta)) {
            $this->o[$name] = $meta;
        } elseif ($meta instanceof Meta) {
            $this->o[$name] = $meta;
        } else {
            throw new \InvalidArgumentException();
        }
        return $this;
    }

    public function toCurlOptions()
    {
        return $this->map(function($v, $k){
            return $k.': '.$v;
        })->export(true);
    }

    public function __toString()
    {
        $s = '';foreach ($this as $k => $v) {
            $s.= "$k: $v\r\n";
        }
        return $s;
    }

    public function parseFromBrowser($header, Cookies $cookies = null)
    {
        foreach (explode("\n", $header) as $h) {
            [$k, $v] = explode(': ', $h);
            if ($k == 'Cookie') {
                if ($cookies) 
                    $cookies->parse($v);
            } else {
                $this->set($k, $v);
            }
        }
    }
}
