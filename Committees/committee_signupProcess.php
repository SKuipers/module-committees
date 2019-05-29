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

use Gibbon\Module\Committees\Domain\CommitteeMemberGateway;

require_once '../../gibbon.php';

$committeesCommitteeID = $_POST['committeesCommitteeID'] ?? '';

$URL = $gibbon->session->get('absoluteURL').'/index.php?q=/modules/Committees/committee.php&committeesCommitteeID='.$committeesCommitteeID;

if (isActionAccessible($guid, $connection2, '/modules/Committees/committee_signup.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
    exit;
} else {
    // Proceed!
    $committeeMemberGateway = $container->get(CommitteeMemberGateway::class);

    $data = [
        'committeesCommitteeID' => $committeesCommitteeID,
        'committeesRoleID'      => $_POST['committeesRoleID'] ?? '',
        'gibbonPersonID'        => $gibbon->session->get('gibbonPersonID'),
    ];

    // Validate the required values are present
    if (empty($data['committeesCommitteeID']) || empty($data['committeesRoleID']) || empty($data['gibbonPersonID'])) {
        $URL .= '&return=error1';
        header("Location: {$URL}");
        exit;
    }

    // Validate that this record is unique
    if (!$committeeMemberGateway->unique($data, ['gibbonPersonID', 'committeesCommitteeID'])) {
        $URL .= '&return=error7';
        header("Location: {$URL}");
        exit;
    }

    // Create the record
    $committeesMemberID = $committeeMemberGateway->insert($data);

    $URL .= !$committeesMemberID
        ? "&return=error2"
        : "&return=success0";

    header("Location: {$URL}");
}
