<?php

use Gheggie\Barcode\Utils\CodabarNumber;
use HudhaifaS\DOM\Model\ManageableDataObject;
use HudhaifaS\DOM\Model\DiscoverableDataObject;
use SilverStripe\Assets\Image;
use SilverStripe\Control\Director;
use SilverStripe\Forms\ListboxField;
use SilverStripe\Forms\NumericField;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataObject;
use SilverStripe\SiteConfig\SiteConfig;

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
        extends DataObject
        implements ManageableDataObject, DiscoverableDataObject {

    private static $table_name = "BookVolume";
    private static $db = [
        'SerialNumber' => 'Varchar(20)', // Unique serial number
        'BookTitle' => 'Varchar(255)', // Read only - for search
        'TheIndex' => 'Int', // Volume index
        'Length' => 'Int', // Number of pages or audio duration
    ];
    private static $translate = [
        'BookTitle'
    ];
    private static $has_one = [
        'Barcode' => Image::class,
        'BookCopy' => BookCopy::class,
        'Loan' => BookLoan::class
    ];
    private static $has_many = [
    ];
    private static $many_many = [
    ];
    private static $belongs_many_many = [
        'Catalogs' => BooksCatalog::class,
    ];
    private static $searchable_fields = [
        'SerialNumber' => [
            'field' => NumericField::class,
            'filter' => 'PartialMatchFilter',
        ],
        'BookTitle' => [
            'field' => TextField::class,
            'filter' => 'PartialMatchFilter',
        ]
    ];
    private static $summary_fields = [
        'SerialNumber',
        'Title',
        'TheIndex',
        'BookCopy.Publisher.Name',
        'Length',
        'BookCopy.IsReference',
    ];
    private static $defaults = [
    ];
    private static $bookDigit = 3;

    /**
     * Checks before call onBeforeWrite()
     * @return type
     */
    public function validate() {
        $result = parent::validate();

//        if (!$this->isAvailable()) {
//            $result->error(_t('Librarian.IS_BORROWED', 'This book is borrowed until {value}', [
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

        if (!$this->BookTitle && $this->BookCopy()->exists()) {
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
                    );
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

    public function canView($member = null) {
        return true;
    }

    function Link($action = null) {
        $page = BookVolumesPage::get()->first();

        return $page ? $page->Link($action) : null;
    }

    /**
     * Show this DataObejct in the sitemap.xml
     */
    function AbsoluteLink($action = null) {
        return Director::absoluteURL($this->Link("show/$this->ID"));
    }

    public function getTitle() {
        return $this->getFullTitle();
    }

    //////// ManageableDataObject ////////
    public function getObjectDefaultImage() {
        
    }

    public function getObjectEditLink() {
        return $this->Link("edit/$this->ID");
    }

    public function getObjectEditableImageName() {
        
    }

    public function getObjectImage() {
        return $this->BookCopy()->getCoverImage();
    }

    public function getObjectItem() {
        return $this->renderWith('Includes\Library_Item');
    }

    public function getObjectLink() {
        return $this->Link("show/$this->ID");
    }

    public function getObjectNav() {
        
    }

    public function getObjectRelated() {
        return BookVolume::get()->sort('RAND()');
    }

    public function getObjectSummary() {
        return $this->renderWith('Includes\Volume_Summary');
    }

    public function getObjectTabs() {
        $lists = [];

        if ($this->getOverview()) {
            $lists[] = [
                'Title' => _t("Librarian.BOOK_OVERVIEW", "Book Overview"),
                'Content' => $this->getOverview()
            ];
        }

        $this->extend('extraTabs', $lists);

        return new ArrayList($lists);
    }

    public function getObjectTitle() {
        return $this->getTitle();
    }

    public function canPublicView() {
        return $this->canView();
    }

    //////// DiscoverableDataObject //////// 
    public function getObjectMarkup() {
        $schema = [];

        $schema['@context'] = "http://schema.org";
        $schema['@type'] = "Book";
        $schema['@id'] = "#record";
        $schema['image'] = Director::absoluteURL($this->BookCopy()->getCoverImage()->URL);
        $schema['name'] = $this->getTitle();

        if ($this->getISBN()) {
            $schema['isbn'] = $this->getISBN();
        }

        if ($this->getEdition()) {
            $schema['bookEdition'] = $this->getEdition();
        }

        if ($this->Length) {
            $schema['numberOfPages'] = $this->Length;
        }

        if ($this->getAuthor()) {
            $schema['author'] = [];
            $schema['author']['@type'] = "Person";
            $schema['author']['name'] = $this->getAuthor()->getTitle();
        }

        if ($this->getPublishYear()) {
            $schema['datePublished'] = $this->getPublishYear();
        }

        if ($this->getPublisher()->exists()) {
            $schema['publisher'] = [];
            $schema['publisher']['@type'] = "Organization";
            $schema['publisher']['name'] = $this->getPublisher()->getTitle();
            if ($this->getPublisher()->Logo()->exists()) {
                $schema['publisher']['logo'] = Director::absoluteURL($this->getPublisher()->Logo()->URL);
            }

            if ($this->getPublisher()->Address) {
                $schema['publisher']['address']['@type'] = "PostalAddress";
                $schema['publisher']['address']['streetAddress'] = $this->getPublisher()->Address;
            }

            $schema['publisher']['telephone'] = $this->getPublisher()->Phone;
        }

        $schema['offers'] = [];
        $schema['offers']['@type'] = "Offer";
        $schema['offers']['availability'] = $this->isAvailable() ? "http://schema.org/InStock" : "http://schema.org/OutOfStock";
        $schema['offers']['serialNumber'] = $this->SerialNumber;
        $schema['offers']['offeredBy'] = [];
        $schema['offers']['offeredBy']['@type'] = "Library";
        $schema['offers']['offeredBy']['@id'] = Director::BaseURL();
        $schema['offers']['offeredBy']['name'] = SiteConfig::current_site_config()->Title;
        $schema['offers']['offeredBy']['image'] = Director::absoluteURL(THEMES_DIR . "/" . SiteConfig::current_site_config()->Theme . "/images/favicon.png");
        $schema['offers']['itemOffered'] = "#record";

        return $schema;
//        return json_encode($schema, JSON_UNESCAPED_UNICODE);
//        return Convert::array2json($schema);
    }

    //////// SociableDataObject //////// 
    public function getObjectDescription() {
        if ($this->getOverview()) {
            return strip_tags($this->getOverview());
        }

        return $this->getObjectTitle();
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
        if ($title = $this->getBookTitle()) {
            return $title;
        } else if ($title = $this->getBookName()) {
            return $title;
        }

        return $this->BookTitle;
    }

    public function getShortTitle() {
        return _t('Librarian.TITLE_VOLUME_NUMBER', "{value1} ({value2})", [
            'value1' => $this->getSimpleTitle(),
            'value2' => $this->TheIndex,
                ]
        );
    }

    public function getFullTitle() {
        if ($this->BookCopy()->Publisher()->exists()) {
            return _t('Librarian.TITLE_VOLUME_FULL', "{value1} ({value2}) - {value3}", [
                'value1' => $this->getSimpleTitle(),
                'value2' => $this->TheIndex,
                'value3' => $this->BookCopy()->Publisher()->Name,
                    ]
            );
        } else {
            return $this->getShortTitle();
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