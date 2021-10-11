<?php

namespace Aleksey\MyPhpBlog;

use Exception;
use PDO;

class PostMapper
{
    /**
     * @var PDO
     */
    private PDO $connection;

    /**
     * @param PDO $connection
     */
    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param string $urlKey
     * @return array|null
     */
    public function getByUrlKey(string $urlKey): ?array
    {
        $statement = $this->connection->prepare('SELECT * FROM post WHERE url_key = :url_key');
        $statement->execute([
            'url_key' => $urlKey,
        ]);
        $result = $statement->fetchAll();

        return array_shift($result);
    }

    /**
     * @param int $page
     * @param int $limit
     * @param string $direction
     * @return array|null
     * @throws Exception
     */
    public function getList(int $page = 1, int $limit = 2, string $direction = 'DESC'): ?array
    {
        if (!in_array($direction, ['ASC', 'DESC'])) {
            throw new Exception('Некорректная сортировка в SQL-запросе!');
        }

        $start = ($page - 1) * $limit;
        $statement = $this->connection->prepare(
            'SELECT * FROM post ORDER BY published_date ' . $direction . ' LIMIT ' . $start . ',' . $limit
        );
        $statement->execute();

        return $statement->fetchAll();
    }
}