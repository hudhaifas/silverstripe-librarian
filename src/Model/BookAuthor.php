<?php

use HudhaifaS\DOM\Model\ManageableDataObject;
use HudhaifaS\DOM\Model\SearchableDataObject;
use HudhaifaS\DOM\Model\SociableDataObject;
use SilverStripe\Assets\Image;
use SilverStripe\Control\Director;
use SilverStripe\Core\Manifest\ModuleLoader;
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
 * @version 1.0, Aug 27, 2016 - 9:24:57 AM
 */
class BookAuthor
        extends DataObject
        implements ManageableDataObject, SearchableDataObject, SociableDataObject {

    private static $db = [
        'Prefix' => 'Varchar(255)',
        'FirstName' => 'Varchar(255)',
        'LastName' => 'Varchar(255)',
        'Postfix' => 'Varchar(255)',
        'NickName' => 'Varchar(255)',
        'SurName' => 'Varchar(255)',
        'Biography' => 'Text',
        'BirthYear' => 'Int',
        'DeathYear' => 'Int',
    ];
    private static $has_one = [
        'Photo' => Image::class
    ];
    private static $has_many = [
    ];
    private static $belongs_many_many = [
        'Books' => Book::class
    ];
    private static $searchable_fields = [
        'NickName',
        'FirstName',
        'LastName',
        'SurName',
    ];
    private static $summary_fields = [
        'ThumbPhoto',
        'NickName',
        'FirstName',
        'LastName',
        'SurName',
        'Books.Count'
    ];
    private static $field_labels = [
        'ThumbPhoto' => 'Photo',
        'Books.Count' => 'Number Of Books',
    ];

    public function fieldLabels($includerelations = true) {
        $labels = parent::fieldLabels($includerelations);
        $labels['Prefix'] = _t('Librarian.PREFIX', "Prefix");
        $labels['FirstName'] = _t('Librarian.FIRSTNAME', "FirstName");
        $labels['LastName'] = _t('Librarian.LASTNAME', 'LastName');
        $labels['Postfix'] = _t('Librarian.POSTFIX', 'Postfix');
        $labels['NickName'] = _t('Librarian.NICKNAME', 'NickName');
        $labels['Biography'] = _t('Librarian.BIOGRAPHY', "Biography");
        $labels['BirthYear'] = _t('Librarian.BIRTH_YEAR', "BirthYear");
        $labels['DeathYear'] = _t('Librarian.DEATH_YEAR', "DeathYear");
        $labels['Photo'] = _t('Librarian.PHOTO', "Photo");
        $labels['SurName'] = _t('Librarian.SURNAME', "SurName");
        $labels['ThumbPhoto'] = _t('Librarian.PHOTO', 'Photo');
        $labels['Books.Count'] = _t('Librarian.NUMBER_OF_BOOKS', 'Number Of Books');
        $labels['Books'] = _t('Librarian.BOOKS', 'Books');

        return $labels;
    }

    public function getCMSFields() {
        $self = & $this;

        $this->beforeUpdateCMSFields(function ($fields) use ($self) {
            $this->reorderField($fields, 'Prefix', 'Root.Main', 'Root.Main');
            $this->reorderField($fields, 'NickName', 'Root.Main', 'Root.Main');
            $this->reorderField($fields, 'FirstName', 'Root.Main', 'Root.Main');
            $this->reorderField($fields, 'LastName', 'Root.Main', 'Root.Main');
            $this->reorderField($fields, 'SurName', 'Root.Main', 'Root.Main');
            $this->reorderField($fields, 'Postfix', 'Root.Main', 'Root.Main');
            $this->reorderField($fields, 'BirthYear', 'Root.Main', 'Root.Main');
            $this->reorderField($fields, 'DeathYear', 'Root.Main', 'Root.Main');
            $this->reorderField($fields, 'Biography', 'Root.Main', 'Root.Main');
            $this->reorderField($fields, 'Photo', 'Root.Main', 'Root.Main');
        });

        $fields = parent::getCMSFields();

        return $fields;
    }

    protected function onBeforeWrite() {

        parent::onBeforeWrite();
        $trim = [
            'Prefix',
            'FirstName',
            'LastName',
            'Postfix',
            'NickName',
            'SurName'
        ];

        foreach ($trim as $field) {
            $this->trim($field);
        }

        if ($this->Biography) {
            $this->Biography = strip_tags($this->Biography);
        }
    }

    public function canView($member = null) {
        return true;
    }

    function Link($action = null) {
        $page = BookAuthorsPage::get()->first();

        return $page ? $page->Link($action) : null;
    }

    /**
     * Show this DataObejct in the sitemap.xml
     */
    function AbsoluteLink($action = null) {
        return Director::absoluteURL($this->Link("show/$this->ID"));
    }

    public function getTitle() {
        return $this->ShortName();
    }

    //////// ManageableDataObject ////////
    public function getObjectDefaultImage() {
        return ModuleLoader::getModule('hudhaifas/silverstripe-librarian')->getResource('res/images/default-author.png')->getURL();
    }

    public function getObjectEditLink() {
        return $this->Link("edit/$this->ID");
    }

    public function getObjectEditableImageName() {
        
    }

    public function getObjectImage() {
        return $this->Photo();
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
        return BookAuthor::get()->sort('RAND()');
    }

    public function getObjectSummary() {
        return $this->renderWith('Author_Summary');
    }

    public function getObjectTabs() {
        $lists = [];

        if ($this->Biography) {
            $lists[] = [
                'Title' => _t("Librarian.AUTHOR_OVERVIEW", "Author Overview"),
                'Content' => $this->Biography
            ];
        }

        $books = $this->Books();
        if ($books->Count()) {
            $lists[] = [
                'Title' => _t("Librarian.BOOKS", "Books") . " ({$books->Count()})",
                'Content' => $this
                        ->customise([
                            'Results' => $books
                        ])
                        ->renderWith('List_Grid')
            ];
        }

        $this->extend('extraTabs', $lists);

        return new ArrayList($lists);
    }

    public function getObjectTitle() {
        return $this->FullName();
    }

    public function canPublicView() {
        return $this->canView();
    }

    //////// SearchableDataObject //////// 
    public function getObjectRichSnippets() {
        
    }

    //////// SociableDataObject //////// 
    public function getSocialDescription() {
        if ($this->Biography) {
            return strip_tags($this->Biography);
        }

        return $this->FullName();
    }

    public function ThumbPhoto() {
        return $this->Photo()->CMSThumbnail();
    }

    public function ShortName() {
        $name = '';

        if ($this->SurName) {
            $name .= '(' . $this->SurName . ') ';
        }

        $name .= $this->FirstName . ' ' . $this->LastName;

        return $name;
    }

    public function FullName() {
        $name = '';

        if ($this->SurName) {
            $name .= '(' . $this->SurName . ')';
        }

        if ($this->Prefix) {
            $name .= ' ' . $this->Prefix;
        }

        if ($this->NickName) {
            $name .= ' ' . $this->NickName;
        }

        $name .= ' ' . $this->FirstName . ' ' . $this->LastName;

        if ($this->Postfix) {
            $name .= ' ' . $this->Postfix;
        }

        return $name;
    }

    public function OptionName() {
        $name = $this->FirstName . ' ' . $this->LastName;

        if ($this->SurName) {
            $name .= ' (' . $this->SurName . ')';
        }

        return $name;
    }

    public function booksID() {
        foreach ($this->Books() as $book) {
            
        }
    }

    public function getRandomBooks($num = 5) {
        $books = $this->getComponents('Books', '', 'RAND()', '', $num);
        return $books;
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
