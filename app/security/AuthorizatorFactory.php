<?php
namespace App\Security;

use App\Model\PermissionsManager;
use Nette;
use Nette\Security\Permission;

/**
 * @package App\Security
 */
class AuthorizatorFactory extends Nette\Object
{
    /**
     * Konstanty role
     */
    const ROLE_GUEST = 'guest';
    const ROLE_USER = 'user';

    /** @var PermissionsManager */
    public $permissionsManager;

    /**
     * AuthorizatorFactory constructor.
     * @param PermissionsManager $permissionsManager Automaticky injektovaná instace triedy modelu pre prácu s právami.
     */
    public function __construct(PermissionsManager $permissionsManager)
    {
        $this->permissionsManager = $permissionsManager;
    }

    /**
     * @return Permission
     */
    public function create()
    {
        $acl = new Permission;

        $roles = $this->permissionsManager->getRoles();

        /**
         * Roles
         */
        $acl->addRole(self::ROLE_GUEST);
        $acl->addRole(self::ROLE_USER, self::ROLE_GUEST);
        $acl->addRole('test', self::ROLE_USER);
/*        foreach ($roles as $role) {
            $acl->addRole($role->role, self::ROLE_USER);
        }*/

        /**
         * Resoures
         */
        /* Admin Module Main */
        $acl->addResource('Admin');
        $acl->addResource('Admin:News', 'Admin');
        $acl->addResource('Admin:Contact', 'Admin');
        $acl->addResource('Admin:Forum', 'Admin');
        $acl->addResource('Admin:Servers', 'Admin');
        $acl->addResource('Admin:Homepage', 'Admin');
        $acl->addResource('Admin:Users', 'Admin');
        $acl->addResource('Admin:Pages', 'Admin');

        /* Front Module Main */
        $acl->addResource('Front');
        $acl->addResource('Front:News', 'Front');
        $acl->addResource('Front:Homepage', 'Front');

        /**
         * Permissions
         */
/*        foreach ($roles as $perm) {
            $acl->allow($perm->role, explode(",", $perm->resource), explode(",", $perm->allow));
        }*/

        $acl->allow(Permission::ALL, Permission::ALL);
        return $acl;
    }

}