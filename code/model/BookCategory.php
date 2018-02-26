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
 * @version 1.0, Aug 27, 2016 - 1:05:45 PM
 */
class BookCategory
        extends DataObject
        implements ManageableDataObject, SearchableDataObject, SociableDataObject {

    private static $db = array(
        'Title' => 'Varchar(255)',
        'Description' => 'Text'
    );
    private static $has_one = array(
    );
    private static $has_many = array(
    );
    private static $belongs_many_many = array(
        'Books' => 'Book',
    );
    private static $searchable_fields = array(
        'Title',
    );
    private static $summary_fields = array(
        'Title',
        'Books.Count',
    );

    public function fieldLabels($includerelations = true) {
        $labels = parent::fieldLabels($includerelations);

        $labels['Title'] = _t('Librarian.TITLE', 'Title');
        $labels['Books.Count'] = _t('Librarian.NUMBER_OF_BOOKS', 'Number Of Books');

        return $labels;
    }

    public function canView($member = null) {
        return true;
    }

    function Link($action = null) {
        $page = BookCategoriesPage::get()->first();

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

    public function getRandomBooks($num = 2) {
        $books = array();
        foreach ($this->Books()->sort('RAND()') as $book) {
            $books[] = $book;
        }

        return (new ArrayList($books))->limit($num);
    }

    public function getRandomVolumes() {
        $volumes = array();

        foreach ($this->Books()->sort('RAND()') as $book) {
            foreach ($this->BookCopies()->sort('RAND()') as $copy) {
                foreach ($copy->BookVolumes() as $volume) {
                    if ($includeReferences || !$volume->IsReference()) {
                        $volumes[] = $volume;
                    }
                }
            }
        }
        foreach ($this->BookVolumes()->sort('RAND()') as $volume) {
            if ($includeReferences || !$volume->IsReference()) {
                $volumes[] = $volume;
            }
        }

        return new ArrayList($volumes);
    }

}
