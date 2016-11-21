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
 * This class represents the physical copy part, with a serial number and can be borrowed.
 * 
 * @author Hudhaifa Shatnawi <hudhaifa.shatnawi@gmail.com>
 * @version 1.0, Aug 27, 2016 - 9:58:57 AM
 */
class BookVolume
        extends LibraryObject {

    private static $db = array(
        'SerialNumber' => 'Varchar(20)', // Unique serial number
        'BookTitle' => 'Varchar(255)', // Read only - for search
        'TheIndex' => 'Int', // Volume index
        'Length' => 'Int', // Number of pages or audio duration
    );
    private static $translate = array(
        'BookTitle'
    );
    private static $has_one = array(
        'Barcode' => 'Image',
        'BookCopy' => 'BookCopy',
        'Loan' => 'BookLoan'
    );
    private static $has_many = array(
    );
    private static $many_many = array(
    );
    private static $belongs_many_many = array(
        'Catalogs' => 'BooksCatalog',
    );
    private static $indexes = array(
        'SerialNumberIndex' => array(
            'type' => 'unique',
            'value' => '"SerialNumber"'
        )
    );
    private static $searchable_fields = array(
        'SerialNumber' => array(
            'field' => 'NumericField',
            'filter' => 'PartialMatchFilter',
        ),
        'BookTitle' => array(
            'field' => 'TextField',
            'filter' => 'PartialMatchFilter',
        )
    );
    private static $summary_fields = array(
        'SerialNumber',
        'BookCopy.Title',
        'TheIndex',
        'BookCopy.Publisher.Name',
        'Length',
        'BookCopy.IsReference',
    );
    private static $defaults = array(
    );
    private static $bookDigit = 3;

    /**
     * Checks before call onBeforeWrite()
     * @return type
     */
    public function validate() {
        $result = parent::validate();

//        if (!$this->isAvailable()) {
//            $result->error(_t('Librarian.IS_BORROWED', 'This book is borrowed until {value}', array(
//                'value' => $this->Loan()->DueDate
//            )));
//        }

        return $result;
    }

    protected function onBeforeWrite() {
        parent::onBeforeWrite();

        if (!$this->SerialNumber) {
            $sn = $this->ID;
            $codabar = new CodabarNumber($sn, $this->config()->bookDigit);
            $this->SerialNumber = $codabar->getCodabar();
        }

        if (!$this->BarcodeID) {
            $this->BarcodeID = LibrarianHelper::generate_barcode($this->SerialNumber);
        }

        if (!$this->BookTitle) {
            $this->BookTitle = $this->BookCopy()->Title;
        }
    }

    public function fieldLabels($includerelations = true) {
        $labels = parent::fieldLabels($includerelations);

        $labels['Barcode'] = _t('Librarian.BARCODE', "Barcode");
        $labels['BookCopy'] = _t('Librarian.BOOKCOPY', 'Book Copy');
        $labels['BookCopy.IsReference'] = _t('Librarian.IS_REFERENCE', 'Is Reference');
        $labels['BookCopy.Publisher.Name'] = _t('Librarian.PUBLISHER', 'Book Copy');
        $labels['BookCopy.Title'] = _t('Librarian.BOOK_TITLE', 'Title');
        $labels['BookTitle'] = _t('Librarian.BOOK_TITLE', 'Title');
        $labels['BookVolumes'] = _t('Librarian.BOOK_VOLUMES', 'Book Volumes');
        $labels['IsReference'] = _t('Librarian.IS_REFERENCE', 'Is Reference');
        $labels['Length'] = _t('Librarian.LENGTH', "Length");
        $labels['Publisher'] = _t('Librarian.PUBLISHER', 'Publisher');
        $labels['SerialNumber'] = _t('Librarian.SERIAL_NUMBER', "Serial Number");
        $labels['TheIndex'] = _t('Librarian.VOLUME_INDEX', "Volume Index");
        $labels['Title'] = _t('Librarian.BOOK_TITLE', 'Title');

        return $labels;
    }

    public function getCMSFields() {
        $self = & $this;

        $this->beforeUpdateCMSFields(function ($fields) use ($self) {
            if (!$self->canEdit()) {
                return;
            }

            $self->reorderField($fields, 'BookCopyID', 'Root.Main', 'Root.Main');
            $self->reorderField($fields, 'BookTitle', 'Root.Main', 'Root.Main', true);

            $catalogField = ListboxField::create(
                            'Catalogs', //
                            _t('Librarian.CATALOGS', 'Catalogs'), //
                            BooksCatalog::get()->map()->toArray()
                    )->setMultiple(true);
            $fields->removeFieldFromTab('Root', 'Catalogs');
            $fields->addFieldToTab('Root.Main', $catalogField);

            $self->reorderField($fields, 'TheIndex', 'Root.Main', 'Root.Main');
            $self->reorderField($fields, 'SerialNumber', 'Root.Main', 'Root.Main');
            $self->reorderField($fields, 'Length', 'Root.Main', 'Root.Main');
            $self->reorderField($fields, 'LoanID', 'Root.Main', 'Root.Main', true);
        });

        $fields = parent::getCMSFields();

        return $fields;
    }

    function Link($action = null) {
        return parent::Link("volume/$this->ID");
    }

    public function getDefaultSearchContext() {
        $fields = $this->scaffoldSearchFields(array(
            'restrictFields' => array(
                'SerialNumber',
                'BookTitle'
            )
        ));

        $filters = array(
            'SerialNumber' => new EqualToFilter('SerialNumber'),
            'BookTitle' => new PartialMatchFilter('BookTitle')
        );

        return new SearchContext(
                $this->class, $fields, $filters
        );
    }

    public function getTitle() {
        if ($this->BookCopy()) {
            return _t('Librarian.TITLE_VOLUME_FULL', "{value1} ({value2}) - {value3}", array(
                'value1' => $this->getSimpleTitle(),
                'value2' => $this->TheIndex,
                'value3' => $this->BookCopy()->Publisher()->Name,
                    )
            );
        } else {
            return $this->BookTitle;
        }
    }

    /// Book Volume ///
    public function isOverDue() {
        return false;
    }

    public function isAvailable($loanID = -1) {
        return !$this->LoanID || $this->LoanID == $loanID;
//        return true;
    }

    public function getStatus() {
        if ($this->IsReference()) {
            return _t('Librarian.REFERENCE', 'Reference');
        } else if ($this->isOverDue()) {
            return _t('Librarian.OVERDUE', 'Overdue');
        } else if ($this->isAvailable()) {
            return _t('Librarian.CHECKED_IN', 'Checked In');
        } else {
            return _t('Librarian.CHECKED_OUT', 'Checked Out');
        }
    }

    public function getRelated() {
        return BookVolume::get()->sort('RAND()');
    }

    /// Book Copy ///
    public function getBookName() {
        return $this->BookCopy()->getBookName();
    }

    public function getBookTitle() {
        return $this->BookCopy()->Title;
    }

    public function getSimpleTitle() {
        return ($title = $this->getBookTitle()) ? $title : $this->getBookName();
    }

    public function getShortTitle() {
        if ($this->BookCopy()) {
            return _t('Librarian.TITLE_VOLUME_NUMBER', "{value1} ({value2})", array(
                'value1' => $this->getSimpleTitle(),
                'value2' => $this->TheIndex,
                    )
            );
        } else {
            return $this->BookTitle;
        }
    }

    public function getFullTitle() {
        if ($this->BookCopy()) {
            return _t('Librarian.TITLE_VOLUME_FULL', "{value1} ({value2}) - {value3}", array(
                'value1' => $this->getSimpleTitle(),
                'value2' => $this->TheIndex,
                'value3' => $this->BookCopy()->Publisher()->Name,
                    )
            );
        } else {
            return $this->BookTitle;
        }
    }

    public function getCollection() {
        return $this->BookCopy()->Collection;
    }

    public function getShelf() {
        return $this->BookCopy()->Shelf;
    }

    public function IsReference() {
        return $this->BookCopy()->IsReference;
    }

    public function getFormat() {
        return $this->BookCopy()->Format;
    }

    public function getPublishYear() {
        return $this->BookCopy()->PublishYear;
    }

    public function getEdition() {
        return $this->BookCopy()->Edition;
    }

    public function getBook() {
        return $this->BookCopy()->Book();
    }

    public function getPublisher() {
        return $this->BookCopy()->Publisher();
    }

    /// Book ///
    public function getISBN() {
        return $this->BookCopy()->ISBN;
    }

    public function getSubject() {
        return $this->BookCopy()->getSubject();
    }

    public function getOverview() {
        return $this->BookCopy()->getOverview();
    }

    public function getAuthoringYear() {
        return $this->BookCopy()->getAuthoringYear();
    }

    public function getLanguage() {
        return $this->BookCopy()->getLanguage();
    }

    public function getAuthors() {
        return $this->BookCopy()->getAuthors();
    }

    public function getAuthor() {
        return $this->BookCopy()->getAuthor();
    }

}