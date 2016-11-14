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
 * @version 1.0, Sep 7, 2016 - 3:18:54 PM
 */
class BookLoan
        extends LibraryObject {

    /**
     * Default loan period, can be changed from the YML config file
     * @var type 
     */
    private static $loan_period = 10;
    private static $db = array(
        'LoanDate' => 'Date',
        'DueDate' => 'Date', // Read Only
    );
    private static $has_one = array(
        'Book' => 'BookVolume',
        'Patron' => 'Patron',
    );
    private static $has_many = array(
    );
    private static $defaults = array(
    );
    private static $searchable_fields = array(
//        'Patron.Title',
//        'Book.SerialNumber',
//        'Book.Title',
        'LoanDate',
        'DueDate',
    );
    private static $summary_fields = array(
        'Patron.Title',
        'Book.SerialNumber',
        'Book.Title',
        'Book.TheIndex',
        'LoanDate',
        'DueDate',
    );

    public function populateDefaults() {
        $this->LoanDate = date("Y-m-d");
        $this->DueDate = $this->calculateDueDate();

        parent::populateDefaults();
    }

//    public function canDelete($member = null) {
//        return Permission::check('CMS_ACCESS_CMSMain', 'any', $member);
//    }

    public function validate() {
        $result = parent::validate();

        if (!$this->Patron()->canBorrow()) {
            $result->error(_t('Librarian.EXCEED_NUMBER', 'This patron has exceeded the allowed number of books ({value})', array(
                'value' => $this->Patron()->MaxLoansNumber
            )));
        } else if (!$this->Book()->isAvailable($this->ID)) {
            $result->error(_t('Librarian.IS_BORROWED', 'This book is borrowed until {value}', array(
                'value' => $this->DueDate
            )));
        }

        return $result;
    }

    protected function onBeforeWrite() {
        parent::onBeforeWrite();
    }

    protected function onBeforeDelete() {
        parent::onBeforeDelete();

        $history = new BookLoanArchive();
        $history->track($this);
    }

    public function fieldLabels($includerelations = true) {
        $labels = parent::fieldLabels($includerelations);

        $labels['LoanDate'] = _t('Librarian.LOAN_DATE', "Loan Date");
        $labels['DueDate'] = _t('Librarian.DUE_DATE', "Due Date");
        $labels['Patron.Title'] = _t('Librarian.PATRON_TITLE', "Patron Title");
        $labels['Patron'] = _t('Librarian.PATRON', "Patron Title");
        $labels['Book'] = _t('Librarian.BOOK', "SerialNumber");
        $labels['Book.SerialNumber'] = _t('Librarian.BARCODE', "SerialNumber");
        $labels['Book.Title'] = _t('Librarian.BOOK_TITLE', 'Title');
        $labels['Book.TheIndex'] = _t('Librarian.VOLUME_INDEX', "Volume Index");

        return $labels;
    }

    public function getCMSFields() {
        $self = & $this;

        $this->beforeUpdateCMSFields(function ($fields) use ($self) {
            if (!$self->canEdit()) {
                return;
            }

            $self->reorderField($fields, 'PatronID', 'Root.Main', 'Root.Main');
            $self->reorderField($fields, 'BookID', 'Root.Main', 'Root.Main');

            if ($field = $self->reorderField($fields, 'LoanDate', 'Root.Main', 'Root.Main')) {
                $field->setConfig('showcalendar', true);
            }

            if ($field = $self->reorderField($fields, 'DueDate', 'Root.Main', 'Root.Main')) {
                $field->setConfig('showcalendar', true);
            }
        });

        $fields = parent::getCMSFields();
//
        return $fields;
    }

    public function Title() {
        return $this->Patron()->getTitle() . ' #' . $this->Book()->getShortTitle();
    }

    private function calculateDueDate() {
        // Calculate the due date
        // 14 days; 24 hours; 60 mins; 60secs
        $dueDate = time() + ($this->config()->loan_period * 24 * 60 * 60);
        return date("Y-m-d", $dueDate);
    }

    public function getCMSValidator() {
        return new RequiredFields('PatronID', 'BookID', 'LoanDate');
    }

    public function isOverdue() {
        return $this->DueDate->InPast();
    }

}