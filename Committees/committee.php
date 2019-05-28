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
use Gibbon\Tables\DataTable;
use Gibbon\Tables\View\GridView;
use Gibbon\Module\Committees\Domain\CommitteeGateway;
use Gibbon\Module\Committees\Domain\CommitteeMemberGateway;

if (isActionAccessible($guid, $connection2, '/modules/Committees/committee.php') == false) {
    // Access denied
    $page->addError(__('You do not have access to this action.'));
} else {
    // Proceed!
    $page->breadcrumbs
        ->add(__m('View Committees'), 'committees.php')
        ->add(__m('Committee'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $committeesCommitteeID = $_GET['committeesCommitteeID'] ?? '';

    $committeeMemberGateway = $container->get(CommitteeMemberGateway::class);

    $committee = $container->get(CommitteeGateway::class)->getByID($committeesCommitteeID);

    if (empty($committee)) {
        $page->addError(__('The specified record cannot be found.'));
        return;
    }

    echo '<h2>';
    echo $committee['name'];
    echo '</h2>';
    
    echo '<p>';
    echo $committee['description'];
    echo '</p>';

    // AVAILABLE SEATS
    $criteria = $committeeMemberGateway->newQueryCriteria()
        ->sortBy('committeesRole.name')
        ->fromPOST();
    $seats = $committeeMemberGateway->queryAvailableSeats($criteria, $committeesCommitteeID);

    if (count($seats) > 0) {
        $gridRenderer = new GridView($container->get('twig'));
        $table = $container->get(DataTable::class)->setRenderer($gridRenderer);

        $table->setTitle(__('Available Seats'));
        $table->setDescription(__('This committee has available seats. Click below to fill a seat.'));
        $table->addMetaData('gridClass', 'rounded-sm bg-gray-100 border py-2');
        $table->addMetaData('gridItemClass', 'w-1/2 sm:w-1/4 md:w-1/5 my-2 text-center');

        $table->addColumn('image_240')
            ->format(function ($role) {
                $availableSeats = intval($role['seats']) - $role['members'];

                $text = '<div class="badge right-0 -mr-4">'.$availableSeats.'</div>';
                $text .= '<img src="./themes/Default/img/attendance_large.png" class="w-16"><br/>';
                $text .= $role['role'];
                $url = 'Foo';
                return Format::link($url, $text, ['class' => 'inline-block relative text-gray-800 hover:text-blue-700']);
            });

        $table->addColumn('seats')
            ->setClass('text-xs text-gray-600 italic leading-loose')
            ->format(function ($role) {
                $availableSeats = intval($role['seats']) - $role['members'];
                return __n('{count} seat available', '{count} seats available', $availableSeats, ['total' => intval($role['seats'])]);
            });

        echo $table->render($seats);
    }

    // QUERY
    $criteria = $committeeMemberGateway->newQueryCriteria()->fromPOST();
    $members = $committeeMemberGateway->queryCommitteeMembers($criteria, $committeesCommitteeID);

    // GRID TABLE
    $gridRenderer = new GridView($container->get('twig'));
    $table = $container->get(DataTable::class)->setRenderer($gridRenderer);

    $table->setTitle(__('Members'));
    $table->addMetaData('gridClass', 'rounded-sm bg-blue-100 border py-2');
    $table->addMetaData('gridItemClass', 'w-1/2 sm:w-1/4 md:w-1/5 my-2 text-center');
    $table->addMetaData('blankSlate', __m('There are currently no members in this committee.'));

    if (isActionAccessible($guid, $connection2, '/modules/Committees/committees_manage_members.php')) {
        $table->addHeaderAction('edit', __('Edit'))
            ->addParam('gibbonSchoolYearID', $committee['gibbonSchoolYearID'])
            ->addParam('committeesCommitteeID', $committeesCommitteeID)
            ->setURL('/modules/Committees/committees_manage_members.php')
            ->displayLabel();
    }

    $table->addColumn('image_240')
        ->format(function ($person) {
            return Format::userPhoto($person['image_240'], 'sm', '');
        });

    $canViewProfile = isActionAccessible($guid, $connection2, '/modules/Staff/staff_view_details.php');
    $table->addColumn('name')
        ->setClass('text-xs font-bold mt-1')
        ->format(function ($person) use ($canViewProfile) {
            $name = Format::name($person['title'], $person['preferredName'], $person['surname'], 'Staff');
            $url = "./index.php?q=/modules/Staff/staff_view_details.php&gibbonPersonID=".$person['gibbonPersonID'];
            return $canViewProfile
                ? Format::link($url, $name)
                : $name;
        });

    $table->addColumn('role')
        ->setClass('text-xs text-gray-600 italic leading-snug');

    echo $table->render($members);
}
