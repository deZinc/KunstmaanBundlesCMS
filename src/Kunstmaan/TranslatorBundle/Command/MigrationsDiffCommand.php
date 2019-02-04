<?php

namespace Kunstmaan\TranslatorBundle\Command;

use Doctrine\Bundle\DoctrineBundle\Command\Proxy\DoctrineCommandHelper;
use Doctrine\Bundle\MigrationsBundle\Command\DoctrineCommand;
use Doctrine\Migrations\Configuration\Configuration;
use Doctrine\Migrations\Tools\Console\Command\GenerateCommand;
use Kunstmaan\TranslatorBundle\Service\Migrations\MigrationsService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command for generate migration classes by checking the translation flag value
 *
 * @final since 5.1
 */
class MigrationsDiffCommand extends GenerateCommand
{
    /**
     * @var MigrationsService
     */
    private $migrationsService;

    public function __construct(MigrationsService $migrationsService)
    {
        $this->migrationsService = $migrationsService;
        parent::__construct(null);
    }

    protected function configure() : void
    {
        parent::configure();

        $this
            ->setName('kuma:translator:migrations:diff')
            ->addOption('em', null, InputOption::VALUE_OPTIONAL, 'The entity manager to use for this command.')
        ;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|null
     *
     * @throws \ErrorException
     * @throws \Doctrine\DBAL\DBALException
     */
    public function execute(InputInterface $input, OutputInterface $output) : ?int
    {
        DoctrineCommandHelper::setApplicationEntityManager($this->getApplication(), $input->getOption('em'));
        $configuration = $this->getMigrationConfiguration($input, $output);
        DoctrineCommand::configureMigrations($this->getApplication()->getKernel()->getContainer(), $configuration);

        $sql = $this->migrationsService->getDiffSqlArray();

        $up = $this->buildCodeFromSql($configuration, $sql);
        $down = '';

        if (!$up && !$down) {
            $output->writeln('No changes detected in your mapping information.', 'ERROR');

            return 0;
        }

        $version = date('YmdHis');
        $path = $this->generateMigration($configuration, $input, $version, $up, $down);

        $output->writeln(sprintf('Generated new migration class to "<info>%s</info>" from schema differences.', $path));

        return 0;
    }

    /**
     * @param Configuration $configuration
     * @param array         $sql
     *
     * @return string
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    private function buildCodeFromSql(Configuration $configuration, array $sql)
    {
        $currentPlatform = $configuration->getConnection()->getDatabasePlatform()->getName();
        $code = [
            "\$this->abortIf(\$this->connection->getDatabasePlatform()->getName() != \"$currentPlatform\", \"Migration can only be executed safely on '$currentPlatform'.\");",
            '',
        ];
        foreach ($sql as $query) {
            if (strpos($query, $configuration->getMigrationsTableName()) !== false) {
                continue;
            }
            $code[] = "\$this->addSql(\"$query\");";
        }

        return implode("\n", $code);
    }
}
