<?php

namespace AppGear\AppBundle\Security;

use AppGear\CoreBundle\Model\ModelManager;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class SecurityManager
{
    /**
     * Agreed value for class scope emulation object identifier
     */
    const CLASS_SCOPE_OBJECT_IDENTIFIER_VALUE = 'class';

    /**
     * @var ModelManager
     */
    protected $modelManager;

    /**
     * @var AuthorizationCheckerInterface
     */
    protected $checker;

    /**
     * SecurityManager constructor.
     *
     * @param AuthorizationCheckerInterface $checker
     * @param ModelManager                  $modelManager
     */
    public function __construct(AuthorizationCheckerInterface $checker, ModelManager $modelManager)
    {
        $this->modelManager = $modelManager;
        $this->checker      = $checker;
    }

    /**
     * Check if permission is granted to model
     *
     * @param string          $permission
     * @param string          $model
     * @param int|string|null $id
     *
     * @return bool
     */
    public function check(string $permission, string $model, $id = null): bool
    {
        $fqcn = $this->modelManager->fullClassName($model);
        $id   = $id ?? self::CLASS_SCOPE_OBJECT_IDENTIFIER_VALUE;

        $objectIdentity = new ObjectIdentity($id, $fqcn);

        return $this->checker->isGranted($permission, $objectIdentity);
    }
}