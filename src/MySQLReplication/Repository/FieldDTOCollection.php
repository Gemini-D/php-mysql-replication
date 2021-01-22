<?php

declare(strict_types=1);
/**
 * @license  https://github.com/krowinski/php-mysql-replication/blob/master/LICENSE
 */
namespace MySQLReplication\Repository;

use Doctrine\Common\Collections\ArrayCollection;

class FieldDTOCollection extends ArrayCollection
{
    public static function makeFromArray(array $fields): self
    {
        $collection = new self();
        foreach ($fields as $field) {
            $collection->add(FieldDTO::makeFromArray($field));
        }

        return $collection;
    }
}
