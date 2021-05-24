<?php
/**
 * $Id$
 */

 /**
  *
  */
namespace Database;

/**
 *
 */
use Pdo;

/**
 *
 */
class PhpPdo extends Pdo
{
    /**
     *
     * @param  string $schema
     * @param  Object $bind
     * @return integer
     */
    public function insert($schema, Object $bind)
    {
        $sql = sprintf('INSERT INTO %s (%s) VALUES (%s)', $schema, $bind->keys()->map(function($c) {
            return "`$c`";
        })->join(','), $bind->map(function($v) {
            return "?";
        })->join(','));

        $stmt = $this->prepare($sql);
        $stmt->execute($bind->export(true));
        return $stmt->rowCount();
    }
}
