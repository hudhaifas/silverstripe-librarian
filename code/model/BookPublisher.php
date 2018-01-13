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
 * @version 1.0, Aug 27, 2016 - 9:50:21 AM
 */
class BookPublisher
        extends DataObject
        implements ManageableDataObject, SearchableDataObject, SociableDataObject {

    private static $db = array(
        'Name' => 'Varchar(255)',
        'Address' => 'Varchar(255)',
        'Phone' => 'Varchar(20)',
        'Description' => 'Text',
    );
    private static $translate = array(
        'Name',
        'Address'
    );
    private static $has_one = array(
        'Logo' => 'Image'
    );
    private static $has_many = array(
        'BookCopies' => 'BookCopy'
    );
    private static $summary_fields = array(
        'ThumbLogo',
        'Name',
        'Address',
        'Phone',
        'BookCopies.Count',
        'Description',
    );
    private static $field_labels = array(
        'ThumbLogo' => 'Logo',
        'BookCopies.Count' => 'Number Of Copies'
    );

    public function fieldLabels($includerelations = true) {
        $labels = parent::fieldLabels($includerelations);
        $labels['Name'] = _t('Librarian.NAME', "Name");
        $labels['Title'] = _t('Librarian.NAME', "Name");
        $labels['Address'] = _t('Librarian.ADDRESS', "Address");
        $labels['Phone'] = _t('Librarian.PHONE', 'Phone');
        $labels['Logo'] = _t('Librarian.LOGO', 'Logo');
        $labels['ThumbLogo'] = _t('Librarian.LOGO', 'Logo');
        $labels['BookCopies.Count'] = _t('Librarian.NUMBER_OF_COPIES', 'Number Of Copies');
        $labels['BookCopies'] = _t('Librarian.BOOK_COPIES', 'Book Copies');

        return $labels;
    }

    public function canView($member = null) {
        return true;
    }

    function Link($action = null) {
        $page = BookPublishersPage::get()->first();

        return $page ? $page->Link($action) : null;
    }

    /**
     * Show this DataObejct in the sitemap.xml
     */
    function AbsoluteLink($action = null) {
        return Director::absoluteURL($this->Link("show/$this->ID"));
    }

    public function Title() {
        $title = $this->Name;

        if ($this->Address) {
            $title .= ' (' . $this->Address . ')';
        }

        return $title;
    }

    //////// ManageableDataObject ////////
    public function getObjectDefaultImage() {
        return LIBRARIAN_DIR . "/images/default-author.png";
    }

    public function getObjectEditLink() {
        return $this->Link("edit/$this->ID");
    }

    public function getObjectImage() {
        return $this->Logo();
    }

    public function getObjectItem() {
        return $this->renderWith('Library_Item');
    }

    public function getObjectLink() {
        return $this->Link("show/$this->ID");
    }

    public function getObjectNav() {
        
    }

    public function getObjectRelated() {
        return BookPublisher::get()->sort('RAND()');
    }

    public function getObjectSummary() {
        $lists = array();

        if ($this->Address) {
            $lists[] = array(
                'Title' => _t('Librarian.ADDRESS', 'Address'),
                'Value' => $this->Address
            );
        }

        if ($this->Phone) {
            $lists[] = array(
                'Title' => _t('Librarian.PHONE', 'Phone'),
                'Value' => $this->Phone
            );
        }

        return new ArrayList($lists);
    }

    public function getObjectTabs() {
        $lists = array();

        $books = $this->BookCopies();
        if ($books->Count()) {
            $lists[] = array(
                'Title' => _t("Librarian.BOOKS", "Books") . " ({$books->Count()})",
                'Content' => $this
                        ->customise(array(
                            'Results' => $books
                        ))
                        ->renderWith('List_Grid')
            );
        }

        $this->extend('extraTabs', $lists);

        return new ArrayList($lists);
    }

    public function getObjectTitle() {
        return $this->Name;
    }

    public function canPublicView() {
        return $this->canView();
    }

    //////// SearchableDataObject //////// 
    public function getObjectRichSnippets() {
        
    }

    //////// SociableDataObject //////// 
    public function getSocialDescription() {
        if ($this->Description) {
            return strip_tags($this->Description);
        }

        return $this->getObjectTitle();
    }

    public function ThumbLogo() {
        return $this->Logo()->CMSThumbnail();
    }

}
