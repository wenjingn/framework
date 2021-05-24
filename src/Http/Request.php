<?php
namespace Http;

use Media,
    Obj;

class Request
{
    /**
     * @fields
     */
    private $version  = 1.1;
    private $method = 'GET';

    public $errno;
    public $error;

    /**
     * @events
     */
    private $events = null;

    public function on($event, callable $callback)
    {
        if ($this->events === null) {
            $this->events = new Obj;
            $this->events->set($event, []);
        } elseif (!$this->events->offsetExists($event)) {
            $this->events->set($event, []);
        }
        $this->events->get($event)->set(null, $callback);
    }

    public function trigger($event, ...$argvs)
    {
        if ($this->events === null) return;
        if (!$this->events->offsetExists($event)) return;
        foreach ($this->events->get($event) as $callback) {
            if (false === $callback($this, ...$argvs))
                return false;
        }
        return true;
    }

    public function getProtocol()
    {
        if ($this->url instanceof Url) {
            return $this->url->getProtocol();
        }
        $partials = explode('://', $this->url);
        if (count($partials) == 1) return 'http';
        return $partials[0];
    }

    public function getVersion()
    {
        return $this->version;
    }

    public function setVersion(float $version)
    {
        $this->version = $version;
        return $this;
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function setMethod(string $method)
    {
        $this->method = $method;
        return $this;
    }

    /**
     * @objects
     */
    public $url     = null;
    public $header  = null;
    public $cookies = null;
    public $content = null;

    public function __construct(Header $header = null, Cookies $cookies = null)
    {
        $this->header  = $header ?? new Header;
        $this->cookies = $cookies ?? new Cookies;
    }

    public function hasHeader()
    {
        return !$this->header->isEmpty();
    }

    public function hasCookie()
    {
        return !$this->cookies->isEmpty();
    }

    public function hasContent()
    {
        if ($this->content === null)
            return false;
        if ($this->content instanceof Media) {
            return !$this->content->isEmpty();
        }
        return !empty($this->content);
    }

    public function setContent($data, $type = null)
    {
        if (null === $type) {
            if (is_scalar($data) || $data instanceof Media) {
                $this->content = $data;
                return $this;
            } else {
                throw new \InvalidArgumentException
                ('Http\\Request::setContent invalid data type');
            }
        }
        if (null === ($o = Media::gen($type))) 
            throw new \OutOfRangeException('notfound typeof Media');
        $this->content = $o->import($data);
        return $this;
    }

    public function getContentType()
    {
        if (is_scalar($this->content)) {
            return 'text/plain';
        } elseif ($this->content instanceof Media) {
            return $this->content->getContentType();
        }
        return Media::$unknown;
    }

    public function __toString()
    {
        if ($this->url instanceof Url) {
            $r = sprintf("%s %s %s/%s\r\n", $this->method, $this->url->toUri(), 
                 strtoupper($this->url->getProtocol()), $this->version);

            $r.= sprintf("Host: %s\r\n", $this->url->toHost());
        } else {
# 简陋版 request line; url为字符串
            $r = sprintf("%s %s %s/%s\r\n", $this->method, $this->url, $this->getProtocol(), $this->version);
        }

        $r.= $this->header;
        $r.= 'Cookie: '.$this->cookies."\r\n";
        return $r;
    }
}
