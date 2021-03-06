<?php

namespace AppGear\AppBundle\Security;

use AppGear\CoreBundle\Model\ModelManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Voter\FieldVote;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class SecurityManager
{
    /**
     * Agreed value for class scope emulation object identifier
     */
    const CLASS_SCOPE_OBJECT_IDENTIFIER_VALUE = 'class';

    /**
     * @var AuthorizationCheckerInterface
     */
    protected $checker;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var ModelManager
     */
    protected $modelManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * SecurityManager constructor.
     *
     * @param AuthorizationCheckerInterface $checker
     * @param TokenStorageInterface         $tokenStorage
     * @param ModelManager                  $modelManager
     * @param LoggerInterface               $logger
     */
    public function __construct(AuthorizationCheckerInterface $checker,
                                TokenStorageInterface $tokenStorage,
                                ModelManager $modelManager,
                                LoggerInterface $logger)
    {
        $this->modelManager = $modelManager;
        $this->checker      = $checker;
        $this->tokenStorage = $tokenStorage;
        $this->logger       = $logger;
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
    public function check(string $permission, string $model = null, $id = null, $field = null): bool
    {
        if (null === $model) {
            return false;
        }

        $fqcn = $this->modelManager->fullClassName($model);
        $id   = $id ?? self::CLASS_SCOPE_OBJECT_IDENTIFIER_VALUE;

        // Check access to object
        $objectIdentity = new ObjectIdentity($id, $fqcn);
        $isGranted      = $this->checker->isGranted($permission, $objectIdentity);

        if ($isGranted) {
            return true;
        }

        // Check access to field
        if ($field !== null) {
            $fieldVote = new FieldVote($objectIdentity, $field);
            $isGranted = $this->checker->isGranted($permission, $fieldVote);

            if ($isGranted) {
                return true;
            }
        }

        // Log access denied
        $token    = $this->tokenStorage->getToken();
        $username = ($token !== null) ? $token->getUsername() : null;
        $this->logger->notice('Access denied', compact('username', 'permission', 'model', 'id'));

        return false;
    }
}