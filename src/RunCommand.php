<?php
namespace Cyclophp;

use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

/**
 * Консольная команда
 */
class RunCommand extends Command
{

    const COMMAND_NAME = 'run';
    const COMMAND_DESCRIPTION = 'Display list of public methods with cyclomatic complexity';

    const ARGUMENT_DIR = 'directory';
    const ARGUMENT_DIR_DESCRIPTION = 'Directory with source files';
    const ARGUMENT_DIR_DEFAULT = 'src';

    const OPTION_THRESHOLD = 'threshold';
    const OPTION_THRESHOLD_SHORTCUT = 't';
    const OPTION_THRESHOLD_DESCRIPTION = 'Minimum value of cyclomatic complexity';
    const OPTION_THRESHOLD_DEFAULT = 2;

    const OPTION_SORT_BY_NAME = 'by-name';
    const OPTION_SORT_BY_NAME_DESCRIPTION = 'Sort by name instead of by complexity';

    const RESULT_HEADERS = ['Method', 'Complexity'];

    /**
     * Конфигурирует команду
     *
     * @return void
     * @throws InvalidArgumentException
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setName(self::COMMAND_NAME)
            ->setDescription(self::COMMAND_DESCRIPTION)
            ->addArgument(
                self::ARGUMENT_DIR,
                InputArgument::OPTIONAL,
                self::ARGUMENT_DIR_DESCRIPTION,
                self::ARGUMENT_DIR_DEFAULT
            )
            ->addOption(
                self::OPTION_THRESHOLD,
                self::OPTION_THRESHOLD_SHORTCUT,
                InputOption::VALUE_OPTIONAL,
                self::OPTION_THRESHOLD_DESCRIPTION,
                self::OPTION_THRESHOLD_DEFAULT
            )
            ->addOption(
                self::OPTION_SORT_BY_NAME,
                null,
                InputOption::VALUE_NONE,
                self::OPTION_SORT_BY_NAME_DESCRIPTION
            );
    }

    /**
     * Выполняет команду
     *
     * @param InputInterface  $input  Интерфейс ввода
     * @param OutputInterface $output Интерфейс вывода
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Находим файлы
        $dir = $input->getArgument(self::ARGUMENT_DIR);
        $files = (new Finder())->files()->name('*.php')->in($dir);
        if ($output->isVerbose()) {
            $output->writeln(sprintf('Найдено файлов: %d', count($files)));
        }

        // Извлекаем методы из файлов
        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
        $methods = (new SourceExtractor($files, $parser, new Standard()))->extract();
        if ($output->isVerbose()) {
            $output->writeln(sprintf('Найдено методов: %d', count($methods)));
        }

        // Подсчитываем цикломатическую сложность
        (new ComplexityCounter())->count($methods);

        // Сортируем методы
        (new Sorter())->sort(
            $methods,
            $input->getOption(self::OPTION_SORT_BY_NAME)
                ? Sorter::BY_NAME
                : Sorter::BY_COMPLEXITY
        );

        $threshold = (int)$input->getOption(self::OPTION_THRESHOLD);

        $this->results($output, $methods, $threshold);
    }

    /**
     * Выводит результаты на экран
     *
     * @param OutputInterface $output    Интерфейс вывода
     * @param Method[]        $methods   Список методов
     * @param int             $threshold Пороговое значение цикломатической сложности
     *
     * @return void
     */
    protected function results(OutputInterface $output, $methods, $threshold)
    {
        $table = new Table($output);
        $table->setHeaders(self::RESULT_HEADERS);
        foreach ($methods as $method) {
            if ($method->complexity < $threshold) {
                continue;
            }

            $table->addRow([(string)$method, $method->complexity]);
        }

        $table->render();
    }

}
