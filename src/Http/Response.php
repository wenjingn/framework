<?php
namespace Http;

use Obj,
    Media;

class Response
{
    public $protocol = null;
    public $version = null;
    public $code  = null;
    public $msg   = null;

    public $prev   = null;
    public $header = null;
    public $cookies = null;
    public $content = null;

    public function __construct()
    {
        $this->header = new Header;
        $this->cookies = new Cookies;
    }

    public function parse($raw)
    {
        $pos = strpos($raw, "\r\n\r\n");
        $header  = substr($raw, 0, $pos);
        $content = substr($raw, $pos+4);

        $header = explode("\r\n", $header);
        list($htver, $this->code, $this->msg) = explode(' ', array_shift($header));
        list($this->protocol, $this->version) = explode('/', $htver);

# 1xx continue status code 
        if (floor($this->code/100) == 1) {
            $prev = new self;
            $prev->protocol = $this->protocol;
            $prev->version  = $this->version;
            $prev->code     = $this->code;
            $prev->msg      = $this->msg;
            $this->prev = $prev;
            return $this->parse($content);
        }

        foreach ($header as $h) {
            list($name, $value) = explode(': ', $h);
            if ($name == 'Set-Cookie') {
                $this->cookies->add((new Cookie)->parse($value));
            } else {
                $this->header->set($name, (new Header\Meta)->parse($value));
            }
        }

        $meta = $this->header->get('Content-Type');
        $o = Media::gen($meta->value);
        if ($o && $o->parse($content) !== false) 
            $this->content = $o;
        else 
            $this->content = $content;
        return $this;
    }

    public function statusLine()
    {
        return sprintf("%s/%s %d %s\r\n", 
        $this->protocol, $this->version, $this->code, $this->msg);
    }

    public function __toString()
    {
        $s = '';
        if ($this->prev) {
            $s.= $this->prev;
        }
        $s.= $this->statusLine();
        $s.= $this->header;
        $s.= $this->cookies->toFull();
        $s.= "\r\n";
        if ($this->content) 
        $s.= $this->content;
        return $s;
    }
}
