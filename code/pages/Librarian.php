<?php

/*
 * MIT License
 *  
 * Copyright (c) 2016 Hudhaifa Shatnawi
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

/**
 *
 * @author Hudhaifa Shatnawi <hudhaifa.shatnawi@gmail.com>
 * @version 1.0, Sep 6, 2016 - 2:12:20 PM
 */
class Librarian
        extends AbstractLibrary {

    private static $db = array(
    );
    private static $has_one = array(
    );
    private static $has_many = array(
    );
    private static $defaults = array(
        'URLSegment' => 'librarian',
        'Title' => 'Librarian',
        'MenuTitle' => 'Librarian',
    );
    private static $icon = "librarian/images/librarian.png";
    private static $url_segment = 'librarian';
    private static $menu_title = 'librarian';
    private static $allowed_children = 'none';
    private static $description = 'Adds a librarian to your website.';

    public function canCreate($member = false) {
        if (!$member || !(is_a($member, 'Member')) || is_numeric($member)) {
            $member = Member::currentUserID();
        }

        return (DataObject::get($this->owner->class)->count() > 0) ? false : true;
    }

}

class Librarian_Controller
        extends AbstractLibrary_Controller {

    private static $allowed_actions = array(
        'loans',
        'patron',
        'lend',
        'LendForm',
        'doLend',
        'ReturnForm',
        'doReturn',
    );
    private static $url_handlers = array(
        'loans/$action/$patron' => 'loans',
        'patron/$patron/$action' => 'patron',
    );

    public function init() {
        parent::init();
    }

    public function LendForm() {
        $patrons = Patron::get()->map();
        $books = BookVolume::get()->map();

        $loanDate = date("Y-m-d");
        $dueDate = $this->calculateDueDate();

        // Create fields          
        $fields = new FieldList(
                DropdownField::create('Patrons', _t('Librarian.PATRONS', 'Patrons'))
                        ->setEmptyString(_t('Librarian.CHOOSE_PATRON', 'Choose Patron'))
                        ->setSource($patrons), //
                DropdownField::create('Books', _t('Librarian.BOOKS', 'Books'))
                        ->setEmptyString(_t('Librarian.CHOOSE_BOOK', 'Choose Book'))
                        ->setSource($books), //
                DateField::create('LoanDate', _t('Librarian.LOAN_DATE', 'Loan Date'), $loanDate)
                        ->setAttribute('data-datepicker', true)
                        ->setAttribute('data-date-format', 'DD-MM-YYYY'), //
                DateField::create('DueDate', _t('Librarian.DUE_DATE', 'Due Date'), $dueDate)
                        ->setAttribute('data-datepicker', true)
                        ->setAttribute('data-date-format', 'DD-MM-YYYY')
        );

        // Create action
        $actions = new FieldList(
                new FormAction('doLend', _t('Librarian.LEND', 'Lend'))
        );

        // Create Validators
        $validator = new RequiredFields();

        return new Form($this, 'LendForm', $fields, $actions, $validator);
    }

    private function calculateDueDate() {
        // Calculate the due date
        // 14 days; 24 hours; 60 mins; 60secs
        $dueDate = time() + ($this->config()->loan_period * 24 * 60 * 60);
        return date("Y-m-d", $dueDate);
    }

    public function doLend($data, $form) {
        $patron = Patron::get_by_id('Patron', $data['Patrons']);
        $book = BookVolume::get_by_id('BookVolume', $data['Books']);

        LibrarianHelper::lend_book($patron, $book);
        return $this->owner->redirectBack();
    }

    public function ReturnForm($loanID) {
        // Create fields          
        $fields = FieldList::create(
                        HiddenField::create('LoanID', 'LoanID', $loanID)
        );

        // Create action
        $actions = FieldList::create(
                        FormAction::create('doReturn', _t('Librarian.RETURN', 'Return'))
        );

        // Create Validators
        $validator = new RequiredFields();
        return new Form($this, 'ReturnForm', $fields, $actions, $validator);
    }

    public function doReturn($data, $form) {
        $loan = BookLoan::get_by_id('BookLoan', $data['LoanID']);

        LibrarianHelper::return_book($loan);
        return $this->owner->redirectBack();
    }

    public function lend() {
        return $this
                        ->customise(array(
                            'Title' => _t('Librarian.LOAN_LEND', 'Lend')
                        ))
                        ->renderWith(array('Librarian_Lend', 'Page'));
    }

    public function loans() {
        $filtered = $this->filterLoans();

        if ($filtered['Loans']) {
            $paginate = $this->getPaginated($filtered['Loans'], 8);

            return $this
                            ->customise(array(
                                'Loans' => $filtered['Loans'],
                                'Results' => $paginate,
                                'ReturnAction' => $filtered['Action'],
                                'Title' => $filtered['Title']
                            ))
                            ->renderWith(array('Librarian_Loans', 'Page'));
        } else {
            return $this->httpError(404, 'No loans could be found!');
        }
    }

    public function patron() {
        $patronID = $this->getRequest()->param('patron');
        $filtered = $this->filterLoans();

        if ($filtered['Loans']) {
            $paginate = $this->getPaginated($filtered['Loans'], 8);

            return $this
                            ->customise(array(
                                'Loans' => $filtered['Loans'],
                                'Results' => $paginate,
                                'PatronID' => $patronID,
                                'ReturnAction' => $filtered['Action'],
                                'Title' => $filtered['Title']
                            ))
                            ->renderWith(array('Librarian_Patron', 'Page'));
        } else {
            return $this->httpError(404, 'No loans could be found!');
        }

//        $action = $this->getRequest()->param('action');
//        $patronID = $this->getRequest()->param('patron');
//
//        $patron = Patron::get()->byID($patronID);
//        $loans = DataObject::get_by_id('Patron', $patronID)->Loans();
//        $paginateLoans = $this->getPaginated($loans, 9);
//
//        $archives = $this->getLoansArhiveList(
//                array(
//                    'PatronID' => $patronID
//                )
//        );
//        $paginateArchive = $this->getPaginated($archives, 9);
//
//        if ($patron) {
//            return $this
//                            ->customise(array(
//                                'Patron' => $patron,
//                                'Loans' => $paginateLoans,
//                                'Archives' => $paginateArchive,
//                                'Title' => $patron->Title
//                            ))
//                            ->renderWith(array('Librarian_Patron', 'Page'));
//        } else {
//            return $this->httpError(404, 'That book could not be found!');
//        }
    }

    /**
     * @param $action overdue, return or archive
     * @param $id the patron ID
     * @return list of loans 
     */
    private function filterLoans() {
        $action = $this->getRequest()->param('action');
        $patronID = $this->getRequest()->param('patron');
        $returnAction = '1';
        $filter = $patronID ? array('PatronID' => $patronID) : array();
        $title = 'Loans';

        if ($action == 'archive') {
            $loans = $this->getLoansArhiveList($filter);
            $returnAction = '0';
            $title = _t('Librarian.ARCHIVE', 'Loans Archive');
        } else if ($action == 'overdue') {
            $loans = $this->getOverdueLoansList($filter);
            $title = _t('Librarian.OVERDUE', 'Overdue Loans');
        } else {
            $loans = $this->getLoansList($filter);
            $title = _t('Librarian.LOAN_RETURN', 'Return Loan');
        }

        return array(
            'Loans' => $loans,
            'Action' => $returnAction,
            'Title' => $title
        );
    }

    public function getLoansList($filters = array()) {
        return BookLoan::get()->filter($filters);
    }

    public function getLoansArhiveList($filters = array()) {
        return BookLoanArchive::get()->filter($filters);
    }

    public function getOverdueLoansList($filters = array()) {
        $dueFilter = array(
            'DueDate:LessThan' => date('Y-m-d') . ' 23:59:59'
        );

        return $this->getLoansList(
                        array_merge($dueFilter, $filters)
        );
    }

}