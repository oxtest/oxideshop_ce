<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\EshopCommunity\Internal\Framework\Database\Logger;

use Doctrine\DBAL\Logging\SQLLogger;
use Psr\Log\LoggerInterface;
use OxidEsales\EshopCommunity\Internal\Transition\Utility\ContextInterface;
use OxidEsales\EshopCommunity\Internal\Transition\Utility\Exception\AdminUserNotFoundException;

class QueryLogger implements SQLLogger
{
    public function __construct(
        private QueryFilterInterface $queryFilter,
        private ContextInterface $context,
        private LoggerInterface $psrLogger
    ) {
    }

    /**
     * Logs an SQL statement somewhere.
     *
     * @param string              $query  The query to be executed.
     * @param mixed[]|null        $params The query parameters.
     * @param int[]|string[]|null $types  The query parameter types.
     *
     * @return void
     */
    public function startQuery($query, ?array $params = null, ?array $types = null): void
    {
        if ($this->filterPass($query)) {
            $queryData = $this->getQueryData($query, $params);
            $this->psrLogger->debug($this->getLogMessage($queryData));
        }
    }

    /**
     * Marks the last started query as stopped. This can be used for timing of queries.
     *
     * @return void
     */
    public function stopQuery(): void
    {
    }

    /**
     * Get first entry from backtrace that is not connected to database.
     * This has to be the origin of the query.
     *
     * @return array
     */
    private function getQueryTrace(): array
    {
        $queryTraceItem = [];

        foreach ((new \Exception())->getTrace() as $item) {
            if (
                (false === stripos($item['class'], $this::class)) &&
                (false === stripos($item['class'], 'Doctrine'))
            ) {
                $queryTraceItem = $item;
                break;
            }
        }

        return $queryTraceItem;
    }

    /**
     * Collect query information.
     *
     * @param string $query  The query to be executed.
     * @param array  $params The query parameters.
     *
     * @return array
     */
    private function getQueryData($query, array $params = null): array
    {
        $backTraceInfo = $this->getQueryTrace();

        return [
            'adminUserId' => $this->getAdminUserIdIfExists(),
            'shopId'      => $this->context->getCurrentShopId(),
            'class'       => $backTraceInfo['class'] ?? '',
            'function'    => $backTraceInfo['function'] ?? '',
            'file'        => $backTraceInfo['file'] ?? '',
            'line'        => $backTraceInfo['line'] ?? '',
            'query'       => $query,
            'params'      => serialize($params),
        ];
    }

    /**
     * @return bool
     */
    private function filterPass(string $query): bool
    {
        return $this->queryFilter->shouldLogQuery($query, $this->context->getSkipLogTags());
    }

    /**
     * Assemble log message
     *
     * @param array $queryData
     *
     * @return string
     */
    private function getLogMessage(array $queryData): string
    {
        $message = '';

        foreach ($queryData as $key => $value) {
            $message .= PHP_EOL . $key . ': ' . $value;
        }

        return $message . PHP_EOL;
    }

    /**
     * @return string
     */
    private function getAdminUserIdIfExists(): string
    {
        try {
            $adminId = $this->context->getAdminUserId();
        } catch (AdminUserNotFoundException) {
            $adminId = '';
        }

        return $adminId;
    }
}
