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
     * @param $direction
     * @return array|null
     * @throws Exception
     */
    public function getList(string $direction = 'DESC'): ?array
    {
        if (!in_array($direction, ['ASC', 'DESC'])) {
            throw new Exception('Некорректная сортировка в SQL-запросе!');
        }
        $statement = $this->connection->prepare('SELECT * FROM post ORDER BY published_date ' . $direction);
        $statement->execute();

        return $statement->fetchAll();
    }
}