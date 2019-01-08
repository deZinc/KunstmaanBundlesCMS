<?php

namespace Kunstmaan\GeneratorBundle\Helper;

/**
 * Class EntityRepositoryGenerator.
 */
class EntityRepositoryGenerator
{
    protected static $_template =
        '<?php

namespace <namespace>;

use <entityClassName>;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

/**
 * class <repository>.
 */
class <repository>
{
    /**
     * @var EntityRepository
     */
    private $repository;

    public function __construct(EntityManager $entityManager)
    {
        $this->repository = $entityManager->getRepository(<entity>::class);
    }
}
';

    /**
     * @param string $entityClassName
     * @param string $repositoryClassName
     *
     * @return string
     */
    public function generateEntityRepositoryClass(string $entityClassName, string $repositoryClassName)
    {
        $variables = [
            '<namespace>'       => $this->generateEntityRepositoryNamespace($repositoryClassName),
            '<entityClassName>' => $entityClassName,
            '<entity>'          => $this->generateEntityName($entityClassName),
            '<repository>'      => $this->generateRepositoryName($repositoryClassName),
        ];

        return str_replace(array_keys($variables), array_values($variables), self::$_template);
    }

    /**
     * @param string $entityClassName
     * @param string $repositoryClassName
     * @param string $path
     *
     * @return void
     */
    public function writeEntityRepositoryClass(string $entityClassName, string $repositoryClassName, string $path)
    {
        $code = $this->generateEntityRepositoryClass($entityClassName, $repositoryClassName);

        $dir = dirname($path);

        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        if (!file_exists($path)) {
            file_put_contents($path, $code);
            chmod($path, 0664);
        }
    }

    /**
     * @param string $repositoryClassName
     *
     * @return bool|string
     */
    private function generateEntityRepositoryNamespace(string $repositoryClassName)
    {
        return substr($repositoryClassName, 0, strrpos( $repositoryClassName, '\\'));
    }

    /**
     * @param string $entityClassName
     *
     * @return bool|string
     */
    private function generateEntityName(string $entityClassName)
    {
        return substr(strrchr($entityClassName, '\\'), 1 );
    }

    /**
     * @param string $entityClassName
     *
     * @return bool|string
     */
    private function generateRepositoryName(string $repositoryClassName)
    {
        return substr(strrchr($repositoryClassName, '\\'), 1 );
    }
}