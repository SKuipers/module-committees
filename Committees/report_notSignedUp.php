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

use Gibbon\Services\Format;
use Gibbon\Tables\Prefab\ReportTable;
use Gibbon\Module\Committees\Domain\CommitteeMemberGateway;

if (isActionAccessible($guid, $connection2, '/modules/Committees/report_notSignedUp.php') == false) {
    // Access denied
    $page->addError(__('You do not have access to this action.'));
} else {
    // Proceed!
    $viewMode = $_REQUEST['format'] ?? '';

    $viewMode = $_REQUEST['format'] ?? '';

    if (empty($viewMode)) {
        $page->breadcrumbs->add(__m('Staff Not Signed-up'));

        if (isset($_GET['return'])) {
            returnProcess($guid, $_GET['return'], null, null);
        }
    }

    $committeeMemberGateway = $container->get(CommitteeMemberGateway::class);

    // QUERY
    $criteria = $committeeMemberGateway->newQueryCriteria()
        ->sortBy('committeesCommittee.name', 'ASC')
        ->sortBy(['gibbonPerson.surname', 'gibbonPerson.preferredName'])
        ->filterBy('active', 'Y')
        ->fromPOST();

    $members = $committeeMemberGateway->queryStaffWhoAreNotMembers($criteria, $gibbon->session->get('gibbonSchoolYearID'));

    // DATA TABLE
    $table = ReportTable::createPaginated('committeeReport', $criteria)->setViewMode($viewMode, $gibbon->session);
    $table->setTitle(__('Staff Not Signed-up'));

    $table->addColumn('fullName', __('Name'))
        ->width('40%')
        ->sortable(['gibbonPerson.surname', 'gibbonPerson.preferredName'])
        ->format(function ($person) {
            return Format::name($person['title'], $person['preferredName'], $person['surname'], 'Staff', true, true);
        });

    $table->addColumn('jobTitle', __('Job Title'))
        ->format(function ($person) {
            return !empty($person['jobTitle']) ? $person['jobTitle'] : $person['type'];
        });

    echo $table->render($members);
}
