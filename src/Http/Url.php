<?php
namespace Http;

use Media\FormData;

class Url {
    /**
     * @fields
     */
    private $protocol = 'http';
    private $host = '';
    private $port = 80;
    private $path = '';
    private $prefix = '';

    /**
     * @objects
     */
    public $query = null;

    public function __construct()
    {
        $this->query = new FormData;
    }
    
    public function getProtocol()
    {
        return $this->protocol;
    }

    public function setProtocol(string $protocol)
    {
        $this->protocol = $protocol;
        return $this;
    }

    public function getHost()
    {
        return $this->host;
    }

    public function setHost(string $host)
    {
        $this->host = $host;
        return $this;
    }

    public function getPort()
    {
        return $this->port;
    }

    public function setPort(int $port)
    {
        $this->port = $port;
        return $this;
    }

    public function getPrefix()
    {
        return $this->prefix;
    }

    public function setPrefix(string $prefix)
    {
        $this->prefix = $prefix;
        return $this;
    }

    public function getPath()
    {
        return $this->prefix.$this->path;
    }

    public function setPath(string $path)
    {
        $this->path = $path;
        return $this;
    }

    public function __toString()
    {
        return sprintf('%s://%s%s', $this->protocol, $this->toHost(), $this->toUri());
    }

    public function toUri()
    {
        if ($this->query->count()) {
            return sprintf('%s?%s', $this->getPath(), $this->query);
        }
        return $this->getPath();
    }

    public function toHost()
    {
        if ($this->port == 80)
            return $this->host;
        return $this->host.':'.$this->port;
    }

    public function parse(string $url = null)
    {
        $partials = explode('://', $url);
        $parts = count($partials);
        if ($parts > 2) throw new InvalidArgumentException('illegal url');
        if ($parts == 1) {
            $protocol = 'http';
        } else {
            $protocol = $partials[0];
            if ($protocol != 'http' || $protocol != 'https') {
                $protocol = 'http';
            }
            $url = $partials[1];
        }
        $this->setProtocol($protocol);

        $partials = explode('?', $url);
        $parts = count($partials);
        if ($parts > 2) throw new InvalidArgumentException('illegal url');
        if ($parts == 1) {
            $query = '';
        } else {
            $query = $partials[1];
            $url = $partials[0];
        }
        $this->query->parse($query);

        $partials = explode('/', $url);
        $host = array_shift($partials);
        $path = '';
        foreach ($partials as $sub) {
            if ($sub)
                $path .= '/'.$sub;
        }
        $this->setPath($path);

        $partials = explode(':', $host);
        $parts = count($partials);
        if ($parts > 2) 
            throw new InvalidArgumentException('illegal url');
        if ($parts == 1) {
            $port = 80;
        } else {
            $host = $partials[0];
            $port = (int)$partials[1];
        }
        $this->setHost($host);
        $this->setPort($port);
    }
}
