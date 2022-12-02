<?php
/**
 * Created by PhpStorm.
 * User: José Lucio
 * Date: 24/10/16
 * Time: 18:25
 */

namespace Admin\Service;



use Zend\Debug\Debug;
use Zend\Permissions\Acl\Acl as AclPermissions;
use Zend\Permissions\Acl\Resource\GenericResource;
use Zend\Permissions\Acl\Role\GenericRole;

class Acl extends AclPermissions
{
    public function __construct(array $roles = null, array  $resources = null, $permissions = null)
    {
        $this->addRole('admin');
        $this->addRole('anonymous') ;

        // set default politic
        $this->deny();

        // allow all adm
        $this->allow('admin');

        //  adiciona os papis
        $this->addRoles($roles);

        // adiciona os recursos
        $this->addResources($resources);

        // adiciona as acls
        $this->addPermissions($permissions);
    }

    private function addRoles($roles)
    {

        foreach ($roles as $role) {
            $parent = null;
            if (!empty($role['parent'])) {
                $parent = $role['parent'];
            }
            $this->addRole(new GenericRole($role['name']), $parent);
        }
    }

    private function addResources($resources)
    {
        foreach ($resources as $resource) {
            $parent = null;
            if (!empty($resource['parent'])) {
                $parent = $resource['parent'];
            }

            $this->addResource(new GenericResource($resource['name']), $parent);
        }
    }

    private function addPermissions($permissions)
    {
        foreach ($permissions as $permission) {
            if ($permission['allow']) {
                $this->allow($permission['roles'], $permission['resources'], $permission['privileges']);
            } else {
                $this->deny($permission['roles'], $permission['resources'], $permission['privileges']);
            }
        }
    }
}
