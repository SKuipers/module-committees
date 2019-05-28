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

class CommitteeGateway extends QueryableGateway
{
    use TableAware;

    private static $tableName = 'committeesCommittee';
    private static $primaryKey = 'committeesCommitteeID';
    private static $searchableColumns = ['committeesCommittee.name'];
    
    /**
     * @param QueryCriteria $criteria
     * @return DataSet
     */
    public function queryCommittees(QueryCriteria $criteria, $gibbonSchoolYearID)
    {
        $query = $this
            ->newQuery()
            ->from($this->getTableName())
            ->cols(['committeesCommittee.committeesCommitteeID', 'committeesCommittee.name', 'committeesCommittee.description', 'committeesCommittee.active', 'COUNT(DISTINCT committeesMember.gibbonPersonID) as members'])
            ->leftJoin('committeesMember', 'committeesMember.committeesCommitteeID=committeesCommittee.committeesCommitteeID')
            ->where('committeesCommittee.gibbonSchoolYearID=:gibbonSchoolYearID')
            ->bindValue('gibbonSchoolYearID', $gibbonSchoolYearID)
            ->groupBy(['committeesCommittee.committeesCommitteeID']);

        $criteria->addFilterRules([
            'active' => function ($query, $active) {
                return $query
                    ->where('committeesCommittee.active = :active')
                    ->bindValue('active', $active);
            },
        ]);

        return $this->runQuery($query, $criteria);
    }
}
