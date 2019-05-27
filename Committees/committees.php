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

    $committees = $committeeGateway->queryCommittees($criteria);

    // GRID TABLE
    $gridRenderer = new GridView($container->get('twig'));
    $table = $container->get(DataTable::class)->setRenderer($gridRenderer);
    $table->setTitle(__('Committees'));

    $table->addColumn('logo')
        ->format(function ($committee) {
            return Format::userPhoto($committee['logo'] ?? '', 125, 'w-20 h-20 sm:w-32 sm:h-32 p-1');
        });

    $table->addColumn('name')
        ->setClass('text-xs font-bold mt-1 mb-4')
        ->format(function ($committee) {
            $url = "./index.php?q=/modules/Committees/committee.php&committeesCommitteeID=".$committee['committeesCommitteeID'];
            return Format::link($url, $committee['name']);
        });

    echo $table->render($committees);
}
