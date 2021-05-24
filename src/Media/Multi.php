<?php
namespace Media;

use Media,
    Media\Multi\Part;

class Multi extends Media
{
    protected $type   = 'multipart/form-data';

    protected function initial()
    {
        $this->params->boundary = uniqid('------'.time());
    }

    public function getBoundary()
    {
        return $this->params->boundary;
    }

    public function setBoundary($boundary)
    {
        $this->params->boundary = $boundary;
        return $this;
    }

    public function set($name, $o)
    {
        $this->o[$name] = new Part($name, $o);
        return $this;
    }

    public function add(Part $part)
    {
        $this->o[$part->name] = $part;
    }

    public function adds(Multi $parts)
    {
        $this->o = array_merge($this->o, $parts->export());
    }

# Multi  = *("--" Boundary CRLF Part CRLF) "--" Boundary "--"
# Part   = 1 (Header CRLF) CRLF Data
    public function __toString()
    {
        $boundary = '--'.$this->params->boundary;
        $s = '';
        foreach ($this as $o) {
            $s.= $boundary."\r\n".$o."\r\n";
        }
        return $s.$boundary."--\r\n";
    }

    public function parse($data)
    {
        $boundary = '--'.$this->params->boundary;
        $data = trim(trim(substr($data, 0, -2), $boundary), "\r\n");
        $parts = explode("\r\n".$boundary."\r\n", $data);
        foreach ($parts as $part) {
            $this->add((new Part)->parse($part));
        }
        return $this;
    }
}
