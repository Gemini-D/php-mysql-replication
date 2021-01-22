<?php

declare(strict_types=1);
/**
 * @license  https://github.com/krowinski/php-mysql-replication/blob/master/LICENSE
 */
namespace MySQLReplication\Event\RowEvent;

use MySQLReplication\BinaryDataReader\BinaryDataReader;
use MySQLReplication\Event\EventInfo;
use MySQLReplication\Repository\RepositoryInterface;
use Psr\SimpleCache\CacheInterface;

class RowEventBuilder
{
    private $repository;

    private $cache;

    /**
     * @var BinaryDataReader
     */
    private $package;

    /**
     * @var EventInfo
     */
    private $eventInfo;

    public function __construct(
        RepositoryInterface $repository,
        CacheInterface $cache
    ) {
        $this->repository = $repository;
        $this->cache = $cache;
    }

    public function withPackage(BinaryDataReader $package): void
    {
        $this->package = $package;
    }

    public function build(): RowEvent
    {
        return new RowEvent(
            $this->repository,
            $this->package,
            $this->eventInfo,
            $this->cache
        );
    }

    public function withEventInfo(EventInfo $eventInfo): void
    {
        $this->eventInfo = $eventInfo;
    }
}
