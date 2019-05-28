<?php
/*
Gibbon, Flexible & Open School System
Copyright (C) 2010, Ross Parker

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

namespace Gibbon\Module\Committees\Domain;

use Gibbon\Domain\Traits\TableAware;
use Gibbon\Domain\QueryCriteria;
use Gibbon\Domain\QueryableGateway;

class CommitteeRoleGateway extends QueryableGateway
{
    use TableAware;

    private static $tableName = 'committeesRole';
    private static $primaryKey = 'committeesRoleID';
    private static $searchableColumns = ['committeesRole.name'];
    
    /**
     * @param QueryCriteria $criteria
     * @return DataSet
     */
    public function queryRoles(QueryCriteria $criteria, $committeesCommitteeID)
    {
        $query = $this
            ->newQuery()
            ->from($this->getTableName())
            ->cols(['committeesRole.committeesRoleID', 'committeesRole.name', 'committeesRole.active', 'committeesRole.seats', 'committeesRole.selectable', 'COUNT(DISTINCT committeesMember.gibbonPersonID) as members'])
            ->innerJoin('committeesCommittee', 'committeesCommittee.committeesCommitteeID=committeesRole.committeesCommitteeID')
            ->leftJoin('committeesMember', 'committeesMember.committeesRoleID=committeesRole.committeesRoleID')
            ->where('committeesCommittee.committeesCommitteeID=:committeesCommitteeID')
            ->bindValue('committeesCommitteeID', $committeesCommitteeID)
            ->groupBy(['committeesRole.committeesRoleID']);

        return $this->runQuery($query, $criteria);
    }

    public function selectRoles($committeesCommitteeID)
    {
        $data = ['committeesCommitteeID' => $committeesCommitteeID];
        $sql = "SELECT committeesRoleID as value, name FROM committeesRole WHERE committeesCommitteeID=:committeesCommitteeID ORDER BY name";

        return $this->db()->select($sql, $data);
    }
}
