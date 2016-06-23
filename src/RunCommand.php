<?php
namespace Cyclophp;

use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Helper\ProgressBar;
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

    const OPTION_PUBLIC_ONLY = 'public-only';
    const OPTION_PUBLIC_ONLY_DESCRIPTION = 'Analyze only public methods';
    const OPTION_PUBLIC_ONLY_DEFAULT = 'yes';

    const OPTION_EXCLUDE = 'exclude';
    const OPTION_EXCLUDE_DESCRIPTION = 'Directory to exclude';
    const OPTION_EXCLUDE_DEFAULT = ['vendor'];

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
                (InputArgument::OPTIONAL | InputArgument::IS_ARRAY),
                self::ARGUMENT_DIR_DESCRIPTION,
                [self::ARGUMENT_DIR_DEFAULT]
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
            )
            ->addOption(
                self::OPTION_PUBLIC_ONLY,
                null,
                InputOption::VALUE_REQUIRED,
                self::OPTION_PUBLIC_ONLY_DESCRIPTION,
                self::OPTION_PUBLIC_ONLY_DEFAULT
            )
            ->addOption(
                self::OPTION_EXCLUDE,
                null,
                (InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY),
                self::OPTION_EXCLUDE_DESCRIPTION,
                self::OPTION_EXCLUDE_DEFAULT
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
        $exclude = $input->getOption(self::OPTION_EXCLUDE);
        $files = (new Finder())->files()->name('*.php')->in($dir)->exclude($exclude);

        // Извлекаем методы из файлов
        $progress = new ProgressBar($output, count($files));
        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
        $methods = (new SourceExtractor($parser, new Standard(), $progress))
            ->setMode($input->getOption(self::OPTION_PUBLIC_ONLY) !== 'no')
            ->extract($files);
        $progress->finish();
        $output->writeln('');

        // Подсчитываем цикломатическую сложность
        $progress = new ProgressBar($output, count($methods));
        $progress->start();
        (new ComplexityCounter($progress))->count($methods);
        $progress->finish();
        $output->writeln('');

        // Сортируем методы
        (new Sorter())->sort(
            $methods,
            $input->getOption(self::OPTION_SORT_BY_NAME)
                ? Sorter::BY_NAME
                : Sorter::BY_COMPLEXITY
        );

        $this->results($output, $methods, (int)$input->getOption(self::OPTION_THRESHOLD));
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
