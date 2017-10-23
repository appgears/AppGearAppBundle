<?php

namespace AppGear\AppBundle\Security;

use AppGear\CoreBundle\Model\ModelManager;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

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

    public function checkModelAccess(string $permission, string $model)
    {
        if (!$this->isModelGranted($permission, $model)) {
            throw new AccessDeniedException();
        }
    }

    /**
     * Check if permission is granted to model
     *
     * @param string $permission
     * @param string $model
     *
     * @return bool
     */
    public function check(string $permission, string $model): bool
    {
        $fqcn  = $this->modelManager->fullClassName($model);

        $objectIdentity = new ObjectIdentity(
            self::CLASS_SCOPE_OBJECT_IDENTIFIER_VALUE,
            $fqcn
        );

        return $this->checker->isGranted($permission, $objectIdentity);
    }
}