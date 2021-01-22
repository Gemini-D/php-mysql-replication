<?php

declare(strict_types=1);
/**
 * @license  https://github.com/krowinski/php-mysql-replication/blob/master/LICENSE
 */
namespace MySQLReplication\Event\DTO;

use MySQLReplication\Definitions\ConstEventsNames;
use MySQLReplication\Event\EventInfo;

class GTIDLogDTO extends EventDTO
{
    private $commit;

    private $gtid;

    private $type = ConstEventsNames::GTID;

    /**
     * GTIDLogEventDTO constructor.
     */
    public function __construct(
        EventInfo $eventInfo,
        bool $commit,
        string $gtid
    ) {
        parent::__construct($eventInfo);

        $this->commit = $commit;
        $this->gtid = $gtid;
    }

    public function __toString(): string
    {
        return PHP_EOL .
            '=== Event ' . $this->getType() . ' === ' . PHP_EOL .
            'Date: ' . $this->eventInfo->getDateTime() . PHP_EOL .
            'Log position: ' . $this->eventInfo->getPos() . PHP_EOL .
            'Event size: ' . $this->eventInfo->getSize() . PHP_EOL .
            'Commit: ' . var_export($this->commit, true) . PHP_EOL .
            'GTID NEXT: ' . $this->gtid . PHP_EOL;
    }

    public function isCommit(): bool
    {
        return $this->commit;
    }

    public function getGtid(): string
    {
        return $this->gtid;
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
