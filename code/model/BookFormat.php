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
 * @version 1.0, Aug 27, 2016 - 9:25:06 AM
 */
class BookFormat
        extends DataObject
        implements ManageableDataObject, SearchableDataObject, SociableDataObject {

    private static $db = array(
        'Title' => 'Varchar(255)',
        'Description' => 'Text'
    );
    private static $has_one = array(
    );
    private static $has_many = array(
        'BookCopies' => 'BookCopy',
    );
    private static $belongs_many_many = array(
    );
    private static $searchable_fields = array(
        'Title',
    );
    private static $summary_fields = array(
        'Title',
        'BookCopies.Count',
    );
    private static $field_labels = array(
        'BookCopies.Count' => 'Number Of Copies'
    );

    public function fieldLabels($includerelations = true) {
        $labels = parent::fieldLabels($includerelations);

        $labels['Title'] = _t('Librarian.TITLE', "Title");
        $labels['BookCopies.Count'] = _t('Librarian.NUMBER_OF_COPIES', "Number Of Copies");

        return $labels;
    }

    public function getCMSFields() {
        $self = & $this;

        $this->beforeUpdateCMSFields(function ($fields) use ($self) {

            $this->reorderField($fields, 'BookID', 'Root.Main', 'Root.Main');
            $this->reorderField($fields, 'Title', 'Root.Main', 'Root.Main');

            if ($volumesField = $fields->fieldByName('Root.BookCopies.BookCopies')) {
                $fields->removeFieldFromTab('Root.BookCopies', 'BookCopies');
                $fields->removeFieldFromTab('Root', 'BookCopies');
//                $fields->addFieldToTab('Root.Main', $volumesField);
            }
        });

        $fields = parent::getCMSFields();

        return $fields;
    }

    public function canView($member = null) {
        return true;
    }

    function Link($action = null) {
        $page = BookFormatsPage::get()->first();

        return $page ? $page->Link($action) : null;
    }

    /**
     * Show this DataObejct in the sitemap.xml
     */
    function AbsoluteLink($action = null) {
        return Director::absoluteURL($this->Link("show/$this->ID"));
    }

    //////// ManageableDataObject ////////
    public function getObjectDefaultImage() {
        return null;
    }

    public function getObjectEditLink() {
        return $this->Link("edit/$this->ID");
    }

    public function getObjectEditableImageName() {
        
    }

    public function getObjectImage() {
        return null;
    }

    public function getObjectItem() {
        return $this->renderWith('Imageless_Item');
    }

    public function getObjectLink() {
        return $this->Link("show/$this->ID");
    }

    public function getObjectNav() {
        
    }

    public function getObjectRelated() {
        return BookCategory::get()->sort('RAND()');
    }

    public function getObjectSummary() {
        $lists = array();

        if ($this->Description) {
            $lists[] = array(
                'Title' => _t('Librarian.DESCRIPTION', 'Description'),
                'Value' => '<br />' . $this->Description
            );
        }

        return new ArrayList($lists);
    }

    public function getObjectTabs() {
        $lists = array();

        $books = $this->Books();
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
        return $this->Title;
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
