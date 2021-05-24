<?php
class Dict extends Obj implements Iterator
{
    /**
     * @method in sort, rsort, asort, arsort, ksort, krsort
     * @flag in SORT_REGULAR, SORT_NUMERIC, SORT_STRING, etc see php manual
     */
    public function sort(callable $method, $flag = SORT_STRING)
    {
        $method($this->o, $flag);
    }

    public function usort(callable $umethod, callable $method)
    {
        $method($this->o, $umethod);
    }

    public function each(callable $call, $reindex = false)
    {
        foreach ($this->o as $k => $v) {
            if (false === $o = $call($v, $k)) break;
            if ($reindex) $this->o[  ] = $o;
            else $this->o[$k] = $o;
        }
        return $this;
    }
}
