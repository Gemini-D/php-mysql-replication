<?php

declare(strict_types=1);
/**
 * @license  https://github.com/krowinski/php-mysql-replication/blob/master/LICENSE
 */
namespace MySQLReplication\Event\DTO;

use MySQLReplication\Definitions\ConstEventsNames;

class DeleteRowsDTO extends RowsDTO
{
    protected $type = ConstEventsNames::DELETE;

    public function getType(): string
    {
        return $this->type;
    }
}
