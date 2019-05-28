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

use Gibbon\Module\Committees\Domain\CommitteeGateway;

require_once '../../gibbon.php';

$committeesCommitteeID = $_GET['committeesCommitteeID'] ?? '';
$gibbonSchoolYearID = $_REQUEST['gibbonSchoolYearID'] ?? $_SESSION[$guid]['gibbonSchoolYearID'];

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Committees/committees_manage.php&gibbonSchoolYearID='.$gibbonSchoolYearID;

if (isActionAccessible($guid, $connection2, '/modules/Committees/committees_manage_delete.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} elseif (empty($committeesCommitteeID)) {
    $URL .= '&return=error1';
    header("Location: {$URL}");
    exit;
} else {
    // Proceed!
    $committeeGateway = $container->get(CommitteeGateway::class);
    $values = $committeeGateway->getByID($committeesCommitteeID);

    if (empty($values)) {
        $URL .= '&return=error2';
        header("Location: {$URL}");
        exit;
    }

    $deleted = $committeeGateway->delete($committeesCommitteeID);

    $URL .= !$deleted
        ? '&return=error2'
        : '&return=success0';

    header("Location: {$URL}");
}
