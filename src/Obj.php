<?php
class Obj implements ArrayAccess, Countable, Iterator, JsonSerializable
{
    protected $o = null;

    public function __construct(array $obj = null)
    {
        $this->o = [];
        if ($obj === null)  return;
        $this->import($obj);
    }

    /**
     * @set
     */
    public function set($k, $v)
    {
        if (is_array($v)) {
            $v = new static($v);
        }
        if ($k === null)
            $this->o[] = $v;
        else
            $this->o[$k] = $v;
        return $this;
    }

    /**
     * @get
     */
    public function get($k)
    {
        if ($k === null) return null;
        if (isset($this->o[$k]))
            return $this->o[$k];
        return null;
    }

    /**
     * @ magic methods
     */
    public function __set(string $k, $v)
    {
        $this->set($k, $v);
    }

    public function __get(string $k)
    {
        return $this->get($k);
    }

    public function __isset($k)
    {
        return $this->offsetExists($k);
    }

    public function __unset($k)
    {
        $this->offsetUnset($k);
    }

    /**
     * @ ArrayAccess implements
     */
    public function offsetSet($k, $v)
    {
        return $this->set($k, $v);
    }

    public function offsetGet($k)
    {
        return $this->get($k);
    }

    public function offsetExists($k)
    {
        return isset($this->o[$k]);
    }

    public function offsetUnset($k)
    {
        unset($this->o[$k]);
    }

    /**
     * @ Iterator implements
     */
    public function valid()
    {
        return $this->key() !== null;
    }
    
    public function current()
    {
        return current($this->o);
    }

    public function key()
    {
        return key($this->o);
    }

    public function next()
    {
        next($this->o);
    }

    public function rewind()
    {
        reset($this->o);
    }

    /**
     * @ countable implements
     */
    public function count()
    {
        return count($this->o);
    }

    public function isEmpty()
    {
        return empty($this->o);
    }

    public function purge()
    {
        if (count($this->o))
            $this->o = [];
        return $this;
    }

    public function import($import)
    {
        foreach ($import as $k => $v) {
            $this->set($k, $v);
        }
        return $this;
    }

    public function keys()
    {
        return new static(array_keys($this->o));
    }

    public function export($reindex = false)
    {
        $export = [];
        foreach ($this->o as $k => $v) {
            if ($v instanceof self) {
                if ($reindex) {
                    $export[  ] = $v->export();
                } else {
                    $export[$k] = $v->export();
                }
            } else {
                if ($reindex) {
                    $export[  ] = $v;
                } else {
                    $export[$k] = $v;
                }
            }
        }
        return $export;
    }

    public function jsonSerialize()
    {
        return $this->o;
    }
    
    public function join($glue = '')
    {
        return implode($glue, $this->o);
    }
    
    public function reduce(callable $callback, $initial)
    {
        $reduced = $initial;
        foreach ($this->o as $k => $v) {
            $reduced = $callback($v, $k, $reduced);
        }
        return $reduced;
    }

    public function map(callable $call, $reindex = false)
    {
        $ret = new static;
        foreach ($this->o as $k => $v) {
            if (false === $o = $call($v, $k)) break;
            if ($reindex) {
                $ret[] = $o;
            } else {
                $ret[$k] = $o;
            }
        }
        return $ret;
    }

    public function toDict()
    {
        return new Dict($this->o);
    }

    public function clone()
    {
        $o = new static($a);
        foreach ($this->o as $k => $v) {
            $o->set($k, $v->clone());
        }
        return $o;
    }
}
