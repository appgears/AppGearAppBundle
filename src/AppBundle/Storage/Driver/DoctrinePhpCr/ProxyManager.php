<?php

namespace AppGear\AppBundle\Storage\Driver\DoctrinePhpCr;

use AppGear\AppBundle\Storage\ProxyManagerAbstract;
use AppGear\CoreBundle\Entity\Model;

class ProxyManager extends ProxyManagerAbstract
{
    /**
     * {@inheritdoc}
     */
    protected function buildProxyClassFile(Model $model, $path)
    {
        $modelName     = $model->getName();
        $className     = $this->manager->className($modelName);
        $fullClassName = $this->manager->fullClassName($modelName);
        $scope         = $this->manager->scope($modelName);
        $proxyScope    = 'AppGearStorageProxy\\' . $scope;
        $content       = <<<EOT
<?php

namespace $proxyScope;

class $className extends \\$fullClassName
{
    /**
     * Document ID
     */
    protected \$id;

    /**
     * Parent document reference
     */
    protected \$parentDocument;

    /**
     * Get document ID
     *
     * @return mixed
     */
    public function getId()
    {
        return \$this->id;
    }

    /**
     * Set document ID
     *
     * @param mixed \$id Document ID
     */
    public function setId(\$id)
    {
        \$this->id = \$id;
    }

    /**
     * Get parent document reference
     *
     * @return object
     */
    public function getParentDocument()
    {
        return \$this->parentDocument;
    }

    /**
     * Set parent document reference
     *
     * @param object \$parentDocument Parent document reference
     */
    public function setParentDocument(\$parentDocument)
    {
        \$this->parentDocument = \$parentDocument;
    }
}
EOT;
        mkdir(dirname($path), 0777, true);
        file_put_contents($path, $content);
    }
}
