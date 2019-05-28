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
use Gibbon\Services\Format;
use Gibbon\Module\Committees\Domain\CommitteeGateway;
use Gibbon\Module\Committees\Domain\CommitteeRoleGateway;
use Gibbon\Tables\DataTable;

if (isActionAccessible($guid, $connection2, '/modules/Committees/committees_manage_edit.php') == false) {
    // Access denied
    $page->addError(__('You do not have access to this action.'));
} else {
    //Proceed!
    $search = $_GET['search'] ?? '';
    $committeesCommitteeID = $_GET['committeesCommitteeID'] ?? '';
    $committeesRoleID = $_GET['committeesRoleID'] ?? '';

    $page->breadcrumbs
        ->add(__m('Manage Committees'), 'committees_manage.php', ['search' => $search])
        ->add(__m('Edit Committee'), 'committees_manage_edit.php', ['committeesCommitteeID' => $committeesCommitteeID])
        ->add(__m('Edit Role'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    if (empty($committeesCommitteeID) || empty($committeesRoleID)) {
        $page->addError(__('You have not specified one or more required parameters.'));
        return;
    }

    $values = $container->get(CommitteeRoleGateway::class)->getByID($committeesRoleID);

    if (empty($values)) {
        $page->addError(__('The specified record cannot be found.'));
        return;
    }

    $form = Form::create('committeesManage', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/committees_manage_edit_role_editProcess.php?search=$search");

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);
    $form->addHiddenValue('gibbonSchoolYearID', $values['gibbonSchoolYearID']);
    $form->addHiddenValue('committeesCommitteeID', $committeesCommitteeID);
    $form->addHiddenValue('committeesRoleID', $committeesRoleID);

    $row = $form->addRow();
        $row->addLabel('name', __('Name'));
        $row->addTextField('name')->maxLength(60)->required();

    $row = $form->addRow();
        $row->addLabel('active', __('Active'));
        $row->addYesNo('active')->required();

    $row = $form->addRow();
        $row->addLabel('selectable', __('Selectable'));
        $row->addYesNo('selectable')->required();
        
    $row = $form->addRow();
        $row->addLabel('seats', __('Seats'));
        $row->addNumber('seats')->onlyInteger(true);

    $row = $form->addRow();
        $row->addFooter();
        $row->addSubmit();

    $form->loadAllValuesFrom($values);

    echo $form->getOutput();
}
