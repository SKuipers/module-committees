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

if (isActionAccessible($guid, $connection2, '/modules/Committees/committees.php') == false) {
    // Access denied
    $page->addError(__('You do not have access to this action.'));
} else {
    // Proceed!
    $page->breadcrumbs->add(__m('View Committees'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $committeeGateway = $container->get(CommitteeGateway::class);

    // QUERY
    $criteria = $committeeGateway->newQueryCriteria()
        ->sortBy('name', 'ASC')
        ->filterBy('active', 'Y')
        ->fromPOST();

    $committees = $committeeGateway->queryCommittees($criteria, $_SESSION[$guid]['gibbonSchoolYearID']);

    // GRID TABLE
    $gridRenderer = new GridView($container->get('twig'));
    $table = $container->get(DataTable::class)->setRenderer($gridRenderer);
    $table->setTitle(__('Committees'));

    $table->addMetaData('gridClass', 'content-center justify-center');
    $table->addMetaData('gridItemClass', 'w-1/2 sm:w-1/3 text-center mb-4');

    $table->addColumn('logo')
        ->format(function ($committee) {
            $url = "./index.php?q=/modules/Committees/committee.php&committeesCommitteeID=".$committee['committeesCommitteeID'];
            $text = Format::userPhoto('themes/Default/img/attendance_large.png', 125, 'w-20 h-20 sm:w-32 sm:h-32 p-6');
            return Format::link($url, $text, ['class' => 'block']);
        });

    $table->addColumn('name')
        ->setClass('text-sm font-bold my-2')
        ->format(function ($committee) {
            $url = "./index.php?q=/modules/Committees/committee.php&committeesCommitteeID=".$committee['committeesCommitteeID'];
            return Format::link($url, $committee['name'], ['class' => '']);
        });

    echo $table->render($committees);
}
