<?php
namespace Cyclophp;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Консольная команда
 */
class RunCommand extends Command
{

    /**
     * Имя команды
     */
    const NAME = 'run';

    /**
     * Краткое описание команды
     */
    const DESCRIPTION = 'Display list of public methods with cyclomatic complexity';

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
            ->setName(self::NAME)
            ->setDescription(self::DESCRIPTION)
            ->addArgument(
                'dir',
                InputArgument::IS_ARRAY,
                'Directory'
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
        $output->writeln('Cyclophp');
    }

}
