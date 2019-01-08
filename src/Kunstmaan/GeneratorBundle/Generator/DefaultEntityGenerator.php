<?php

namespace Kunstmaan\GeneratorBundle\Generator;

use Kunstmaan\AdminBundle\Entity\AbstractEntity;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

/**
 * DefaultEntityGenerator
 */
class DefaultEntityGenerator extends KunstmaanGenerator
{
    /**
     * @var string
     */
    private $namespace;

    /**
     * @var string
     */
    private $entity;

    /**
     * @var string
     */
    private $prefix;

    /**
     * @var array
     */
    private $fields;

    /**
     * @param string          $namespace            The entity namespace
     * @param string          $entity               The entity name
     * @param string          $prefix               The database prefix
     * @param array           $fields               The fields
     * @param bool            $withRepository
     * @param null|string     $repositoryNamespace
     */
    public function generate(string $namespace, $entity, $prefix, array $fields, $withRepository = false, $repositoryNamespace = null)
    {
        $this->namespace = $namespace;
        $this->entity = $entity;
        $this->prefix = $prefix;
        $this->fields = $fields;

        list($entityCode, $entityPath) = $this->generateEntity(
            $this->namespace,
            $this->entity,
            $this->fields,
            '',
            $this->prefix,
            AbstractEntity::class,
            $withRepository,
            $repositoryNamespace
        );

        $pos = strrpos($entityCode, '}');
        $trimmed = substr($entityCode, 0, $pos);
        $entityCode = $trimmed . "\n}";

        // Write class to filesystem
        $this->filesystem->mkdir(dirname($entityPath));
        file_put_contents($entityPath, $entityCode);

        $this->assistant->writeLine('Generating entity : <info>OK</info>');
    }
}
