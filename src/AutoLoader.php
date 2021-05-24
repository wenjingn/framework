<?php
class AutoLoader
{
    private $extdirs = [];
    private $libdirs = [];

    public function add($dirs, $pre = false)
    {
        $dirs = (array)$dirs;
        if ($pre) {
            $this->extdirs = array_merge($dirs, $this->extdirs);
        } else {
            $this->extdirs = array_merge($this->extdirs, $dirs);
        }
    }

    public function addns($ns, $dirs, $pre = false)
    {
        if (empty($ns)) 
            throw new \InvalidArgumentException('namespace must not empty');
        $dirs = (array)$dirs;
        $ns = trim($ns, '\\');
        if (!isset($this->libdirs[$ns])) {
            $this->libdirs[$ns] = [];
        }

        if ($pre) {
            $this->libdirs[$ns] = array_merge($dirs, $this->libdirs[$ns]);
        } else {
            $this->libdirs[$ns] = array_merge($this->libdirs[$ns], $dirs);
        }
    }

    public function register()
    {
        spl_autoload_register([$this, 'loadClass']);
    }

    public function loadClass($class)
    {
        $logicPath = strtr($class, '\\', DIRECTORY_SEPARATOR).'.php';
        $subPath = $class;
        
        while ($pos = strrpos($subPath, '\\')) {
            $subPath = substr($subPath, 0, $pos);
            if (isset($this->libdirs[$subPath])) {
                foreach ($this->libdirs[$subPath] as $dir) {
                    $realPath = $dir.substr($logicPath, $pos);
                    if (is_file($realPath)) {
                        includeFile($realPath);
                        return true;
                    }
                }
            }
        }

        foreach ($this->extdirs as $dir) {
            $realPath = $dir.DIRECTORY_SEPARATOR.$logicPath;
            if (is_file($realPath)) {
                includeFile($realPath);
                return true;
            }
        }
    }
}

/* scope isolated */
function includeFile($file)
{
    include $file;
}
