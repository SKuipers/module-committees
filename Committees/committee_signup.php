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

use Gibbon\Forms\Form;
use Gibbon\Module\Committees\Domain\CommitteeGateway;
use Gibbon\Module\Committees\Domain\CommitteeRoleGateway;
use Gibbon\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/Committees/committee_signup.php') == false) {
    // Access denied
    $page->addError(__('You do not have access to this action.'));
} else {
    // Proceed!
    $committeesCommitteeID = $_GET['committeesCommitteeID'] ?? '';
    $committeesRoleID = $_GET['committeesRoleID'] ?? '';

    $page->breadcrumbs
        ->add(__m('View Committees'), 'committees.php')
        ->add(__m('Committee'), 'committee.php', ['committeesCommitteeID' => $committeesCommitteeID])
        ->add(__m('Sign-up'));

    if (empty($committeesCommitteeID) || empty($committeesRoleID)) {
        $page->addError(__('You have not specified one or more required parameters.'));
        return;
    }

    $committeeRoleGateway = $container->get(CommitteeRoleGateway::class);

    $committee = $container->get(CommitteeGateway::class)->getByID($committeesCommitteeID);
    $role = $committeeRoleGateway->getByID($committeesRoleID);

    if (empty($committee) || empty($role)) {
        $page->addError(__('The specified record cannot be found.'));
        return;
    }

    $signupActive = getSettingByScope($connection2, 'Committees', 'signupActive');

    if ($signupActive != 'Y' || $committee['register'] != 'Y') {
        $page->addError(__('This committee is not available for sign-up.'));
        return;
    }

    $roleCount = $committeeRoleGateway->getRoleCountByPerson($gibbon->session->get('gibbonSchoolYearID'), $gibbon->session->get('gibbonPersonID'));
    if ($roleCount >= 1) {
        $page->addMessage(__('You have already signed-up for the maximum number of committees.'));
        return;
    }

    $memberCount = $committeeRoleGateway->getMemberCountByRole($committeesRoleID);
    $availableSeats = intval($role['seats']) - $memberCount;

    if ($role['selectable'] != 'Y' || $availableSeats <= 0) {
        $page->addMessage(__('There are currently no seats available for this role.'));
        return;
    }

    $form = Form::create('committeesSignup', $gibbon->session->get('absoluteURL').'/modules/Committees/committee_signupProcess.php');
    $form->setFactory(DatabaseFormFactory::create($pdo));
    
    $form->addHiddenValue('address', $gibbon->session->get('address'));
    $form->addHiddenValue('committeesCommitteeID', $committeesCommitteeID);
    $form->addHiddenValue('committeesRoleID', $committeesRoleID);

    $row = $form->addRow();
        $row->addLabel('gibbonPersonIDLabel', __('Person'));
        $row->addSelectStaff('gibbonPersonID')->readonly()->selected($gibbon->session->get('gibbonPersonID'));

    $row = $form->addRow();
        $row->addLabel('committeeLabel', __('Committee'));
        $row->addTextField('committee')->readonly()->setValue($committee['name']);

    $row = $form->addRow();
        $row->addLabel('roleLabel', __('Role'));
        $row->addTextField('role')->readonly()->setValue($role['name']);

    $row = $form->addRow();
        $row->addFooter();
        $row->addSubmit();

    echo $form->getOutput();
}
