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
    $gibbonSchoolYearID = $_REQUEST['gibbonSchoolYearID'] ?? $_SESSION[$guid]['gibbonSchoolYearID'];

    $page->breadcrumbs
        ->add(__m('Manage Committees'), 'committees_manage.php', ['search' => $search, 'gibbonSchoolYearID' => $gibbonSchoolYearID])
        ->add(__m('Edit Committee'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $committeesCommitteeID = $_GET['committeesCommitteeID'] ?? '';

    if (empty($committeesCommitteeID)) {
        $page->addError(__('You have not specified one or more required parameters.'));
        return;
    }

    $values = $container->get(CommitteeGateway::class)->getByID($committeesCommitteeID);

    if (empty($values)) {
        $page->addError(__('The specified record cannot be found.'));
        return;
    }

    $form = Form::create('committeesManage', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/committees_manage_editProcess.php?search=$search");

    $form->addHiddenValue('address', $gibbon->session->get('address'));
    $form->addHiddenValue('gibbonSchoolYearID', $gibbonSchoolYearID);
    $form->addHiddenValue('committeesCommitteeID', $committeesCommitteeID);

    $row = $form->addRow();
        $row->addLabel('name', __('Name'));
        $row->addTextField('name')->maxLength(120)->required();

    $row = $form->addRow();
        $row->addLabel('active', __('Active'));
        $row->addYesNo('active')->required();

    $row = $form->addRow();
        $row->addLabel('register', __('Registration'))->description(__m(''));
        $row->addYesNo('register')->required();

    $row = $form->addRow();
        $column = $row->addColumn()->setClass('');
        $column->addLabel('description', __('Description'));
        $column->addEditor('description', $guid);

    $row = $form->addRow();
        $row->addFooter();
        $row->addSubmit();

    $form->loadAllValuesFrom($values);

    echo $form->getOutput();


    // MANAGE ROLES
    $committeeRoleGateway = $container->get(CommitteeRoleGateway::class);
    $criteria = $committeeRoleGateway->newQueryCriteria()
        ->sortBy('name', 'ASC')
        ->fromPOST();

    $roles = $committeeRoleGateway->queryRoles($criteria, $committeesCommitteeID);

    // DATA TABLE
    $table = DataTable::createPaginated('committeeRoles', $criteria);
    $table->setTitle(__('Roles'));

    $table->modifyRows(function ($role, $row) {
        if ($role['active'] != 'Y') $row->addClass('error');
        return $row;
    });

    $table->addColumn('name', __('Name'));
    $table->addColumn('active', __('Active'))->format(Format::using('yesNo', 'active'));
    $table->addColumn('selectable', __('Selectable'))->format(Format::using('yesNo', 'selectable'));
    $table->addColumn('seats', __('Seats'))->width('10%');
    $table->addColumn('members', __('Members'))->width('10%');

    // ACTIONS
    $table->addActionColumn()
        ->addParam('gibbonSchoolYearID', $gibbonSchoolYearID)
        ->addParam('committeesCommitteeID', $committeesCommitteeID)
        ->addParam('committeesRoleID')
        ->format(function ($role, $actions) {
            $actions->addAction('edit', __('Edit'))
                ->setURL('/modules/Committees/committees_manage_edit_role_edit.php');

            $actions->addAction('deleteInstant', __('Delete'))
                ->setIcon('garbage')
                ->isDirect()
                ->setURL('/modules/Committees/committees_manage_edit_role_deleteProcess.php')
                ->addConfirmation(__('Are you sure you wish to delete this record?'));
        });

    echo $table->render($roles);

    // ADD ROLE
    $form = Form::create('committeesManageEdit', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/committees_manage_edit_role_addProcess.php?search='.$search);

    $form->addHiddenValue('address', $gibbon->session->get('address'));
    $form->addHiddenValue('gibbonSchoolYearID', $gibbonSchoolYearID);
    $form->addHiddenValue('committeesCommitteeID', $committeesCommitteeID);

    $form->addRow()->addHeading(__('Add Role'));

    $row = $form->addRow();
        $row->addLabel('name', __('Name'));
        $row->addTextField('name')->maxLength(60)->required();

    $row = $form->addRow();
        $row->addLabel('active', __('Active'));
        $row->addYesNo('active')->required();

    $row = $form->addRow();
        $row->addLabel('selectable', __('Selectable'));
        $row->addYesNo('selectable')->required()->selected('N');
        
    $row = $form->addRow();
        $row->addLabel('seats', __('Seats'));
        $row->addNumber('seats')->onlyInteger(true);

    $row = $form->addRow();
        $row->addFooter();
        $row->addSubmit();

    echo $form->getOutput();
}
