<?php

declare(strict_types=1);
/**
 * @license  https://github.com/krowinski/php-mysql-replication/blob/master/LICENSE
 */
namespace MySQLReplication\Repository;

interface RepositoryInterface
{
    public function getFields(string $database, string $table): FieldDTOCollection;

    public function isCheckSum(): bool;

    public function getVersion(): string;

    public function getMasterStatus(): MasterStatusDTO;
}
