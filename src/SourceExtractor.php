<?php
namespace Cyclophp;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Parser;
use PhpParser\PrettyPrinter\Standard;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Worker
 */
class SourceExtractor
{

    /**
     * Парсер исходных кодов
     *
     * @var Parser
     */
    private $parser;

    /**
     * Принтер исходных кодов
     *
     * @var Standard
     */
    private $printer;

    /**
     * Контекст обрабатываемого файла
     *
     * @var MethodContext
     */
    private $context;

    /**
     * Флаг обработки только публичных методов
     *
     * @var bool
     */
    private $isPublicOnly = true;

    /**
     * Конструктор
     *
     * @param Parser      $parser      Парсер исходных кодов
     * @param Standard    $printer     Принтер исходных кодов
     * @param ProgressBar $progressBar Индикатор прогресса
     */
    public function __construct(Parser $parser, Standard $printer, ProgressBar $progressBar)
    {
        $this->parser = $parser;
        $this->printer = $printer;
        $this->context = new MethodContext('', '');
        $this->progressBar = $progressBar;
    }

    /**
     * Устанафливает флаг обработки только публичных методов
     *
     * @param bool $isPublicOnly Флаг обработки только публичных методов
     *
     * @return SourceExtractor
     */
    public function setMode($isPublicOnly)
    {
        $this->isPublicOnly = $isPublicOnly;

        return $this;
    }

    /**
     * Возвращает коллекцию методов
     *
     * @param SplFileInfo[] $files Список файлов с исходными кодами
     *
     * @return Method[]
     */
    public function extract($files)
    {
        $methods = [];
        foreach ($files as $file) {
            $nodes = $this->parser->parse($file->getContents());
            if ($nodes === null) {
                error_log($file . ' does not contains any node');
                continue;
            }

            $this->parse($nodes, $methods);
            $this->progress();
        }

        return $methods;
    }

    /**
     * Рекурсивно извлекает информацию о методах из структуры файла
     *
     * @param Node[]   $stmts   Список выражений
     * @param Method[] $methods Список методов
     *
     * @return void
     */
    private function parse(array $stmts, array &$methods)
    {
        foreach ($stmts as $stmt) {
            $this->extractNamespace($stmt);
            $this->extractClassName($stmt);
            $this->extractMethod($stmt, $methods);

            if (property_exists($stmt, 'stmts') && $stmt->stmts !== null) {
                $this->parse($stmt->stmts, $methods);
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

    /**
     * Извлекает пространство имён из текущего узла
     *
     * @param Node $stmt Узел
     *
     * @return void
     */
    private function extractNamespace($stmt)
    {
        if ($stmt instanceof Namespace_) {
            $this->context->namespace = implode('\\', $stmt->name->parts);
        }
    }

    /**
     * Извлекает имя класса из текущего узла
     *
     * @param Node $stmt Узел
     *
     * @return void
     */
    private function extractClassName($stmt)
    {
        if ($stmt instanceof Class_) {
            $this->context->class = $stmt->name;
        }
    }

    /**
     * Ищвлекает тело метода из текущего узла
     *
     * @param Node|ClassMethod $stmt    Узел
     * @param Method[]         $methods Список методов
     *
     * @return void
     */
    private function extractMethod($stmt, array &$methods)
    {
        if (!($stmt instanceof ClassMethod)) {
            return;
        }

        $skip = $this->isPublicOnly && $stmt->isPublic() === false;

        if (!$skip) {
            $methods[] = new Method(
                new MethodContext($this->context->namespace, $this->context->class),
                $stmt->name,
                $this->printer->prettyPrint([$stmt])
            );
        }
    }
}
