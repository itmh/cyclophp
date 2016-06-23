<?php
namespace Cyclophp;

/**
 * Модель контекста метода
 */
class MethodContext
{

    /**
     * Пространство имён
     *
     * @var string
     */
    public $namespace;

    /**
     * Имя класса
     *
     * @var string
     */
    public $class;

    /**
     * Конструктор
     *
     * @param string $namespace Пространство имён
     * @param string $class     Имя класса
     */
    public function __construct($namespace, $class)
    {
        $this->namespace = $namespace;
        $this->class = $class;
    }

    /**
     * Возвращает строковое представление контекста
     *
     * @return string
     */
    public function __toString()
    {
        return sprintf('%s\%s', $this->namespace, $this->class);
    }
}
