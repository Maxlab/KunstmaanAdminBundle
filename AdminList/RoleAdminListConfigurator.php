<?php

namespace Kunstmaan\AdminBundle\AdminList;

use Kunstmaan\AdminBundle\Entity\Role;
use Kunstmaan\AdminListBundle\AdminList\FilterType\ORM\StringFilterType;

/**
 * Role admin list configurator used to manage {@link Role} in the admin
 */
class RoleAdminListConfigurator extends AbstractSettingsAdminListConfigurator
{

    /**
     * Build filters for admin list
     */
    public function buildFilters()
    {
        $this->getFilterBuilder()->add('role', new StringFilterType('role'), 'Role');
    }

    /**
     * Configure the visible columns
     */
    public function buildFields()
    {
        $this->addField('role', 'Role', true);
    }

    /**
     * Get repository name
     *
     * @return string
     */
    public function getEntityName()
    {
        return 'Role';
    }

}
