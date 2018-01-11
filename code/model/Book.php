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
 * This class represents a virtual library item, means information about item that may not exist yet or used 
 * to be exist in the library, regardless the publisher, the publish year or the number of pages and volumes.
 * The BookCopy represent an exisit copy of this item and the BookVolume class represents the phisical item.
 * 
 * - Book: 
 *   * Name: Hamlet
 *   * Author: William Shakespeare
 *   * Authoring Year: 1605
 *   * Language: English
 *   - Copies:
 *     - 1
 *       * ISBN: 978-0812036381
 *       * Publish Year: 1986 
 *       * Edition: 86
 *       * Publisher: Barron's Educational Series
 *       * Format: Paperback
 *     - 2
 *       * ISBN: 978-1932219081
 *       * Publish Year: 2005
 *       * Edition: Unabridged edition
 *       * Publisher: BBC Audiobooks America
 *       * Format: CD
 *     - 3
 *       * ISBN: 978-1420922530
 *       * Publish Year: 2005
 *       * Edition: 05
 *       * Publisher: Digireads.com
 *       * Format: Paperback
 *     - 4
 *       * ISBN: 978-0671726546
 *       * Publish Year: 1958
 *       * Edition: 58
 *       * Publisher: Washington Square Press
 *       * Format: Paperback
 * 
 * @author Hudhaifa Shatnawi <hudhaifa.shatnawi@gmail.com>
 * @version 1.0, Aug 27, 2016 - 9:24:49 AM
 */
class Book
        extends DataObject
        implements ManageableDataObject, SearchableDataObject {

    private static $db = array(
        'Name' => 'Varchar(255)',
        'Subject' => 'Varchar(255)',
        'Overview' => 'Text',
        'OriginalPublish' => 'Int', // The year of authoring the first
        'Language' => "Enum('العربية, English', 'العربية')",
    );
    private static $translate = array(
        'Name',
        'Subject',
        'Overview',
    );
    private static $has_one = array(
        'Cover' => 'Image',
    );
    private static $has_many = array(
        'BookCopies' => 'BookCopy'
    );
    private static $many_many = array(
        'Authors' => 'BookAuthor',
        'Categories' => 'BookCategory',
    );
    private static $searchable_fields = array(
        'Name' => array(
            'field' => 'TextField',
            'filter' => 'PartialMatchFilter',
        ),
        'Authors.LastName' => array(
            'field' => 'TextField',
            'filter' => 'PartialMatchFilter',
        ),
    );
    private static $summary_fields = array(
        'ThumbCover',
        'Name',
        'Language',
        'BookCopies.Count'
    );

    public function fieldLabels($includerelations = true) {
        $labels = parent::fieldLabels($includerelations);
        $labels['Authors'] = _t('Librarian.AUTHORS', 'Authors');
        $labels['BookCopies'] = _t('Librarian.BOOK_COPIES', 'Book Copies');
        $labels['BookCopies.Count'] = _t('Librarian.NUMBER_OF_COPIES', 'Number Of Copies');
        $labels['Cover'] = _t('Librarian.COVER_IMAGE', 'Cover Image');
        $labels['Language'] = _t('Librarian.LANGUAGE', 'Language');
        $labels['Name'] = _t('Librarian.BOOK_TITLE', 'Book Title');
        $labels['OriginalPublish'] = _t('Librarian.ORIGINAL_PUBLISH', 'Original Publish');
        $labels['Overview'] = _t('Librarian.OVERVIEW', 'Overview');
        $labels['Subject'] = _t('Librarian.SUBJECT', 'Subject');
        $labels['ThumbCover'] = _t('Librarian.COVER_IMAGE', 'Cover Image');
        $labels['Title'] = _t('Librarian.BOOK_TITLE', 'Book Title');
        $labels['Categories'] = _t('Librarian.CATEGORIES', 'Categories');

        return $labels;
    }

    public function getCMSFields() {
        $self = & $this;

        $this->beforeUpdateCMSFields(function ($fields) use ($self) {
            $this->reorderField($fields, 'Name', 'Root.Main', 'Root.Main');
            $this->reorderField($fields, 'Subject', 'Root.Main', 'Root.Main');

            if ($field = $fields->fieldByName('Root.Main.Cover')) {
                $field->getValidator()->setAllowedExtensions(array('jpg', 'jpeg', 'png', 'gif'));
                $field->setFolderName("librarian");

                $fields->removeFieldFromTab('Root.Main', 'Cover');
                $fields->addFieldToTab('Root.Main', $field);
            }
            $this->reorderField($fields, 'Overview', 'Root.Main', 'Root.Main');

            $this->reorderField($fields, 'Language', 'Root.Main', 'Root.Details');
            $this->reorderField($fields, 'OriginalPublish', 'Root.Main', 'Root.Details');

            $fields->removeFieldFromTab('Root', 'Categories');
            $fields->removeFieldFromTab('Root', 'Authors');

            $categoryField = TagField::create(
                            'Categories', //
                            'Categories', //
                            BookCategory::get(), //
                            $self->Categories()
            );
            $fields->addFieldToTab('Root.Details', $categoryField);

            $authorField = AuthorField::create(
                            'Authors', //
                            _t('Librarian.AUTHORS', 'Authors'), //
                            BookAuthor::get(), //
                            $self->Authors()
            );

            $authorField->setTitleField('SurName');
            $authorField->setTitleFunction('OptionName');
            $fields->addFieldToTab('Root.Details', $authorField);
        });

        $fields = parent::getCMSFields();

        return $fields;
    }

    protected function onBeforeWrite() {
        parent::onBeforeWrite();

        if (!$this->Title) {
            $this->Title = $this->Book()->Name;
        }

        if (!$this->BookCopies() || !$this->BookCopies()->Count()) {
            $copy = new BookCopy();
            $copy->Book = $this;
            $copy->write();

            $this->BookCopies()->add($copy);
        }

        if ($this->Overview) {
            $this->Overview = strip_tags($this->Overview);
        }
        // TODO: generate default Index number
    }

    protected function onBeforeDelete() {
        parent::onBeforeDelete();
//        if ($this->BookCopies() && $this->BookCopies()->Count()) {
//            foreach ($this->BookCopies() as $copy) {
//                $copy->delete();
//            }
//        }
    }

    public function canView($member = null) {
        return true;
    }

    public function getTitle() {
        return $this->Name;
    }

    function Link($action = null) {
        $page = BooksPage::get()->first();

        return $page ? $page->Link($action) : null;
    }

    public function getDefaultSearchContext() {
        $fields = $this->scaffoldSearchFields(array(
            'restrictFields' => array(
                'Name',
                'Authors.LastName',
            )
        ));

        $filters = array(
            'Name' => new PartialMatchFilter('Name'),
            'Authors.LastName' => new PartialMatchFilter('Authors.LastName'),
        );

        return new SearchContext(
                $this->class, $fields, $filters
        );
    }

    //////// ManageableDataObject //////// 
    public function getObjectTitle() {
        $title = $this->getTitle();
        return $title;
    }

    public function getObjectDefaultImage() {
        return LIBRARIAN_DIR . "/images/book-cover.png";
    }

    public function getObjectImage() {
        return $this->Cover();
    }

    public function getObjectEditLink() {
        return $this->Link("edit/$this->ID");
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
//        $releated = array();
//
//        foreach ($this->Categories() as $category) {
//            $releated[] = $category->Books()->first()->BookCopies()->first();
//        }
//
//        return new ArrayList($releated);
        return Book::get()->sort('RAND()');
    }

    public function getObjectSummary() {
        return $this->renderWith('Book_Summary');
    }

    public function getObjectTabs() {
        $lists = array();

        $copies = $this->BookCopies();
        if ($copies->Count()) {
            $lists[] = array(
                'Title' => _t("Librarian.BOOK_COPIES", "Book Copies") . " ({$copies->Count()})",
                'Content' => $this->renderWith('Book_Copies')
            );
        }

        if ($this->Overview) {
            $lists[] = array(
                'Title' => _t("Librarian.BOOK_OVERVIEW", "Book Overview"),
                'Content' => $this->Overview
            );
        }

        $this->extend('extraTabs', $lists);

        return new ArrayList($lists);
    }

    public function canPublicView() {
        return $this->canView();
    }

    //////// SearchableDataObject //////// 
    public function getObjectRichSnippets() {
        $schema = array();

        $schema['@context'] = "http://schema.org";
        $schema['@type'] = "Book";
        $schema['@id'] = "#record";
        $schema['name'] = $this->getTitle();
        $schema['url'] = Director::absoluteURL($this->Link());
        $schema['image'] = Director::absoluteURL($this->Cover()->URL);

        if ($this->getAuthor()) {
            $schema['author'] = array();
            $schema['author']['@type'] = "Person";
            $schema['author']['name'] = $this->getAuthor()->getTitle();
        }

        foreach ($this->BookCopies() as $copy) {
            $schema['workExample'] = $copy->getObjectRichSnippets();
        }

        return $schema;
//        return json_encode($schema, JSON_UNESCAPED_UNICODE);
        return Convert::array2json($schema);
    }

    public function getRelated() {
//        $releated = array();
//
//        foreach ($this->Categories() as $category) {
//            $releated[] = $category->Books()->first()->BookCopies()->first();
//        }
//
//        return new ArrayList($releated);
        return Book::get()->sort('RAND()');
    }

    public function getRandomCopies($num = 2) {
        $copies = array();
        foreach ($this->BookCopies()->sort('RAND()') as $copy) {
            $copies[] = $copy;
        }

        return (new ArrayList($copies))->limit($num);
    }

    public function getRandomCategories($num = 3) {
        $categories = array();
        foreach ($this->Categories()->sort('RAND()') as $category) {
            $categories[] = $category;
        }

        return (new ArrayList($categories))->limit($num);
    }

    public function getAuthor() {
        return $this->Authors()->first();
    }

    public function getAvailable() {
        $available = 0;
        return $available;
    }

    public function ThumbCover() {
        return $this->Cover()->CMSThumbnail();
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
