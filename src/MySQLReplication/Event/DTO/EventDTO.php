<?php

declare(strict_types=1);
/**
 * @license  https://github.com/krowinski/php-mysql-replication/blob/master/LICENSE
 */
namespace MySQLReplication\Event\DTO;

use JsonSerializable;
use MySQLReplication\Event\EventInfo;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @see https://dev.mysql.com/doc/internals/en/event-meanings.html
 */
abstract class EventDTO extends GenericEvent implements JsonSerializable
{
    protected $eventInfo;

    public function __construct(
        EventInfo $eventInfo
    ) {
        $this->eventInfo = $eventInfo;
    }

    abstract public function __toString(): string;

    public function getEventInfo(): EventInfo
    {
        return $this->eventInfo;
    }

    abstract public function getType(): string;
}
