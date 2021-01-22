<?php

declare(strict_types=1);
/**
 * @license  https://github.com/krowinski/php-mysql-replication/blob/master/LICENSE
 */
namespace MySQLReplication\Event\DTO;

use MySQLReplication\Definitions\ConstEventsNames;

class FormatDescriptionEventDTO extends EventDTO
{
    private $type = ConstEventsNames::FORMAT_DESCRIPTION;

    public function __toString(): string
    {
        return PHP_EOL .
            '=== Event ' . $this->getType() . ' === ' . PHP_EOL .
            'Date: ' . $this->eventInfo->getDateTime() . PHP_EOL .
            'Log position: ' . $this->eventInfo->getPos() . PHP_EOL .
            'Event size: ' . $this->eventInfo->getSize() . PHP_EOL;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}
