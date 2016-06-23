<?php

namespace Cyclophp;

/**
 * Модель метода
 */
class Method
{

    /**
     * Контекст
     *
     * @var MethodContext
     */
    public $context;

    /**
     * Имя метода
     *
     * @var string
     */
    public $name;

    /**
     * Тело метода
     *
     * @var string
     */
    public $body;

    /**
     * Значение цикломатической сложности
     *
     * @var int
     */
    public $complexity;

    /**
     * Конструктор
     *
     * @param MethodContext $context Контекст
     * @param string        $name    Имя метода
     * @param string        $body    Тело метода
     */
    public function __construct(MethodContext $context, $name, $body)
    {
        $this->context = $context;
        $this->name = $name;
        $this->body = $body;
    }

    /**
     * Возвращает строковое представление метода
     *
     * @return string
     */
    public function __toString()
    {
        return sprintf('%s::%s', (string)$this->context, $this->name);
    }
}
