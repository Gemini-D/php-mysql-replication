<?php

declare(strict_types=1);
/**
 * @license  https://github.com/krowinski/php-mysql-replication/blob/master/LICENSE
 */
namespace MySQLReplication\Event\DTO;

use MySQLReplication\Definitions\ConstEventsNames;

class WriteRowsDTO extends RowsDTO
{
    protected $type = ConstEventsNames::WRITE;

    public function getType(): string
    {
        return $this->type;
    }
}
