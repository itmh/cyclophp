<?php
namespace Cyclophp;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Parser;
use PhpParser\PrettyPrinter\Standard;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Worker
 */
class SourceExtractor
{

    /**
     * Список файлов с исходными кодами
     *
     * @var SplFileInfo[]
     */
    private $files;

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
     * SourceExtractor constructor.
     *
     * @param SplFileInfo[] $files   Список файлов с исходными кодами
     * @param Parser        $parser  Парсер исходных кодов
     * @param Standard      $printer Принтер исходных кодов
     */
    public function __construct($files, Parser $parser, Standard $printer)
    {
        $this->files = $files;
        $this->parser = $parser;
        $this->printer = $printer;
        $this->context = new MethodContext('', '');
    }

    /**
     * Возвращает коллекцию методов
     *
     * @return Method[]
     */
    public function extract()
    {
        $methods = [];
        foreach ($this->files as $file) {
            $nodes = $this->parser->parse($file->getContents());
            $this->methods($nodes, $methods);
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
    private function methods(array $stmts, array &$methods)
    {
        if (count($stmts) === 0) {
            return;
        }

        foreach ($stmts as $stmt) {
            if ($stmt instanceof Namespace_) {
                $this->context->namespace = implode('\\', $stmt->name->parts);
            }

            if ($stmt instanceof Class_) {
                $this->context->class = $stmt->name;
            }

            if ($stmt instanceof ClassMethod) {
                $methods[] = new Method(
                    new MethodContext(
                        $this->context->namespace,
                        $this->context->class
                    ),
                    $stmt->name,
                    $this->printer->prettyPrint([$stmt])
                );
            }

            if (property_exists($stmt, 'stmts')) {
                $this->methods($stmt->stmts, $methods);
            }
        }
    }
}
