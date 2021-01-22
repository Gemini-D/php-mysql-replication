<?php

declare(strict_types=1);
/**
 * @license  https://github.com/krowinski/php-mysql-replication/blob/master/LICENSE
 */
namespace MySQLReplication\Event;

use MySQLReplication\BinaryDataReader\BinaryDataReader;
use MySQLReplication\BinaryDataReader\BinaryDataReaderException;
use MySQLReplication\BinLog\BinLogException;
use MySQLReplication\BinLog\BinLogServerInfo;
use MySQLReplication\BinLog\BinLogSocketConnect;
use MySQLReplication\Config\Config;
use MySQLReplication\Definitions\ConstEventType;
use MySQLReplication\Event\DTO\EventDTO;
use MySQLReplication\Event\DTO\FormatDescriptionEventDTO;
use MySQLReplication\Event\DTO\HeartbeatDTO;
use MySQLReplication\Event\DTO\QueryDTO;
use MySQLReplication\Event\RowEvent\RowEventFactory;
use MySQLReplication\Exception\MySQLReplicationException;
use MySQLReplication\JsonBinaryDecoder\JsonBinaryDecoderException;
use MySQLReplication\Socket\SocketException;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Event
{
    private const MARIADB_DUMMY_QUERY = '# Dum';

    private const EOF_HEADER_VALUE = 254;

    private $binLogSocketConnect;

    private $rowEventFactory;

    private $eventDispatcher;

    private $cache;

    public function __construct(
        BinLogSocketConnect $binLogSocketConnect,
        RowEventFactory $rowEventFactory,
        EventDispatcherInterface $eventDispatcher,
        CacheInterface $cache
    ) {
        $this->binLogSocketConnect = $binLogSocketConnect;
        $this->rowEventFactory = $rowEventFactory;
        $this->eventDispatcher = $eventDispatcher;
        $this->cache = $cache;
    }

    /**
     * @throws BinaryDataReaderException
     * @throws BinLogException
     * @throws MySQLReplicationException
     * @throws JsonBinaryDecoderException
     * @throws InvalidArgumentException
     * @throws SocketException
     */
    public function consume(): void
    {
        $binaryDataReader = new BinaryDataReader($this->binLogSocketConnect->getResponse());

        // check EOF_Packet -> https://dev.mysql.com/doc/internals/en/packet-EOF_Packet.html
        if ($binaryDataReader->readUInt8() === self::EOF_HEADER_VALUE) {
            return;
        }

        // decode all events data
        $eventInfo = $this->createEventInfo($binaryDataReader);

        $eventDTO = null;

        // we always need this events to clean table maps and for BinLogCurrent class to keep track of binlog position
        // always parse table map event but propagate when needed (we need this for creating table cache)
        if ($eventInfo->getType() === ConstEventType::TABLE_MAP_EVENT) {
            $eventDTO = $this->rowEventFactory->makeRowEvent($binaryDataReader, $eventInfo)->makeTableMapDTO();
        } elseif ($eventInfo->getType() === ConstEventType::ROTATE_EVENT) {
            $this->cache->clear();
            $eventDTO = (new RotateEvent($eventInfo, $binaryDataReader))->makeRotateEventDTO();
        } elseif ($eventInfo->getType() === ConstEventType::GTID_LOG_EVENT) {
            $eventDTO = (new GtidEvent($eventInfo, $binaryDataReader))->makeGTIDLogDTO();
        } elseif ($eventInfo->getType() === ConstEventType::HEARTBEAT_LOG_EVENT) {
            $eventDTO = new HeartbeatDTO($eventInfo);
        } elseif ($eventInfo->getType() === ConstEventType::MARIA_GTID_EVENT) {
            $eventDTO = (new MariaDbGtidEvent($eventInfo, $binaryDataReader))->makeMariaDbGTIDLogDTO();
        }

        // check for ignore and permitted events
        if (! Config::checkEvent($eventInfo->getType())) {
            return;
        }

        if (in_array($eventInfo->getType(), [ConstEventType::UPDATE_ROWS_EVENT_V1, ConstEventType::UPDATE_ROWS_EVENT_V2], true)) {
            $eventDTO = $this->rowEventFactory->makeRowEvent($binaryDataReader, $eventInfo)->makeUpdateRowsDTO();
        } elseif (in_array($eventInfo->getType(), [ConstEventType::WRITE_ROWS_EVENT_V1, ConstEventType::WRITE_ROWS_EVENT_V2], true)) {
            $eventDTO = $this->rowEventFactory->makeRowEvent($binaryDataReader, $eventInfo)->makeWriteRowsDTO();
        } elseif (in_array($eventInfo->getType(), [ConstEventType::DELETE_ROWS_EVENT_V1, ConstEventType::DELETE_ROWS_EVENT_V2], true)) {
            $eventDTO = $this->rowEventFactory->makeRowEvent($binaryDataReader, $eventInfo)->makeDeleteRowsDTO();
        } elseif ($eventInfo->getType() === ConstEventType::XID_EVENT) {
            $eventDTO = (new XidEvent($eventInfo, $binaryDataReader))->makeXidDTO();
        } elseif ($eventInfo->getType() === ConstEventType::QUERY_EVENT) {
            $eventDTO = $this->filterDummyMariaDbEvents((new QueryEvent($eventInfo, $binaryDataReader))->makeQueryDTO());
        } elseif ($eventInfo->getType() === ConstEventType::FORMAT_DESCRIPTION_EVENT) {
            $eventDTO = new FormatDescriptionEventDTO($eventInfo);
        }

        $this->dispatch($eventDTO);
    }

    private function createEventInfo(BinaryDataReader $binaryDataReader): EventInfo
    {
        return new EventInfo(
            $binaryDataReader->readInt32(),
            $binaryDataReader->readUInt8(),
            $binaryDataReader->readInt32(),
            $binaryDataReader->readInt32(),
            $binaryDataReader->readInt32(),
            $binaryDataReader->readUInt16(),
            $this->binLogSocketConnect->getCheckSum(),
            $this->binLogSocketConnect->getBinLogCurrent()
        );
    }

    private function filterDummyMariaDbEvents(QueryDTO $queryDTO): ?QueryDTO
    {
        if (BinLogServerInfo::isMariaDb() && strpos($queryDTO->getQuery(), self::MARIADB_DUMMY_QUERY) !== false) {
            return null;
        }

        return $queryDTO;
    }

    private function dispatch(EventDTO $eventDTO = null): void
    {
        if ($eventDTO !== null) {
            $this->eventDispatcher->dispatch($eventDTO, $eventDTO->getType());
        }
    }
}
