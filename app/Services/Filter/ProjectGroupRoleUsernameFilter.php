<?php

/*
 * This file is part of Hiject.
 *
 * Copyright (C) 2016 Hiject Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hiject\Filter;

use Hiject\Core\Filter\FilterInterface;
use Hiject\Model\GroupMemberModel;
use Hiject\Model\ProjectGroupRoleModel;
use Hiject\Model\UserModel;

/**
 * Filter ProjectGroupRole users by username.
 */
class ProjectGroupRoleUsernameFilter extends BaseFilter implements FilterInterface
{
    /**
     * Get search attribute.
     *
     * @return string[]
     */
    public function getAttributes()
    {
        return [];
    }

    /**
     * Apply filter.
     *
     * @return FilterInterface
     */
    public function apply()
    {
        $this->query
            ->join(GroupMemberModel::TABLE, 'group_id', 'group_id', ProjectGroupRoleModel::TABLE)
            ->join(UserModel::TABLE, 'id', 'user_id', GroupMemberModel::TABLE)
            ->ilike(UserModel::TABLE.'.username', $this->value.'%');

        return $this;
    }
}
