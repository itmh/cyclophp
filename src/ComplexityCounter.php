<?php

namespace Cyclophp;

use Symfony\Component\Console\Helper\ProgressBar;

/**
 * Класс для подсчёта цикломатической сложности
 */
class ComplexityCounter
{

    const PHP_FILE_TEMPLATE = '<?php%s%s';

    /**
     * Базовое значение цикломатической сложности.
     */
    const BASE_VALUE = 1;
    /**
     * Список токенов, увеличивающих значение цикломатической сложности.
     *
     * @var int[]
     */
    const TOKENS = [
        T_IF,
        T_ELSEIF,
        T_FOR,
        T_FOREACH,
        T_WHILE,
        T_CASE,
        T_CATCH,
        T_BOOLEAN_AND,
        T_LOGICAL_AND,
        T_BOOLEAN_OR,
        T_LOGICAL_OR
    ];

    /**
     * Индикатор прогресса
     *
     * @var ProgressBar
     */
    private $progressBar;

    /**
     * Конструктор
     *
     * @param ProgressBar $progressBar Индикатор прогресса
     */
    public function __construct(ProgressBar $progressBar)
    {
        $this->progressBar = $progressBar;
    }

    /**
     * Вычисляет цикломатическую сложность для каждого метода
     *
     * @param Method[] $methods Список методов
     *
     * @return void
     */
    public function count(array &$methods)
    {
        foreach ($methods as $method) {
            $this->method($method);
            $this->progress();
        }
    }

    /**
     * Вычисляет цикломатическую сложность метода
     *
     * @param Method $method Метод
     *
     * @return void
     */
    private function method(Method $method)
    {
        $source = sprintf(self::PHP_FILE_TEMPLATE, PHP_EOL, $method->body);
        $tokens = token_get_all($source);

        $method->complexity = self::BASE_VALUE;
        foreach ($tokens as $token) {
            list($token) = $token;
            if (in_array($token, self::TOKENS, true)) {
                $method->complexity++;
            }
        }
    }

    /**
     * Инкременирует прогресс
     *
     * @return void
     */
    private function progress()
    {
        $this->progressBar->advance();
    }
}
