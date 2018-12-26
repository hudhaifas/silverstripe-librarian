<?php

use HudhaifaS\DOM\Model\ManageableDataObject;
use HudhaifaS\DOM\Model\SearchableDataObject;
use HudhaifaS\DOM\Model\SociableDataObject;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\RequiredFields;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataObject;

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
        extends DataObject
        implements ManageableDataObject {

    private static $table_name = "BookLoan";
    /**
     * Default loan period, can be changed from the YML config file
     * @var type 
     */
    private static $loan_period = 10;
    private static $db = [
        'LoanDate' => 'Date',
        'DueDate' => 'Date', // Read Only
    ];
    private static $has_one = [
        'Book' => BookVolume::class,
        'Patron' => Patron::class,
    ];
    private static $has_many = [
    ];
    private static $defaults = [
    ];
    private static $searchable_fields = [
//        'Patron.Title',
//        'Book.SerialNumber',
//        'Book.Title',
        'LoanDate',
        'DueDate',
    ];
    private static $summary_fields = [
        'Patron.Title',
        'Book.getSimpleTitle',
        'Book.TheIndex',
        'Book.SerialNumber',
        'LoanDate',
        'DueDate',
    ];

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
            $result->error(_t('Librarian.EXCEED_NUMBER', 'This patron has exceeded the allowed number of books ({value})', [
                'value' => $this->Patron()->MaxLoansNumber
            ]));
        } else if (!$this->Book()->isAvailable($this->ID)) {
            $result->error(_t('Librarian.IS_BORROWED', 'This book is borrowed until {value}', [
                'value' => $this->DueDate
            ]));
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
        $labels['Book.SerialNumber'] = _t('Librarian.SERIAL_NUMBER', "SerialNumber");
        $labels['Book.getSimpleTitle'] = _t('Librarian.BOOK_TITLE', 'Title');
        $labels['Book.TheIndex'] = _t('Librarian.VOLUME_INDEX', "Volume Index");

        return $labels;
    }

    public function getCMSFields() {
        $self = & $this;

        $this->beforeUpdateCMSFields(function ($fields) use ($self) {
            if (!$self->canEdit()) {
                return;
            }

            // Explicitly define the dropdown lists
            $volumesList = BookVolume::get()->map()->toArray();
            $volumesSelect = DropdownField::create('BookID', 'Book')->setSource($volumesList);
            $fields->replaceField('BookID', $volumesSelect);

            $patronsList = Patron::get()->map()->toArray();
            $patronsSelect = DropdownField::create('PatronID', 'Patron')->setSource($patronsList);
            $fields->replaceField('PatronID', $patronsSelect);

            // Reorder fields
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

    function Link($action = null) {
        $page = BookLoansPage::get()->first();

        return $page ? $page->Link($action) : null;
    }

    //////// ManageableDataObject ////////
    public function getObjectDefaultImage() {
        return $this->Book()->getObjectDefaultImage();
    }

    public function getObjectEditLink() {
        return $this->Link("edit/$this->ID");
    }

    public function getObjectEditableImageName() {
        
    }

    public function getObjectImage() {
        return $this->Book()->getObjectImage();
    }

    public function getObjectItem() {
        return $this->renderWith('Includes\Imageless_Item');
    }

    public function getObjectLink() {
        return $this->Link("show/$this->ID");
    }

    public function getObjectNav() {
        
    }

    public function getObjectRelated() {
        return null;
    }

    public function getObjectSummary() {
        return $this->renderWith('Includes\BookLoan_Summary');
    }

    public function getObjectTabs() {
        $lists = [];

        $lists[] = [
            'Title' => _t("Librarian.DETAILS", "Details"),
            'Content' => $this->renderWith('Includes\BookLoan_Details')
        ];

        $this->extend('extraTabs', $lists);

        return new ArrayList($lists);
    }

    public function getObjectTitle() {
        return $this->Title;
    }

    public function canPublicView() {
        return $this->canView();
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

    function reorderField($fields, $name, $fromTab, $toTab, $disabled = false) {
        $field = $fields->fieldByName($fromTab . '.' . $name);

        if ($field) {
            $fields->removeFieldFromTab($fromTab, $name);
            $fields->addFieldToTab($toTab, $field);

            if ($disabled) {
                $field = $field->performDisabledTransformation();
            }
        }

        return $field;
    }

    function removeField($fields, $name, $fromTab) {
        $field = $fields->fieldByName($fromTab . '.' . $name);

        if ($field) {
            $fields->removeFieldFromTab($fromTab, $name);
        }

        return $field;
    }

    function trim($field) {
        if ($this->$field) {
            $this->$field = trim($this->$field);
        }
    }

}
