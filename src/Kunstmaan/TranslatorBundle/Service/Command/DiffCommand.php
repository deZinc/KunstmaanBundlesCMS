<?php

namespace Kunstmaan\TranslatorBundle\Service\Command;

use Doctrine\DBAL\Migrations\Configuration\Configuration;
use Doctrine\DBAL\Migrations\Tools\Console\Command\GenerateCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class DiffCommand.
 *
 * @deprecated This class is deprecated since TranslatorBundle 5.2 and will be removed in TranslatorBundle 6.0.
 *             This class will not work with doctrine/doctrine-migrations-bundle < 2.0 which the TranslatorBundle started depending on in 5.2
 */
class DiffCommand extends GenerateCommand
{
    protected function configure()
    {
        parent::configure();
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $configuration = $this->getMigrationConfiguration($input, $output);
        $sql = $this->getApplication()->getKernel()->getContainer()->get('kunstmaan_translator.service.migrations.migrations')->getDiffSqlArray();

        $up = $this->buildCodeFromSql($configuration, $sql);
        $down = '';

        if (!$up && !$down) {
            $output->writeln('No changes detected in your mapping information.', 'ERROR');

            return;
        }

        $version = date('YmdHis');
        $path = $this->generateMigration($configuration, $input, $version, $up, $down);

        $output->writeln(sprintf('Generated new migration class to "<info>%s</info>" from schema differences.', $path));
    }

    private function buildCodeFromSql(Configuration $configuration, array $sql)
    {
        $currentPlatform = $configuration->getConnection()->getDatabasePlatform()->getName();
        $code = array(
            "\$this->abortIf(\$this->connection->getDatabasePlatform()->getName() != \"$currentPlatform\", \"Migration can only be executed safely on '$currentPlatform'.\");", '',
        );
        foreach ($sql as $query) {
            if (strpos($query, $configuration->getMigrationsTableName()) !== false) {
                continue;
            }
            $code[] = "\$this->addSql(\"$query\");";
        }

        return implode("\n", $code);
    }
}
