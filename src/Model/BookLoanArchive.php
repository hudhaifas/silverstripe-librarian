<?php

use SilverStripe\Control\Director;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\ReadonlyField;
use SilverStripe\Forms\ToggleCompositeField;
use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Member;
use SilverStripe\View\Requirements;

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
class BookLoanArchive
        extends DataObject {

    private static $table_name = "BookLoanArchive";
    /**
     * Default loan period, can be changed from the YML config file
     * @var type 
     */
    private static $loan_period = 10;
    private static $db = [
        'LoanDate' => 'Date',
        'DueDate' => 'Date', // Read Only
        'BookID' => 'Int',
        'PatronID' => 'Int',
        'RemoteIP' => 'Varchar(128)',
        'Agent' => 'Varchar(255)',
    ];
    private static $has_one = [
        'ReturnedBy' => Member::class,
    ];
    private static $has_many = [
    ];
    private static $defaults = [
    ];
    private static $searchable_fields = [
        'BookID',
        'PatronID',
        'LoanDate',
        'DueDate',
    ];
    private static $summary_fields = [
        'BookID',
        'PatronID',
        'LoanDate',
        'DueDate',
        'Created',
        'ReturnedBy.Title',
    ];
    private static $default_sort = 'Created DESC';

    public function fieldLabels($includerelations = true) {
        $labels = parent::fieldLabels($includerelations);

        $labels['LoanDate'] = _t('Librarian.LOAN_DATE', "Loan Date");
        $labels['DueDate'] = _t('Librarian.DUE_DATE', "Due Date");
        $labels['Created'] = _t('Librarian.RETURN_DATE', "Return Date");
        $labels['ReturnedBy'] = _t('Librarian.LIBRARIAN', "Librarian");
        $labels['ReturnedBy.Title'] = _t('Librarian.LIBRARIAN', "Librarian");
        $labels['Patron.Title'] = _t('Librarian.PATRON_TITLE', "Patron Title");
        $labels['Patron'] = _t('Librarian.PATRON', "Patron Title");
        $labels['Book'] = _t('Librarian.BOOK', "SerialNumber");
        $labels['Book.SerialNumber'] = _t('Librarian.BARCODE', "SerialNumber");
        $labels['Book.Title'] = _t('Librarian.BOOK_TITLE', 'Title');
        $labels['Book.TheIndex'] = _t('Librarian.VOLUME_INDEX', "Volume Index");

        return $labels;
    }

    public function getCMSFields() {
        Requirements::css('hudhaifas/silverstripe-librarian: res/css/librarian.css');

        $fields = FieldList::create(
                        ToggleCompositeField::create('Book', 'Book', [
                                    ReadonlyField::create('BookTitle', 'Book Title'),
                                    ReadonlyField::create('VolumeIndex', 'Index'),
                                    ReadonlyField::create('BookPublisher', 'Publisher'),
                                ])
                                ->setStartClosed(false)
                                ->addExtraClass('history-field'), //
                        ToggleCompositeField::create('Patron', 'Patron', [
                                    ReadonlyField::create('PatronName', 'Name'),
                                ])
                                ->setStartClosed(false)
                                ->addExtraClass('history-field'), //
                        ToggleCompositeField::create('Details', 'Details', [
                                    ReadonlyField::create('LoanDate', 'Loan Date'),
                                    ReadonlyField::create('DueDate', 'Due Date'),
                                    ReadonlyField::create('Created', 'Return Date'),
                                    ReadonlyField::create('Librarian', 'Librarian'),
                                    ReadonlyField::create('RemoteIP', 'Remote IP'),
                                    ReadonlyField::create('Agent', 'Agent'),
                                ])
                                ->setStartClosed(false)
                                ->addExtraClass('history-field')
        );

        $fields = $fields->makeReadonly();

        return $fields;
    }

//    public function canDelete($member = null) {
//        return false; //Permission::check('CMS_ACCESS_CMSMain', 'any', $member);
//    }

    public function Title() {
        return $this->getPatronName() . ' #' . $this->getBookTitle();
    }

    /**
     * Track returned loans
     * 
     * @param BookLoan $returnedLoan The returned loan 
     * @return BookLoanArchive
     * */
    public function track(BookLoan $returnedLoan) {
        $this->BookID = $returnedLoan->BookID;
        $this->PatronID = $returnedLoan->PatronID;
        $this->LoanDate = $returnedLoan->LoanDate;
        $this->DueDate = $returnedLoan->DueDate;

        $this->ReturnedByID = Member::currentUserID();

        $this->RemoteIP = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : (Director::is_cli() ? 'CLI' : 'Unknown remote addr');
        $this->Agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';

        $this->write();

        return $this;
    }

    /**
     * Return a description/summary of the user
     * @return string
     * */
    public function getLibrarian() {
        if ($user = $this->ReturnedBy()) {
            $name = $user->getTitle();
            if ($user->Email) {
                $name .= " <$user->Email>";
            }
            return $name;
        }
    }

    public function getPatron() {
        return DataObject::get_by_id('Patron', $this->PatronID);
    }

    public function getPatronName() {
        if ($patron = $this->getPatron()) {
            $name = $patron->getTitle();
            if ($patron->Email) {
                $name .= " <$patron->Email>";
            }
            return $name;
        }
    }

    public function getBook() {
        return DataObject::get_by_id('BookVolume', $this->BookID);
    }

    public function getBookTitle() {
        if ($book = $this->getBook()) {
            $name = $book->getTitle();
            return $name;
        }
    }

    public function getBookName() {
        if ($book = $this->getBook()) {
            $name = $book->getBookName();
            return $name;
        }
    }

    public function getVolumeIndex() {
        if ($book = $this->getBook()) {
            $name = $book->TheIndex;
            return $name;
        }
    }

    public function getBookPublisher() {
        if ($book = $this->getBook()) {
            $name = $book->BookCopy()->Publisher()->Name;
            return $name;
        }
    }

}
