<?php

namespace AppGear\AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Exception\InvalidOptionException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Exception\AclNotFoundException;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class AclSetClassFieldScopeCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('appgear:app:acl:set:class-field-scope')
            ->setDescription("Setup ACLs and ACEs for Class-Field-Scope access demo.\nExample:\nappgear:app:acl:set:class-field-scope --fqcn=\"Commerce\PlatformBundle\Entity\Shop\Order\" --field=\"status\" --permission=\"VIEW\" --username=\"khz\" --user-provider=\"in_memory\"")
            ->addOption('fqcn', null, InputOption::VALUE_REQUIRED, 'Target entity FQCN')
            ->addOption('field', null, InputOption::VALUE_REQUIRED, 'Target entity field name')
            ->addOption('permission', null, InputOption::VALUE_REQUIRED, 'Permission (VIEW, C - CREATE, E - EDIT, D - DELETE etc. See all codes in the MaskBuilder class)')
            ->addOption('username', null, InputOption::VALUE_REQUIRED, 'User name')
            ->addOption('user-provider', null, InputOption::VALUE_REQUIRED, 'User provider (in_memory etc.)');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $fqcn         = $input->getOption('fqcn');
        $field        = $input->getOption('field');
        $userName     = $input->getOption('username');
        $permission   = $input->getOption('permission');
        $userProvider = $input->getOption('user-provider');

        if (!$fqcn) {
            throw new InvalidOptionException('Set "fqcn" option');
        }
        if (!$field) {
            throw new InvalidOptionException('Set "field" option');
        }
        if (!$userName) {
            throw new InvalidOptionException('Set "username" option');
        }
        if (!$permission) {
            throw new InvalidOptionException('Set "permission" option');
        }
        if (!$userProvider) {
            throw new InvalidOptionException('Set "user-provider" option');
        }

        $aclProvider = $this->getContainer()->get('security.acl.provider');

        $oid = new ObjectIdentity('class', $fqcn);
        
        try {
            $acl = $aclProvider->findAcl($oid);
        } catch (AclNotFoundException $e) {
            $acl = $aclProvider->createAcl($oid);
        }

        /** @var UserProviderInterface $securityUserProvider */
        $securityUserProvider = $this->getContainer()->get('security.user.provider.concrete.' . $userProvider);
        $user                 = $securityUserProvider->loadUserByUsername($userName);
        $securityIdentity     = UserSecurityIdentity::fromAccount($user);

        $maskBuilder = new MaskBuilder();
        $mask        = $maskBuilder->resolveMask($permission);

        $acl->insertClassFieldAce($field, $securityIdentity, $mask);
        $aclProvider->updateAcl($acl);

        return 0;
    }
}