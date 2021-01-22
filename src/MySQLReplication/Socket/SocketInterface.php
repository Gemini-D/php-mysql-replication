<?php

declare(strict_types=1);
/**
 * @license  https://github.com/krowinski/php-mysql-replication/blob/master/LICENSE
 */
namespace MySQLReplication\Socket;

interface SocketInterface
{
    public function isConnected(): bool;

    /**
     * @throws SocketException
     */
    public function connectToStream(string $host, int $port): void;

    /**
     * @throws SocketException
     */
    public function readFromSocket(int $length): string;

    /**
     * @throws SocketException
     */
    public function writeToSocket(string $data): void;
}
