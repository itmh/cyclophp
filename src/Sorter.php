<?php

namespace Cyclophp;

/**
 * Класс для сортировки списка методов по одному из доступных алгоритмов
 */
class Sorter
{

    const BY_NAME = 'BY_NAME';
    const BY_COMPLEXITY = 'BY_COMPLEXITY';
    const SORTS = [
        self::BY_COMPLEXITY,
        self::BY_NAME
    ];

    /**
     * Сортирует список методов по указанному алгоритму
     *
     * @param Method[] $methods Список методов
     * @param string   $sort    Алгоритм сортировки
     *
     * @return void
     */
    public function sort(array &$methods, $sort = self::BY_COMPLEXITY)
    {
        if (!in_array($sort, self::SORTS, true)) {
            $sort = self::BY_COMPLEXITY;
        }

        usort(
            $methods,
            $sort === self::BY_COMPLEXITY
                ? $this->byComplexity()
                : $this->byName()
        );
    }

    /**
     * Возвращает функцию сортировки по значению цикломатической сложности
     *
     * @return \Closure
     */
    private function byComplexity()
    {
        return function (Method $a, Method $b) {
            return strcmp($b->complexity, $a->complexity);
        };
    }

    /**
     * Возвращает функцию сортировки по имени метода
     *
     * @return \Closure
     */
    private function byName()
    {
        return function (Method $a, Method $b) {
            return strcmp($a->name, $b->name);
        };
    }
}
